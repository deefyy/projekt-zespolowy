<?php

namespace App\Exports;

use App\Models\Competition;
use App\Models\Registration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Auth;

class CompetitionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected Competition $competition;
    protected $user;

    public function __construct(Competition $competition)
    {
        $this->competition = $competition;
        $this->user = Auth::user();
    }

    public function collection()
    {
        $query = $this->competition->registrations()->with(['student', 'user']);

        if ($this->user && $this->user->role !== 'admin') {
            $query->where('user_id', $this->user->id);
        }

        return $query->get();
    }

    public function map($registration): array
    {
        return [
            $registration->student->name ?? 'Brak',
            $registration->student->last_name ?? 'Brak',
            $registration->student->class ?? 'Brak',
            $registration->student->school ?? 'Brak',
        ];
    }

    public function headings(): array
    {
        return [
            'Imię',
            'Nazwisko',
            'Klasa',
            'Szkoła',
        ];
    }
}