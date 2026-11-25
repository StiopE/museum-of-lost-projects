<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AdoptionRequestDecision extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private ?string $projectTitle, private ?int $revivedProjectId = null)
    {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $project = $this->projectTitle === null ? '' : ' for '.Str::squish($this->projectTitle);

        if ($this->revivedProjectId !== null) {
            return (new MailMessage)
                ->subject('Adoption approved'.$project)
                ->line('The creator approved your request. Your private revival draft is ready to edit.')
                ->action('Open your revival draft', route('my.projects.edit', $this->revivedProjectId));
        }

        return (new MailMessage)
            ->subject('Adoption request declined'.$project)
            ->line('Your adoption request was declined. You can find the decision in your request history.')
            ->action('View your requests', route('my.adoption-requests.index'));
    }
}
