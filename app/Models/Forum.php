<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ForumComment;

class Forum extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic',
        'added_date',
        'description',
        'competition_id',
    ];

    public function competition() {
        return $this->belongsTo(Competition::class);
    }

    public function comments()
    {
        return $this->hasMany(ForumComment::class);
    }
}
