<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\URL;

class CustomResetPassword extends ResetPassword
{
    use Queueable;
    public $token;
    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
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
            ->subject('Resetowanie hasła')
            ->greeting('Czołem')
            ->line('Otrzymaliśmy prośbę o zresetowanie hasła dla Twojego konta.')
            ->action('Zresetuj hasło', route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]))
            ->line('Ten link wygaśnie za ' . config('auth.passwords.' . config('auth.defaults.passwords') . '.expire') . ' minut.')
            ->line('Jeśli to nie Ty prosiłeś o reset hasła, nie musisz nic robić.')
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
