<?php

namespace App\Exports;

use App\Models\Competition;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithSort;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Auth;

class CompetitionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithSort
{
    protected Competition $competition;
    protected $user;
    protected $stages;
    protected int $rowNumber = 0;

    public function __construct(Competition $competition)
    {
        $this->competition = $competition;
        $this->user = Auth::user();
        $this->stages = $competition->stages()->orderBy('stage')->get();
    }

    public function collection()
    {
        $query = $this->competition->registrations()->with(['student']);

        if ($this->user && $this->user->role !== 'admin') {
            $query->where('user_id', $this->user->id);
        }

        return $query->get();
    }

    public function map($registration): array
    {
        $this->rowNumber++;

        $student = $registration->student;

        $base = [
            $this->rowNumber,
            $student->name ?? '',
            $student->last_name ?? '',
            $student->class ?? '',
            $student->school ?? '',
            $student->statement ?? '',
            $student->teacher ?? '',
            $student->guardian ?? '',
            $student->contact ?? '',
        ];

        $stageValues = $this->stages->map(function ($stage) use ($student) {
            return $stage->results()->where('student_id', $student->id)->value('result') ?? '';
        })->toArray();

        return array_merge($base, $stageValues);
    }

    public function headings(): array
    {
        $base = [
            'L.p.',
            'Imię ucznia',
            'Nazwisko ucznia',
            'Klasa',
            'Nazwa szkoły',
            'Oświadczenie',
            'Nauczyciel',
            'Rodzic',
            'Kontakt',
        ];

        $stageHeaders = $this->stages->map(fn($stage) => "{$stage->stage} ETAP")->toArray();

        $sum = [
            'SUMA'
        ];

        return array_merge($base, $stageHeaders, $sum);
    }
}