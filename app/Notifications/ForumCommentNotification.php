<?php

namespace App\Notifications;

use App\Models\ForumComment;   
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForumCommentNotification extends Notification
{
    use Queueable;

    protected $comment;

    public function __construct(ForumComment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Określenie kanałów, przez które powiadomienie będzie wysłane.
     * Wysyłamy tylko e-mail.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Zbudowanie wiadomości e-mail.
     */
    public function toMail($notifiable)
    {
        $forum = $this->comment->forum;
        $competition = $forum->competition;
        $author = $this->comment->user; // autor komentarza (powinien być autor konkursu)
        
        return (new MailMessage)
                    ->subject('Nowy komentarz w forum konkursu: ' . $competition->name)
                    ->greeting('Witaj,')
                    ->line('W konkursie "' . $competition->name . '" pojawił się nowy komentarz.')
                    ->line('Treść komentarza: "' . $this->comment->content . '"')
                    ->line('Autor komentarza: ' . $author->name .' '. $author->last_name)
                    ->action('Zobacz dyskusję', url(route('forums.show', $forum)))
                    ->line('Powiadomienie zostało wysłane, ponieważ dodano ucznia do tego konkursu.')
                    ->salutation('Pozdrawiamy, Zespół Konkursu');
    }

    // (Opcjonalnie można dodać toArray dla powiadomień w bazie, jeśli są wykorzystywane)
    public function toArray($notifiable)
    {
        return [
            'forum_id' => $this->comment->forum_id,
            'content' => $this->comment->content,
        ];
    }
}
