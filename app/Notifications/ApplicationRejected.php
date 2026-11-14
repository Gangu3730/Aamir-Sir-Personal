<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationRejected extends Notification
{
    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Update on your community application')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Thank you for your interest in the private community. After review, we are unable to offer access at this time.')
            ->line('You are welcome to follow the public website for updates.');
    }
}
