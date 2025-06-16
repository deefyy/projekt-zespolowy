<?php

namespace App\Imports;

use App\Models\Competition;
use App\Models\Student;
use App\Models\CompetitionRegistration;
use App\Models\StageCompetition;
use App\Models\User;
use Illuminate\Support\Collection as IlluminateCollection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class CompetitionsImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected Competition $competition;
    protected array $columnMappings;
    protected IlluminateCollection $stages;

    private const INITIAL_VERIFICATION_THRESHOLD = 3;
    private const INITIAL_VERIFICATION_ROWS_TO_CHECK = 5;

    public function __construct(Competition $competition, array $columnMappingsFromController = [])
    {
        $this->competition = $competition;
        $this->columnMappings = $columnMappingsFromController;
        $this->stages = $competition->stages()->orderBy('stage')->get();
    }

    private function getRowValue(array $excelRowData, string $systemStandardHeaderName, $default = null)
    {
        $headerActuallyInUserExcelFile = $systemStandardHeaderName;
        if (!empty($this->columnMappings)) {
            $foundUserHeader = array_search($systemStandardHeaderName, $this->columnMappings, true);
            if ($foundUserHeader !== false) {
                $headerActuallyInUserExcelFile = $foundUserHeader;
            }
        }
        $maatwebsiteKey = Str::snake(Str::ascii(strtolower(trim($headerActuallyInUserExcelFile))));
        
        if (preg_match('/^(\d+)\s*ETAP$/i', trim($headerActuallyInUserExcelFile), $matches)) {
             $maatwebsiteKey = strtolower($matches[1] . '_etap');
        } elseif (strcasecmp(trim($headerActuallyInUserExcelFile), 'ID Systemowe') == 0) {
            $maatwebsiteKey = 'id_systemowe';
        }
        
        if (array_key_exists($maatwebsiteKey, $excelRowData)) {
            return $excelRowData[$maatwebsiteKey];
        }
        return $default;
    }

    public function prepareForValidation(array $data, int $index): array
    {
        $headersToConvert = ['Klasa'];
        foreach($this->stages as $stage) {
            $headersToConvert[] = "{$stage->stage} ETAP";
        }

        foreach ($headersToConvert as $headerName) {
            $headerInFile = $headerName;
            if (!empty($this->columnMappings)) {
                $userProvidedHeader = array_search($headerName, $this->columnMappings, true);
                if ($userProvidedHeader !== false) {
                    $headerInFile = $userProvidedHeader;
                }
            }
            $normalizedKey = Str::snake(Str::ascii(strtolower(trim($headerInFile))));
            if (preg_match('/^(\d+)\s*ETAP$/i', trim($headerInFile), $matches)) {
                $normalizedKey = strtolower($matches[1] . '_etap');
            }
            if (isset($data[$normalizedKey]) && $data[$normalizedKey] !== null) {
                $data[$normalizedKey] = (string) $data[$normalizedKey];
            }
        }
        return $data;
    }

    public function collection(IlluminateCollection $rows)
    {
        if ($rows->isEmpty()) return;

        $verifiedMatchCount = 0;
        $rowsToCheck = min($rows->count(), self::INITIAL_VERIFICATION_ROWS_TO_CHECK);
        for ($i = 0; $i < $rowsToCheck; $i++) {
            $excelRowData = $rows->get($i)->toArray();
            $systemId = $this->getRowValue($excelRowData, 'ID Systemowe');
            if (is_numeric($systemId)) {
                $registration = CompetitionRegistration::with('student')->find((int)$systemId);
                if ($registration && $registration->student) {
                    if (
                        strtolower(trim($registration->student->name ?? '')) === strtolower(trim($this->getRowValue($excelRowData, 'Imię ucznia') ?? '')) &&
                        strtolower(trim($registration->student->last_name ?? '')) === strtolower(trim($this->getRowValue($excelRowData, 'Nazwisko ucznia') ?? '')) &&
                        (string)($registration->student->class ?? '') === (string)($this->getRowValue($excelRowData, 'Klasa') ?? '') &&
                        strtolower(trim($registration->student->school ?? '')) === strtolower(trim($this->getRowValue($excelRowData, 'Nazwa szkoły') ?? ''))
                    ) {
                        $verifiedMatchCount++;
                    }
                }
            }
        }
        
        $effectiveThreshold = min(self::INITIAL_VERIFICATION_THRESHOLD, $rows->count());
        if ($verifiedMatchCount < $effectiveThreshold) {
            throw new Exception("Import przerwany: Weryfikacja wstępna pliku nie powiodła się. Niewystarczająca liczba pasujących wierszy (oczekiwano {$effectiveThreshold}, znaleziono {$verifiedMatchCount}).");
        }

        foreach ($rows as $rowIndex => $row) {
            $excelRowData = $row->toArray();
            $systemId = $this->getRowValue($excelRowData, 'ID Systemowe');
            $registrationToUpdate = null;

            if (is_numeric($systemId)) {
                $registrationToUpdate = CompetitionRegistration::with('student')->find((int)$systemId);
            }

            if ($registrationToUpdate && $registrationToUpdate->student) {
                $studentDataToUpdate = [
                    'name'           => $this->getRowValue($excelRowData, 'Imię ucznia', $registrationToUpdate->student->name),
                    'last_name'      => $this->getRowValue($excelRowData, 'Nazwisko ucznia', $registrationToUpdate->student->last_name),
                    'class'          => (string)$this->getRowValue($excelRowData, 'Klasa', $registrationToUpdate->student->class),
                    'school'         => $this->getRowValue($excelRowData, 'Nazwa szkoły', $registrationToUpdate->student->school),
                    'school_address' => $this->getRowValue($excelRowData, 'Adres szkoły', $registrationToUpdate->student->school_address),
                    'teacher'        => $this->getRowValue($excelRowData, 'Nauczyciel', $registrationToUpdate->student->teacher),
                    'guardian'       => $this->getRowValue($excelRowData, 'Rodzic', $registrationToUpdate->student->guardian),
                    'contact'        => $this->getRowValue($excelRowData, 'Kontakt', $registrationToUpdate->student->contact),
                    'statement'      => $this->parseBoolean($this->getRowValue($excelRowData, 'Oświadczenie', $registrationToUpdate->student->statement)),
                ];
                $registrationToUpdate->student->update($studentDataToUpdate);
                $student = $registrationToUpdate->student;
            } else {
                $studentName = $this->getRowValue($excelRowData, 'Imię ucznia');
                $studentLastName = $this->getRowValue($excelRowData, 'Nazwisko ucznia');
                if (empty($studentName) || empty($studentLastName)) continue;

                $student = Student::create([
                    'name'           => $studentName,
                    'last_name'      => $studentLastName,
                    'class'          => (string)$this->getRowValue($excelRowData, 'Klasa'),
                    'school'         => $this->getRowValue($excelRowData, 'Nazwa szkoły'),
                    'school_address' => $this->getRowValue($excelRowData, 'Adres szkoły'),
                    'teacher'        => $this->getRowValue($excelRowData, 'Nauczyciel'),
                    'guardian'       => $this->getRowValue($excelRowData, 'Rodzic'),
                    'contact'        => $this->getRowValue($excelRowData, 'Kontakt'),
                    'statement'      => $this->parseBoolean($this->getRowValue($excelRowData, 'Oświadczenie')),
                ]);
                $userId = Auth::id() ?? $this->competition->user_id ?? User::where('role', 'admin')->orderBy('id')->value('id');
                if ($userId) {
                    CompetitionRegistration::create([
                        'competition_id' => $this->competition->id,
                        'user_id'        => $userId,
                        'student_id'     => $student->id,
                    ]);
                } else {
                    $student = null;
                }
            }

            if ($student && $this->stages->isNotEmpty()) {
                foreach ($this->stages as $stage) {
                    $stageSystemHeader = "{$stage->stage} ETAP";
                    $resultValue = $this->getRowValue($excelRowData, $stageSystemHeader);
                    if ($resultValue !== null) {
                        $finalResult = ($resultValue === '') ? null : (string)$resultValue;
                        StageCompetition::updateOrCreate(
                            ['competition_id' => $this->competition->id, 'stage_id' => $stage->id, 'student_id' => $student->id],
                            ['result' => $finalResult]
                        );
                    }
                }
            }
        }
    }

    private function parseBoolean($value): bool
    {
        if (is_bool($value)) return $value;
        if ($value === null || $value === '') return false;
        if (is_string($value)) {
            $val = strtolower(trim($value));
            if (in_array($val, ['true', '1', 'yes', 'prawda', 'tak', 'on', 't'])) return true;
        }
        if (is_numeric($value)) return ((int)$value === 1);
        return false;
    }

    public function rules(): array
    {
        $rules = [
            'id_systemowe'       => ['nullable', 'integer', 'min:1'],
            'imie_ucznia'        => ['nullable', 'string', 'max:255'],
            'nazwisko_ucznia'    => ['nullable', 'string', 'max:255'],
            'klasa'              => ['nullable', 'string', 'max:255'],
            'nazwa_szkoly'       => ['nullable', 'string', 'max:255'],
            'adres_szkoly'       => ['nullable', 'string', 'max:1000'],
            'oswiadczenie'       => ['nullable'],
            'nauczyciel'         => ['nullable', 'string', 'max:255'],
            'rodzic'             => ['nullable', 'string', 'max:255'],
            'kontakt'            => ['nullable', 'string', 'max:255'],
        ];
        if ($this->stages) {
            foreach ($this->stages as $stage) {
                $stageMaatwebsiteKey = strtolower($stage->stage . '_etap');
                $rules[$stageMaatwebsiteKey] = ['nullable', 'string'];
            }
        }
        return $rules;
    }
}