<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Contact;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Traits\HasPermission;
use Illuminate\Database\Eloquent\Model;

class ViewLead extends ViewRecord
{
    use HasPermission;
    protected static ?string $permission = 'leads.view';
    
    protected static string $resource = LeadResource::class;

    protected string $view = 'filament.resources.leads.view-lead';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->modalHeading('Edit Lead')
                ->modalSubmitActionLabel('Save')
                ->slideOver()
                ->fillForm(function ($record): array {
                    // Load existing lead data
                    $formData = $record->toArray();
                    
                    // Load tags relationship
                    $formData['tags'] = $record->tags->pluck('id')->toArray();
                    
                    // Load existing contacts
                    $formData['existing_contact_ids'] = $record->contacts->pluck('id')->toArray();
                    
                    return $formData;
                })
                ->mutateFormDataUsing(function (array $data): array {
                    // Auto-set team_id from session
                    $teamId = session('team_id');
                    if ($teamId) {
                        $data['team_id'] = $teamId;
                    }
                    
                    // Extract contact data from form
                    $data['_contact_data'] = [
                        'existing_contact_ids' => $data['existing_contact_ids'] ?? [],
                        'first_name' => $data['contact_first_name'] ?? null,
                        'last_name' => $data['contact_last_name'] ?? null,
                        'email' => $data['contact_email'] ?? null,
                        'phone_number' => $data['contact_phone_number'] ?? null,
                        'company' => $data['contact_company'] ?? null,
                        'job_title' => $data['contact_job_title'] ?? null,
                        'website' => $data['contact_website'] ?? null,
                    ];
                    
                    // Remove contact fields from lead data
                    unset(
                        $data['existing_contact_ids'],
                        $data['contact_first_name'],
                        $data['contact_last_name'],
                        $data['contact_email'],
                        $data['contact_phone_number'],
                        $data['contact_company'],
                        $data['contact_job_title'],
                        $data['contact_website']
                    );
                    
                    return $data;
                })
                ->after(function (Model $record, array $data) {
                    // Handle contact creation/linking after lead is updated
                    $contactData = $data['_contact_data'] ?? null;
                    
                    if (!$contactData) {
                        return;
                    }
                    
                    $contactIdsToSync = [];
                    
                    // Check if linking existing contacts
                    if (!empty($contactData['existing_contact_ids']) && is_array($contactData['existing_contact_ids'])) {
                        $contactIdsToSync = $contactData['existing_contact_ids'];
                    } 
                    
                    // Check if creating new contact (at least one field is filled)
                    if (
                        !empty($contactData['first_name']) || 
                        !empty($contactData['last_name']) || 
                        !empty($contactData['email']) ||
                        !empty($contactData['phone_number']) ||
                        !empty($contactData['company'])
                    ) {
                        // Create new contact
                        $contact = Contact::create([
                            'first_name' => $contactData['first_name'],
                            'last_name' => $contactData['last_name'],
                            'email' => $contactData['email'],
                            'phone_number' => $contactData['phone_number'],
                            'company' => $contactData['company'],
                            'job_title' => $contactData['job_title'],
                            'website' => $contactData['website'],
                            'team_id' => session('team_id'),
                        ]);
                        
                        $contactIdsToSync[] = $contact->id;
                    }
                    
                    // Sync contacts to lead - this will replace all existing contacts with the new selection
                    if (!empty($contactIdsToSync)) {
                        $record->contacts()->sync($contactIdsToSync);
                    }
                }),
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Lead')
                ->modalDescription('Are you sure you want to delete this lead? This action cannot be undone.')
                ->modalSubmitActionLabel('Delete')
                ->visible(fn ($record) => self::hasPermissionTo('leads.delete'))
                ->successRedirectUrl(route('filament.admin.resources.leads.index')),
        ];
    }
}
