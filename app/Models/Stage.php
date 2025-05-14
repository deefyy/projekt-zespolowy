<?php

namespace App\Models;
use App\Models\StagesCompetition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'stage',
        'date',
        'competition_id',
    ];

    public function competition() {
        return $this->belongsTo(Competition::class);
    }

    public function results()
    {
        return $this->hasMany(StageCompetition::class, 'stage_id');
    }
}
