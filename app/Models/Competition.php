<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'student_id',
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function managers() {
        return $this->hasMany(CompetitionManagement::class);
    }

    public function registrations() {
        return $this->hasMany(CompetitionRegistration::class);
    }

    public function forum() {
        return $this->hasOne(Forum::class);
    }

    public function stages() {
        return $this->hasMany(Stage::class);
    }
    

}
