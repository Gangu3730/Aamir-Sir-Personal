<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Generic in-app + email alert: replies, mentions, new posts. */
class CommunityActivity extends Notification
{
    public function __construct(public string $title, public string $url, public bool $email = true) {}

    public function via($notifiable): array
    {
        return $this->email ? ['database', 'mail'] : ['database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->title)
            ->action('View in the community', $this->url);
    }

    public function toArray($notifiable): array
    {
        return ['title' => $this->title, 'url' => $this->url];
    }
}
