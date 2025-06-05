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
        'user_id',
        'stages_count',
        'registration_deadline',
        'poster_path',
    ];

    public function user() {
        return $this->belongsTo(User::class);
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
