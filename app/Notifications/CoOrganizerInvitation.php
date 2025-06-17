<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Competition;

class CoOrganizerInvitation extends Notification
{
    use Queueable;

    protected $competition;

    public function __construct(Competition $competition)
    {
        $this->competition = $competition;
    }

    // Send via email
    public function via($notifiable):array
    {
        return ['mail'];
    }

    // Build the email message
    public function toMail($notifiable)
    {
        $url = route('competitions.show', $this->competition->id);

        return (new MailMessage)
            ->subject('Zostałeś dodany jako współorganizator.')
            ->greeting("Witaj,")
            ->line("Zostałeś dodany jako współorganizator do konkursu \"{$this->competition->name}\".")
            ->action('Zobacz konkurs', $url)
            ->salutation('Pozdrawiamy, Zespół Konkursu');
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}