<?php

namespace App\Filament\Resources\Portfolios\Pages;

use App\Filament\Resources\Portfolios\PortfolioResource;
use App\Jobs\ProcessPortfolioCsvImport;
use App\Models\Portfolio;
use App\Traits\HasPermission;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class ImportPortfolios extends Page implements HasForms
{
    use HasPermission;
    use InteractsWithForms;

    protected static ?string $permission = 'portfolio.import';

    protected static string $resource = PortfolioResource::class;

    protected string $view = 'filament.resources.portfolios.pages.import-portfolios';

    protected static ?string $title = 'Import Portfolios';

    protected static ?string $navigationLabel = 'Import';

    public ?array $data = [];

    public bool $showQueued = false;

    public int $queuedCount = 0;

    public function mount(): void
    {
        abort_unless(static::hasPermissionTo('portfolio.import'), 403);
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Upload CSV File')
                    ->description('Upload a CSV file to import portfolios. Each row is processed in the background with embeddings generated automatically.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('CSV File')
                            ->acceptedFileTypes(['text/csv', 'application/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->required()
                            ->disk('local')
                            ->directory('imports')
                            ->visibility('private')
                            ->maxSize(5120)
                            ->helperText('Maximum file size: 5MB. Accepted formats: CSV')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan('full'),

                Section::make('CSV Format Instructions')
                    ->description('Your CSV file should have the following column headers in the first row:')
                    ->schema([
                        Placeholder::make('format_info')
                            ->label('')
                            ->content(new HtmlString('
                                <div class="space-y-3">
                                    <div class="font-mono text-sm bg-gray-50 dark:bg-gray-800 p-3 rounded">
                                        title,description,keywords,is_active,sort_order,scale
                                    </div>
                                    <div class="text-sm space-y-2">
                                        <p><strong>Column Details:</strong></p>
                                        <ul class="list-disc list-inside space-y-1 ml-2">
                                            <li><strong>title</strong>: Portfolio title (required)</li>
                                            <li><strong>description</strong>: Portfolio description (required, max '.number_format(Portfolio::DESCRIPTION_MAX_WORDS).' words)</li>
                                            <li><strong>keywords</strong>: Pipe-separated keywords, e.g. <code>laravel|php|api</code> (required, max 15)</li>
                                            <li><strong>is_active</strong>: 1/0, true/false, yes/no (optional, default: true)</li>
                                            <li><strong>sort_order</strong>: Display order number (optional, default: 0)</li>
                                            <li><strong>scale</strong>: Number from 1 to 10 (optional)</li>
                                        </ul>
                                        <p class="mt-3"><strong>Important Notes:</strong></p>
                                        <ul class="list-disc list-inside space-y-1 ml-2">
                                            <li>Keywords must use <strong>|</strong> as separator to avoid conflicts with CSV commas</li>
                                            <li>All portfolios are assigned to your currently selected team</li>
                                            <li>Import runs in the background; embeddings and credits are applied per portfolio</li>
                                            <li><strong>scale</strong> must be a whole number between 1 and 10 when provided</li>
                                            <li>Empty rows are skipped</li>
                                        </ul>
                                    </div>
                                </div>
                            ')),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columnSpan('full'),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $data = $this->form->getState();

        if (empty($data['file'])) {
            Notification::make()
                ->title('No file selected')
                ->body('Please select a CSV file to import.')
                ->danger()
                ->send();

            return;
        }

        $teamId = session('team_id');
        $userId = auth()->id();

        if (! $teamId) {
            Notification::make()
                ->title('No team selected')
                ->body('Please select a team before importing portfolios.')
                ->danger()
                ->send();

            return;
        }

        $storagePath = $data['file'];
        $filePath = Storage::disk('local')->path($storagePath);

        try {
            $this->validateCsvHeaders($filePath);
            $queuedCount = $this->countImportableRows($filePath);
        } catch (\InvalidArgumentException $exception) {
            Storage::disk('local')->delete($storagePath);

            Notification::make()
                ->title('Invalid CSV')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        if ($queuedCount === 0) {
            Storage::disk('local')->delete($storagePath);

            Notification::make()
                ->title('Import Failed')
                ->body('No valid portfolio rows were found. Check the CSV headers and required fields.')
                ->warning()
                ->send();

            return;
        }

        ProcessPortfolioCsvImport::dispatch($storagePath, $teamId, $userId);

        $this->queuedCount = $queuedCount;
        $this->showQueued = true;
        $this->form->fill();

        Notification::make()
            ->title('Import Queued')
            ->body("{$queuedCount} portfolios queued for import. Embeddings will be generated in the background.")
            ->success()
            ->send();
    }

    protected function validateCsvHeaders(string $filePath): void
    {
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new \RuntimeException('Unable to open import file.');
        }

        $headerRow = fgetcsv($handle, 0);
        fclose($handle);

        if (! $headerRow) {
            throw new \InvalidArgumentException('CSV file is empty or missing a header row.');
        }

        $header = array_map(fn (string $column): string => strtolower(trim($column)), $headerRow);
        $requiredHeaders = ['title', 'description', 'keywords'];
        $missingHeaders = array_diff($requiredHeaders, $header);

        if (! empty($missingHeaders)) {
            throw new \InvalidArgumentException(
                'CSV is missing required headers: '.implode(', ', $missingHeaders),
            );
        }
    }

    protected function countImportableRows(string $filePath): int
    {
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new \RuntimeException('Unable to open import file.');
        }

        $header = null;
        $count = 0;

        while (($row = fgetcsv($handle, 0)) !== false) {
            if (! $header) {
                $header = array_map(fn (string $column): string => strtolower(trim($column)), $row);

                continue;
            }

            if (empty(array_filter($row))) {
                continue;
            }

            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), '');
            }

            $data = array_combine($header, array_slice($row, 0, count($header)));

            if (! $data) {
                continue;
            }

            if ($this->isImportableRow($data)) {
                $count++;
            }
        }

        fclose($handle);

        return $count;
    }

    protected function isImportableRow(array $data): bool
    {
        $title = trim((string) ($data['title'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $keywords = trim((string) ($data['keywords'] ?? ''));

        if ($title === '' || $description === '' || $keywords === '') {
            return false;
        }

        if (Portfolio::exceedsDescriptionWordLimit($description)) {
            return false;
        }

        $scale = trim((string) ($data['scale'] ?? ''));

        if ($scale === '') {
            return true;
        }

        if (! ctype_digit($scale)) {
            return false;
        }

        $scaleValue = (int) $scale;

        return $scaleValue >= 1 && $scaleValue <= 10;
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('import')
                ->label('Queue Import')
                ->action('import')
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray')
                ->requiresConfirmation()
                ->modalHeading('Confirm Import')
                ->modalDescription('Portfolios will be imported in the background. Each row generates an embedding and uses workspace credits.')
                ->modalSubmitActionLabel('Yes, Queue Import'),

            \Filament\Actions\Action::make('cancel')
                ->label('Cancel')
                ->url(PortfolioResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
