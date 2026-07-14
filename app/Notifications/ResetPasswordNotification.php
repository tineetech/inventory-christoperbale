<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token, public string $email) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appUrl = rtrim(env('FE_URL', 'http://localhost:8000'), '/');
        $resetLink = $appUrl . '/reset-password?token=' . $this->token . '&email=' . urlencode($this->email);

        return (new MailMessage)
            ->subject('Reset Password')
            ->line('Klik tombol di bawah untuk mereset password Anda.')
            ->action('Reset Password', $resetLink)
            ->line('Link ini berlaku 60 menit.');
    }
}
