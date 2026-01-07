<?php

namespace App\Livewire;

use App\Models\Lead;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class LeadAttachmentManagement extends Component
{
    use WithFileUploads;

    public Lead $lead;
    public $attachment;
    public $uploading = false;
    public $uploadProgress = 0;


    public function mount(Lead $lead)
    {
        $this->lead = $lead;
    }

    public function updatedAttachment()
    {
        try {
            $this->validate([
                'attachment' => 'required|file|max:2048', // 2MB in kilobytes
            ], [
                'attachment.max' => 'The attachment must not be larger than 2MB.',
                'attachment.required' => 'Please select a file to upload.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Validation Error')
                ->body($e->validator->errors()->first())
                ->send();
            
            $this->reset('attachment');
        }
    }

    public function uploadAttachment()
    {
        try {
            $this->validate([
                'attachment' => 'required|file|max:2048', // 2MB in kilobytes
            ], [
                'attachment.max' => 'The attachment must not be larger than 2MB.',
                'attachment.required' => 'Please select a file to upload.',
            ]);

            $this->uploading = true;

            // Add the file to the media collection
            $this->lead->addMedia($this->attachment->getRealPath())
                ->usingName($this->attachment->getClientOriginalName())
                ->usingFileName($this->attachment->getClientOriginalName())
                ->toMediaCollection('attachments');

            // Reset the upload field
            $this->reset('attachment');
            $this->uploading = false;

            // Show success notification using Filament's notification system
            \Filament\Notifications\Notification::make()
                ->success()
                ->title('Success!')
                ->body('Attachment uploaded successfully!')
                ->send();

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->uploading = false;
            
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Validation Error')
                ->body($e->validator->errors()->first())
                ->send();
                
        } catch (\Exception $e) {
            $this->uploading = false;
            
            // Log the error
            \Log::error('Lead attachment upload failed: ' . $e->getMessage(), [
                'lead_id' => $this->lead->id,
                'exception' => $e,
            ]);
            
            // Show error notification
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Upload Failed')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function deleteAttachment($mediaId)
    {
        try {
            $media = $this->lead->getMedia('attachments')->where('id', $mediaId)->first();
            
            if ($media) {
                $media->delete();
                
                \Filament\Notifications\Notification::make()
                    ->success()
                    ->title('Attachment deleted successfully!')
                    ->send();
            }
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Delete Failed')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function downloadAttachment($mediaId)
    {
        try {
            $media = $this->lead->getMedia('attachments')->where('id', $mediaId)->first();
            
            if ($media) {
                return response()->download($media->getPath(), $media->file_name);
            }
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Download Failed')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        $attachments = $this->lead->getMedia('attachments');
        
        return view('livewire.lead-attachment-management', [
            'attachments' => $attachments,
        ]);
    }

    public function getFileIcon($mimeType)
    {
        $iconMap = [
            'application/pdf' => 'heroicon-o-document-text',
            'application/msword' => 'heroicon-o-document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'heroicon-o-document',
            'application/vnd.ms-excel' => 'heroicon-o-table-cells',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'heroicon-o-table-cells',
            'image/jpeg' => 'heroicon-o-photo',
            'image/png' => 'heroicon-o-photo',
            'image/gif' => 'heroicon-o-photo',
            'image/webp' => 'heroicon-o-photo',
            'text/plain' => 'heroicon-o-document-text',
            'application/zip' => 'heroicon-o-archive-box',
            'application/x-rar-compressed' => 'heroicon-o-archive-box',
        ];

        return $iconMap[$mimeType] ?? 'heroicon-o-document';
    }
}

