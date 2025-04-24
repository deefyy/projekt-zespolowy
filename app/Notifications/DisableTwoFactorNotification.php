<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class DisableTwoFactorNotification extends Notification
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
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'two-factor.disable',
            Carbon::now()->addMinutes(15),
            [
                'id' => $notifiable->id,
                'hash' => sha1($notifiable->email),
            ]
        );

        return (new MailMessage)
            ->subject('Wyłączenie Two-Factor Authentication')
            ->greeting('Czołem')
            ->line('Dostaliśmy prośbę o wyłączenie 2FA na twoim koncie.')
            ->action('Wyłącz 2FA', $url)
            ->line('Ten link przestanie działać w ciągu 15 minut.')
            ->line('Jeśli nie wysłałeś żadnej prośby, możesz zingnorować ten mail.');
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
