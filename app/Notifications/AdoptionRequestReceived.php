<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AdoptionRequestReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $projectTitle,
        private int $projectId,
        private int $requestId,
    ) {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Adoption request for '.Str::squish($this->projectTitle))
            ->line('An explorer would like to revive your project. Read their proposal and decide whether to approve it.')
            ->action('Review adoption request', route('my.projects.edit', $this->projectId).'#request-'.$this->requestId);
    }
}
