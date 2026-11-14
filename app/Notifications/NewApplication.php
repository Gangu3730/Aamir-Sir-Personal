<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewApplication extends Notification
{
    public function __construct(public User $applicant) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $a = $this->applicant;

        return (new MailMessage)
            ->subject('New community application: '.$a->name)
            ->greeting('New application received')
            ->line("**{$a->name}** ({$a->email}, {$a->phone}) has applied to join the community.")
            ->lineIf($a->profession, 'Profession: '.$a->profession)
            ->lineIf($a->reason, 'Reason: '.$a->reason)
            ->action('Review application', route('admin.applications.show', $a));
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'New application: '.$this->applicant->name,
            'url' => route('admin.applications.show', $this->applicant),
        ];
    }
}
