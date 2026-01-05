<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

/**
 * Email Queue Job
 * 
 * Queue emails with support for templates, plain messages, and CC recipients.
 * 
 * Usage Examples:
 * 
 * // Send plain message email
 * EmailQueue::dispatch('user@example.com', 'Subject', 'Plain text message');
 * 
 * // Send email with template
 * EmailQueue::dispatch(
 *     'user@example.com',
 *     'Welcome Email',
 *     null,
 *     null,
 *     'emails.welcome',
 *     ['name' => 'John Doe', 'link' => 'https://example.com']
 * );
 * 
 * // Send email with CC
 * EmailQueue::dispatch(
 *     'user@example.com',
 *     'Report',
 *     'Please find the report attached.',
 *     'manager@example.com'
 * );
 * 
 * // Send to multiple recipients with CC
 * EmailQueue::dispatch(
 *     ['user1@example.com', 'user2@example.com'],
 *     'Team Update',
 *     'Team update message',
 *     ['cc1@example.com', 'cc2@example.com'],
 *     'emails.team-update',
 *     ['team' => 'Development Team']
 * );
 */
class EmailQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * 
     * @param string|array $to Recipient email(s)
     * @param string $subject Email subject
     * @param string|null $message Plain text message (used if template is not provided)
     * @param string|array|null $cc CC recipient email(s)
     * @param string|null $template Blade template path (e.g., 'emails.welcome')
     * @param array $data Data to pass to the template
     */
    public function __construct(
        public string|array $to,
        public string $subject,
        public ?string $message = null,
        public string|array|null $cc = null,
        public ?string $template = null,
        public array $data = []
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $to = is_array($this->to) ? $this->to : [$this->to];
        $cc = $this->cc ? (is_array($this->cc) ? $this->cc : [$this->cc]) : [];
        

        // If template is provided, render it with data
        if ($this->template) {
            // Render the template view with data
            $htmlContent = View::make($this->template, $this->data)->render();
        } else {
            // Use plain message or convert to HTML
            $htmlContent = $this->message ? nl2br(e($this->message)) : '';
        }

        // Send email using Mail::send with closure
        Mail::send([], [], function ($message) use ($to, $cc, $htmlContent) {
            $message->to($to)
                ->subject($this->subject);

            // Add CC recipients if provided
            if (!empty($cc)) {
                $message->cc($cc);
            }

            // Set HTML content
            $message->html($htmlContent);
        });
    }
}

