<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'last_name',
        'class',
        'school',
    ];
    public function competitions() {
        return $this->hasMany(Competition::class);
    }
    public function competitionRegistrations()
    {
        return $this->hasMany(CompetitionRegistration::class);
    }

}
