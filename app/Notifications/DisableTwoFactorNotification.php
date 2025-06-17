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
            ->subject('Wyłączenie zabezpieczenia dwuetapowego')
            ->greeting('Witaj,')
            ->line('Dostaliśmy prośbę o wyłączenie zabezpieczenia dwuetapowego na twoim koncie.')
            ->action('Wyłącz zabezpieczenie dwuetapowe', $url)
            ->line('Ten link przestanie działać w ciągu 15 minut.')
            ->line('Jeśli nie wysłałeś żadnej prośby, możesz zingnorować ten mail.')
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
