<?php

namespace App\Exports;

use App\Models\Competition;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Auth;

class CompetitionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
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
        $query->orderBy('competition_registrations.id', 'asc');
        return $query->get();
    }

    public function map($registration): array
    {
        $this->rowNumber++;
        $student = $registration->student;
        
        $data = [
            $this->rowNumber,
            $registration->id,
            $student->name ?? '',
            $student->last_name ?? '',
            (string)($student->class ?? ''),
            $student->school ?? '',
            $student->school_address ?? '',
            $student->statement,
            $student->teacher ?? '',
            $student->guardian ?? '',
            $student->contact ?? '',
        ];

        foreach ($this->stages as $stage) {
            $result = null;
            if ($student) {
                $dbResult = $stage->results()->where('student_id', $student->id)->value('result');
                $result = is_numeric($dbResult) ? (float)$dbResult : null;
            }
            $data[] = $result;
        }
        
        $data[] = null;
        return $data;
    }

    public function headings(): array
    {
        $baseHeaders = [
            'L.p.',
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getColumnDimension('B')->setVisible(false);
            },
        ];
    }
}