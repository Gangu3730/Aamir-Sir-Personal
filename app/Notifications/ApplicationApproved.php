<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent on approval. Never contains a password - only a secure, expiring setup link. */
class ApplicationApproved extends Notification
{
    public function __construct(public string $token) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = route('password.reset', ['token' => $this->token, 'email' => $notifiable->email, 'setup' => 1]);
        $days = (int) round(config('auth.passwords.users.expire') / 1440);

        return (new MailMessage)
            ->subject('Your application has been approved - welcome!')
            ->greeting('Welcome, '.$notifiable->name.'!')
            ->line('Your application to join Aamir Sheeraz\'s private community has been approved.')
            ->line('Please set your password to activate your account.')
            ->action('Set your password', $url)
            ->line("This link is valid for {$days} days. After that you can request a new one from the login page.");
    }
}
