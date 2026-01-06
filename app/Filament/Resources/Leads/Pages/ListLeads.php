<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Contact;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Create Lead')
                ->modalSubmitActionLabel('Create')
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by_id'] = auth()->id();
                    
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
                    // Handle contact creation/linking after lead is created
                    $contactData = $data['_contact_data'] ?? null;
                    
                    if (!$contactData) {
                        return;
                    }
                    
                    $contactIds = [];
                    
                    // Check if linking existing contacts
                    if (!empty($contactData['existing_contact_ids']) && is_array($contactData['existing_contact_ids'])) {
                        $contactIds = $contactData['existing_contact_ids'];
                    } 
                    // Check if creating new contact (at least one field is filled)
                    else if (
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
                        
                        $contactIds[] = $contact->id;
                    }
                    
                    // Link contacts to lead if we have contact IDs
                    if (!empty($contactIds)) {
                        $record->contacts()->attach($contactIds);
                    }
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $teamId = session('team_id');
        
        return parent::getTableQuery()
            ->when($teamId, fn ($query) => $query->where('team_id', $teamId));
    }
}
