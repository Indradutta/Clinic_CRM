<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuthEmailOtpNotification extends Notification
{
    use Queueable;

    public function __construct(public string $code) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your password reset code')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Use this code to reset your clinic workspace password:')
            ->line('**'.$this->code.'**')
            ->line('This code expires in 10 minutes and can only be used once.')
            ->line('If you did not request this, you can ignore this email.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
