<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;

class CustomVerifyEmail extends BaseVerifyEmail
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {

        return (new MailMessage)
            ->subject('Weryfikacja E-mail')
            ->greeting('Witaj,')
            ->line('Potwierdź swoje konto klikając przycisk poniżej.')
            ->action('Potwierdź Email', $this->verificationUrl($notifiable))
            ->line('Jeśli to nie Ty zakładałeś konto, możesz zignorować tę wiadomość.')
            ->salutation('Pozdrawiamy, Zespół Konkursu'); 
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
