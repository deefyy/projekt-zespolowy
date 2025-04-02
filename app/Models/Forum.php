<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
