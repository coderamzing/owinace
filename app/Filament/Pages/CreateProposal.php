<?php

namespace App\Filament\Pages;

use App\Models\Proposal;
use App\Models\Team;
use App\Models\WorkspaceCredit;
use App\Services\ProposalService;
use App\Filament\Resources\Proposals\ProposalResource;
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

    protected static ?int $navigationSort = 2;

    protected static ?string $title = '';

    protected static ?string $slug = 'create-proposal';

    protected string $view = 'filament.pages.create-proposal';

    public ?array $data = [];

    protected ProposalService $proposalService;

    public function boot(ProposalService $proposalService): void
    {
        $this->proposalService = $proposalService;
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
                        'pitch' => 'PITCH',
                        'intermediate' => 'EXPERIENCE',
                        'professional' => 'APPROACH',
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

            // Generate proposal
            $result = $this->proposalService->generateProposal(
                $description,
                $teamId,
                $type,
                $words
            );

            // Deduct credits
            WorkspaceCredit::create([
                'workspace_id' => $workspace->id,
                'credits' => -$creditCost,
                'transaction_type' => 'USE',
                'triggered_by_id' => Auth::id(),
            ]);

            // Save proposal
            $proposal = Proposal::create([
                'user_id' => Auth::id(),
                'team_id' => $teamId,
                'title' => substr($result['title'], 0, 255),
                'description' => $result['content'],
                'keywords' => '',
                'job_description' => $description,
                'sort_order' => 0,
            ]);

            Notification::make()
                ->success()
                ->title('Proposal Generated')
                ->body('Your proposal has been generated successfully! Redirecting...')
                ->send();

            // Redirect to proposal view page
            $this->redirect(ProposalResource::getUrl('view', ['record' => $proposal]));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Failed to generate proposal: ' . $e->getTraceAsString())
                ->send();
        }
    }
}

