<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StageCompetition extends Model
{
    use HasFactory;

    // Jeśli tabela nazywa się "stages_competition", niech model o tym wie:
    protected $table = 'stages_competition';

    // Pola masowego przypisania
    protected $fillable = [
        'competition_id',
        'stage_id',
        'student_id',
        'result',
    ];

    // Relacje:
    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
