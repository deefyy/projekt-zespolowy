<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumComment extends Model
{
    use HasFactory;

    // Nazwa tabeli w bazie danych (opcjonalnie, domyślnie Laravel przyjmie "forum_comments")
    protected $table = 'forum_comments';

    // Pola do masowego przypisania
    protected $fillable = ['forum_id', 'user_id', 'content'];

    /**
     * Relacja: komentarz należy do konkretnego postu forum.
     */
    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    /**
     * Relacja: komentarz należy do konkretnego użytkownika (autora komentarza).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
