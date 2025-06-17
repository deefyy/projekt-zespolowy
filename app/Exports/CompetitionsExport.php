<?php

namespace App\Exports;

use App\Models\Competition;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Auth;

class CompetitionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected Competition $competition;
    protected $user;
    protected $stages;

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
        $query->orderBy('competition_registrations.id', 'asc');
        return $query->get();
    }

    public function map($registration): array
    {
        $student = $registration->student;
        $mappedData = [
            'ID Systemowe' => $registration->id,
            'Imię ucznia' => $student->name ?? '',
            'Nazwisko ucznia' => $student->last_name ?? '',
            'Klasa' => (string)($student->class ?? ''),
            'Nazwa szkoły' => $student->school ?? '',
            'Adres szkoły' => $student->school_address ?? '',
            'Oświadczenie' => $student->statement,
            'Nauczyciel' => $student->teacher ?? '',
            'Rodzic' => $student->guardian ?? '',
            'Kontakt' => $student->contact ?? '',
        ];

        foreach ($this->stages as $stage) {
            $headerName = "{$stage->stage} ETAP";
            $result = '';
            if ($student) {
                $dbResult = $stage->results()
                                  ->where('student_id', $student->id)
                                  ->value('result');
                $result = ($dbResult === null || $dbResult === '') ? '' : (string)$dbResult;
            }
            $mappedData[$headerName] = $result;
        }
        
        $mappedData['SUMA'] = '';
        return $mappedData;
    }

    public function headings(): array
    {
        $baseHeaders = [
            'ID Systemowe',
            'Imię ucznia',
            'Nazwisko ucznia',
            'Klasa',
            'Nazwa szkoły',
            'Adres szkoły',
            'Oświadczenie',
            'Nauczyciel',
            'Rodzic',
            'Kontakt',
        ];
        $stageHeaders = $this->stages->map(fn($stage) => "{$stage->stage} ETAP")->toArray();
        $sumHeader = ['SUMA'];
        return array_merge($baseHeaders, $stageHeaders, $sumHeader);
    }
}