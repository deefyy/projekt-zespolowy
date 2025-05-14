<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompetitionCreatedNotification extends Notification
{
    use Queueable;

    protected $competition;

    /**
     * Create a new notification instance.
     */
    public function __construct($competition)
    {
        $this->competition = $competition;
    }

    /**
     * Get the notification’s delivery channels.
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
            ->subject('Twój konkurs został utworzony!')
            ->greeting('Czołem, ' . $notifiable->name .' '.$notifiable->last_name. '!')
            ->line('Gratulacje! Konkurs "' . $this->competition->name . '" został pomyślnie utworzony.')
            ->action('Zarządzaj konkursem', route('competitions.show', $this->competition->id))
            ->line('Możesz teraz dodawać uczestników, edytować szczegóły i śledzić zgłoszenia.')
            ->salutation('Pozdrawiamy, Zespół Konkursu');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'competition_id' => $this->competition->id,
            'competition_name' => $this->competition->name,
        ];
    }
}