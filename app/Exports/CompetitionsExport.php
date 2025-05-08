<?php

namespace App\Exports;

use App\Models\Competition;
use App\Models\Registration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;

class CompetitionsExport implements FromCollection, WithHeadings, WithMapping
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
            $registration->student->email ?? 'Brak',
            $this->competition->name,
            $registration->id,
            $registration->student->name ?? 'N/A',
            $registration->user->name ?? 'N/A',
            $registration->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            'Competition Name',
            'Registration ID',
            'Student Name',
            'Student Email',
            'Registered By (User)',
            'Registration Date',
        ];
    }
}