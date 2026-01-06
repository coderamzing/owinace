<?php

namespace App\Filament\Resources\Contacts\Pages;

use App\Filament\Resources\Contacts\ContactResource;
use App\Models\Contact;
use App\Traits\HasPermission;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class ImportContacts extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPermission;
    
    protected static ?string $permission = 'contact.import';
    
    protected static string $resource = ContactResource::class;

    protected string $view = 'filament.resources.contacts.pages.import-contacts';
    
    protected static ?string $title = 'Import Contacts';
    
    protected static ?string $navigationLabel = 'Import';
    
    public ?array $data = [];
    
    public $importedCount = 0;
    public $errorCount = 0;
    public $showResults = false;
    public $errors = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Upload CSV File')
                    ->description('Upload a CSV file to import contacts. The CSV must include headers for the contact fields.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('CSV File')
                            ->acceptedFileTypes(['text/csv', 'application/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->required()
                            ->disk('local')
                            ->directory('imports')
                            ->visibility('private')
                            ->maxSize(5120) // 5MB
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
                                        first_name,last_name,email,phone_number,company,job_title,website
                                    </div>
                                    <div class="text-sm space-y-2">
                                        <p><strong>Column Details:</strong></p>
                                        <ul class="list-disc list-inside space-y-1 ml-2">
                                            <li><strong>first_name</strong>: Contact\'s first name</li>
                                            <li><strong>last_name</strong>: Contact\'s last name</li>
                                            <li><strong>email</strong>: Email address (recommended - used for duplicate detection)</li>
                                            <li><strong>phone_number</strong>: Phone number</li>
                                            <li><strong>company</strong>: Company name</li>
                                            <li><strong>job_title</strong>: Job title or position</li>
                                            <li><strong>website</strong>: Website URL</li>
                                        </ul>
                                        <p class="mt-3"><strong>Important Notes:</strong></p>
                                        <ul class="list-disc list-inside space-y-1 ml-2">
                                            <li>All columns are optional, but at least one field should contain data</li>
                                            <li>If email is provided and exists, the contact will be updated instead of creating a duplicate</li>
                                            <li>All contacts will be assigned to your currently selected team</li>
                                            <li>Empty rows will be skipped</li>
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

        $filePath = Storage::disk('local')->path($data['file']);
        
        try {
            $result = $this->importContacts($filePath);
            
            $this->importedCount = $result['success'];
            $this->errorCount = $result['errors'];
            $this->errors = $result['error_details'] ?? [];
            $this->showResults = true;
            
            // Delete the temporary file
            Storage::disk('local')->delete($data['file']);
            
            // Reset the form
            $this->form->fill();
            
            if ($result['success'] > 0) {
                Notification::make()
                    ->title('Import Completed')
                    ->body("Successfully imported {$result['success']} contacts." . 
                           ($result['errors'] > 0 ? " {$result['errors']} rows had errors." : ""))
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Import Failed')
                    ->body("No contacts were imported. Please check the file format and try again.")
                    ->warning()
                    ->send();
            }
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    protected function importContacts(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        $header = null;
        $successCount = 0;
        $errorCount = 0;
        $errorDetails = [];
        $teamId = session('team_id');
        $rowNumber = 0;
        
        if (!$teamId) {
            throw new \Exception('No team selected. Please select a team before importing.');
        }
        
        while (($row = fgetcsv($handle, 1000)) !== false) {
            $rowNumber++;
            
            // First row is header
            if (!$header) {
                $header = array_map('trim', array_map('strtolower', $row));
                
                // Validate required headers
                $requiredHeaders = ['first_name', 'last_name', 'email', 'phone_number', 'company', 'job_title', 'website'];
                $missingHeaders = array_diff($requiredHeaders, $header);
                
                if (!empty($missingHeaders)) {
                    throw new \Exception('CSV is missing required headers: ' . implode(', ', $missingHeaders));
                }
                
                continue;
            }
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Create associative array from header and row
            $data = array_combine($header, $row);
            
            if (!$data) {
                $errorCount++;
                $errorDetails[] = "Row {$rowNumber}: Invalid row format";
                continue;
            }
            
            try {
                // Prepare contact data
                $contactData = [
                    'team_id' => $teamId,
                    'first_name' => isset($data['first_name']) ? trim($data['first_name']) : null,
                    'last_name' => isset($data['last_name']) ? trim($data['last_name']) : null,
                    'email' => isset($data['email']) ? trim($data['email']) : null,
                    'phone_number' => isset($data['phone_number']) ? trim($data['phone_number']) : null,
                    'company' => isset($data['company']) ? trim($data['company']) : null,
                    'job_title' => isset($data['job_title']) ? trim($data['job_title']) : null,
                    'website' => isset($data['website']) ? trim($data['website']) : null,
                ];
                
                // Remove null and empty values
                $contactData = array_filter($contactData, function ($value) {
                    return !is_null($value) && $value !== '';
                });
                
                // Skip if no meaningful data (only team_id)
                if (count($contactData) <= 1) {
                    $errorCount++;
                    $errorDetails[] = "Row {$rowNumber}: No valid data found";
                    continue;
                }
                
                // Create or update contact (update if email exists)
                if (!empty($contactData['email'])) {
                    Contact::updateOrCreate(
                        [
                            'email' => $contactData['email'],
                            'team_id' => $teamId
                        ],
                        $contactData
                    );
                } else {
                    Contact::create($contactData);
                }
                
                $successCount++;
                
            } catch (\Exception $e) {
                $errorCount++;
                $errorDetails[] = "Row {$rowNumber}: " . $e->getMessage();
                Log::error('Contact import error on row ' . $rowNumber . ': ' . $e->getMessage());
            }
        }
        
        fclose($handle);
        
        return [
            'success' => $successCount,
            'errors' => $errorCount,
            'error_details' => array_slice($errorDetails, 0, 10), // Only return first 10 errors
        ];
    }
    
    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('import')
                ->label('Import Contacts')
                ->action('import')
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray')
                ->requiresConfirmation()
                ->modalHeading('Confirm Import')
                ->modalDescription('Are you sure you want to import these contacts? Existing contacts with the same email will be updated.')
                ->modalSubmitActionLabel('Yes, Import'),
                
            \Filament\Actions\Action::make('cancel')
                ->label('Cancel')
                ->url(ContactResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}

