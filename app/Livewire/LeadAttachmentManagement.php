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

    protected $listeners = ['refreshAttachments' => '$refresh'];

    public function mount(Lead $lead)
    {
        $this->lead = $lead;
    }

    public function updatedAttachment()
    {
        $this->validate([
            'attachment' => 'required|file|max:2048', // 2MB in kilobytes
        ], [
            'attachment.max' => 'The attachment must not be larger than 2MB.',
            'attachment.required' => 'Please select a file to upload.',
        ]);
    }

    public function uploadAttachment()
    {
        $this->validate([
            'attachment' => 'required|file|max:2048', // 2MB in kilobytes
        ], [
            'attachment.max' => 'The attachment must not be larger than 2MB.',
            'attachment.required' => 'Please select a file to upload.',
        ]);

        try {
            $this->uploading = true;

            // Add the file to the media collection
            $this->lead->addMedia($this->attachment->getRealPath())
                ->usingName($this->attachment->getClientOriginalName())
                ->usingFileName($this->attachment->getClientOriginalName())
                ->toMediaCollection('attachments');

            // Reset the upload field
            $this->attachment = null;
            $this->uploading = false;

            // Show success notification
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Attachment uploaded successfully!'
            ]);

            // Refresh the component
            $this->dispatch('refreshAttachments');

        } catch (\Exception $e) {
            $this->uploading = false;
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to upload attachment: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteAttachment($mediaId)
    {
        try {
            $media = $this->lead->getMedia('attachments')->where('id', $mediaId)->first();
            
            if ($media) {
                $media->delete();
                
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Attachment deleted successfully!'
                ]);

                $this->dispatch('refreshAttachments');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to delete attachment: ' . $e->getMessage()
            ]);
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
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to download attachment: ' . $e->getMessage()
            ]);
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

