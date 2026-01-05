<?php

namespace App\Filament\Pages;

use App\Models\Proposal;
use App\Models\Team;
use App\Models\WorkspaceCredit;
use App\Services\OpenAIService;
use App\Services\ProposalService;
use BackedEnum;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateProposal extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Create Proposal';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Create New Coverletter';

    protected static ?string $slug = 'create-proposal';

    protected string $view = 'filament.pages.create-proposal';

    public ?array $data = [];

    protected ProposalService $proposalService;
    protected OpenAIService $openAIService;

    public function boot(ProposalService $proposalService, OpenAIService $openAIService): void
    {
        $this->proposalService = $proposalService;
        $this->openAIService = $openAIService;
    }

    public function mount(): void
    {
        $this->data = [
            'description' => '',
            'words' => '215',
            'type' => 'intermediate',
        ];
        
        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('description')
                    ->label('')
                    ->placeholder('Enter your proposal description...')
                    ->required()
                    ->rows(8)
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'text-lg']),
                Radio::make('words')
                    ->label('Word Count')
                    ->options([
                        '150' => '150',
                        '215' => '215',
                        '300' => '300',
                    ])
                    ->default('215')
                    ->inline()
                    ->required(),
                Radio::make('type')
                    ->label('Proposal Type')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'professional' => 'Professional',
                    ])
                    ->default('intermediate')
                    ->inline()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function generate(): void
    {
        try {
            $data = $this->form->getState();
            $description = $data['description'] ?? '';
            $words = (int) ($data['words'] ?? 215);
            $type = $data['type'] ?? 'intermediate';

            // Get team from session
            $teamId = session('team_id');
            if (!$teamId) {
                throw ValidationException::withMessages([
                    'description' => 'No team selected. Please select a team first.',
                ]);
            }

            $team = Team::find($teamId);
            if (!$team) {
                throw ValidationException::withMessages([
                    'description' => 'Team not found.',
                ]);
            }

            // Check credits
            $workspace = $team->workspace;
            $totalCredits = $workspace->totalCredits();
            $creditCost = config('app.credit_use_per_proposal', 1);

            // if ($totalCredits <= 0) {
            //     throw ValidationException::withMessages([
            //         'description' => 'You have no credits left. Please buy credits.',
            //     ]);
            // }

            // Match portfolio
            $portfolioText = $this->proposalService->matchPortfolio($description, $teamId);

            // Generate proposal
            $result = $this->openAIService->generateProposal($description, $portfolioText, $type, $words);

            // Extract keywords
            $keywords = $this->openAIService->extractKeywords($description);
            $keywordsText = implode(' ', $keywords);

            // Deduct credits
            WorkspaceCredit::create([
                'workspace_id' => $workspace->id,
                'credits' => -$creditCost,
                'transaction_type' => 'USE',
                'triggered_by_id' => Auth::id(),
            ]);

            // Save proposal
            Proposal::create([
                'user_id' => Auth::id(),
                'team_id' => $teamId,
                'title' => substr($result['title'], 0, 255),
                'description' => $result['content'],
                'keywords' => $keywordsText,
                'job_description' => $description,
                'sort_order' => 0,
            ]);

            Notification::make()
                ->success()
                ->title('Proposal Generated')
                ->body('Your proposal has been generated successfully!')
                ->send();

            // Redirect to proposals list
            $this->redirect(route('filament.admin.resources.proposals.index'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Failed to generate proposal: ' . $e->getMessage())
                ->send();
        }
    }
}

