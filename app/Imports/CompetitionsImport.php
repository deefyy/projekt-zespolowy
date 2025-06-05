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
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompetitionsImport implements ToCollection, WithHeadingRow, WithValidation, WithColumnFormatting
{
    protected Competition $competition;
    protected array $columnMappings;
    protected $stagesByNumber;
    protected $registrationsOrdered;

    public function __construct(Competition $competition, array $columnMappingsFromController = [])
    {
        $this->competition = $competition;
        $this->columnMappings = $columnMappingsFromController;
        if (method_exists($this->competition, 'stages')) {
            $this->stagesByNumber = $this->competition->stages()->orderBy('stage')->get()->keyBy('stage');
        } else {
            $this->stagesByNumber = collect();
        }
        $this->loadRegistrations();
    }

    protected function loadRegistrations()
    {
        if (method_exists($this->competition, 'registrations')) {
            $this->registrationsOrdered = $this->competition->registrations()
                                              ->with('student')
                                              ->orderBy('id')
                                              ->get();
        } else {
            $this->registrationsOrdered = collect();
        }
    }

    public function columnFormats(): array
    {
        return $formats;
    }

    public function collection(IlluminateCollection $rows)
    {
        DB::beginTransaction();
        try {
            $initialRegistrationsCount = $this->registrationsOrdered ? $this->registrationsOrdered->count() : 0;

            foreach ($rows as $rowIndex => $row) {
                $rowDataFromExcel = $row->toArray();
                $lpValue = $this->getRowValue($rowDataFromExcel, 'Lp');
                $lp = is_numeric($lpValue) ? (int)$lpValue : 0;

                if ($lp <= 0) {
                    continue;
                }

                $student = null;
                $isNewStudentFlow = false;

                $classValueFromExcel = $this->getRowValue($rowDataFromExcel, 'Klasa');
                $classStringValue = ($classValueFromExcel === null) ? null : (string) $classValueFromExcel;

                if ($this->registrationsOrdered && $lp <= $initialRegistrationsCount) {
                    $registration = $this->registrationsOrdered->get($lp - 1);
                    if (!$registration || !$registration->student) {
                        continue;
                    }
                    $student = $registration->student;
                    $studentDataToUpdate = [
                        'name'           => $this->getRowValue($rowDataFromExcel, 'Imię ucznia', $student->name),
                        'last_name'      => $this->getRowValue($rowDataFromExcel, 'Nazwisko ucznia', $student->last_name),
                        'class'          => $classStringValue ?? (string) $student->class,
                        'school'         => $this->getRowValue($rowDataFromExcel, 'Nazwa szkoły', $student->school),
                        'school_address' => $this->getRowValue($rowDataFromExcel, 'Adres szkoły', $student->school_address),
                        'teacher'        => $this->getRowValue($rowDataFromExcel, 'Nauczyciel', $student->teacher),
                        'guardian'       => $this->getRowValue($rowDataFromExcel, 'Rodzic', $student->guardian),
                        'contact'        => $this->getRowValue($rowDataFromExcel, 'Kontakt', $student->contact),
                        'statement'      => $this->parseBoolean($this->getRowValue($rowDataFromExcel, 'Oświadczenie', $student->statement)),
                    ];
                    $student->update($studentDataToUpdate);
                } elseif ($lp > $initialRegistrationsCount) {
                    $isNewStudentFlow = true;
                    $newStudentName = $this->getRowValue($rowDataFromExcel, 'Imię ucznia');
                    $newStudentLastName = $this->getRowValue($rowDataFromExcel, 'Nazwisko ucznia');

                    if (empty($newStudentName) || empty($newStudentLastName)) {
                        continue;
                    }

                    $studentDataToCreate = [
                        'name'           => $newStudentName,
                        'last_name'      => $newStudentLastName,
                        'class'          => $classStringValue,
                        'school'         => $this->getRowValue($rowDataFromExcel, 'Nazwa szkoły'),
                        'school_address' => $this->getRowValue($rowDataFromExcel, 'Adres szkoły'),
                        'teacher'        => $this->getRowValue($rowDataFromExcel, 'Nauczyciel'),
                        'guardian'       => $this->getRowValue($rowDataFromExcel, 'Rodzic'),
                        'contact'        => $this->getRowValue($rowDataFromExcel, 'Kontakt'),
                        'statement'      => $this->parseBoolean($this->getRowValue($rowDataFromExcel, 'Oświadczenie')),
                    ];
                    $student = Student::create($studentDataToCreate);

                    $userIdForRegistration = Auth::id() ?? $this->competition->user_id;
                    if (!$userIdForRegistration) {
                        $adminUser = User::where('role', 'admin')->orderBy('id', 'asc')->first();
                        $userIdForRegistration = $adminUser ? $adminUser->id : null;
                    }

                    if ($student && $userIdForRegistration) {
                        CompetitionRegistration::create([
                            'competition_id' => $this->competition->id,
                            'user_id'        => $userIdForRegistration,
                            'student_id'     => $student->id,
                        ]);
                    } elseif (!$student || !$userIdForRegistration) {
                        $student = null;
                    }
                } else {
                    continue;
                }

                if ($student && $this->stagesByNumber && $this->stagesByNumber->isNotEmpty()) {
                    foreach ($this->stagesByNumber as $stageNumber => $stageModel) {
                        $stageSystemHeader = "{$stageNumber} ETAP";
                        $resultExcelValue = $this->getRowValue($rowDataFromExcel, $stageSystemHeader);
                        
                        $finalResultForDb = null;
                        if ($resultExcelValue !== null) {
                           $finalResultForDb = ($resultExcelValue === '') ? null : (string) $resultExcelValue;
                        }
                        
                        if ($resultExcelValue !== null) {
                            $updateOrCreateCriteria = [
                                'competition_id' => $this->competition->id,
                                'stage_id'       => $stageModel->id,
                                'student_id'     => $student->id,
                            ];
                            $updateOrCreateValues = ['result' => $finalResultForDb];
                            StageCompetition::updateOrCreate($updateOrCreateCriteria, $updateOrCreateValues);
                        }
                    }
                }
            }
            DB::commit();
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            Log::error("CompetitionsImport: ValidationException. DB::rollBack() executed. Failures: " . json_encode($e->failures()) . " Message: " . $e->getMessage());
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            $errorContext = isset($rowDataFromExcel) ? json_encode($rowDataFromExcel) : 'Error occurred';
            Log::error("CompetitionsImport: Critical Throwable. DB::rollBack() executed. Msg: " . $e->getMessage(), [
                'trace' => implode("\n", array_slice(explode("\n", $e->getTraceAsString()), 0, 10)),
                'row_data' => $errorContext
            ]);
            throw new \Exception("Import failed: " . $e->getMessage(), 0, $e);
        }
    }

    private function getRowValue(array $rowDataFromExcel, string $systemStandardHeaderName, $default = null)
    {
        $headerActuallyInUserExcelFile = $systemStandardHeaderName;
        if (!empty($this->columnMappings)) {
            $foundUserHeader = array_search($systemStandardHeaderName, $this->columnMappings, true);
            if ($foundUserHeader !== false) {
                $headerActuallyInUserExcelFile = $foundUserHeader;
            }
        }

        $maatwebsiteKey = null;
        if (preg_match('/^(\d+)\sETAP$/i', $headerActuallyInUserExcelFile, $matches)) {
            $maatwebsiteKey = strtolower($matches[1] . '_etap');
        } elseif (strcasecmp($headerActuallyInUserExcelFile, 'Lp') == 0 || strcasecmp($headerActuallyInUserExcelFile, 'L.p.') == 0) {
            $maatwebsiteKey = 'lp';
        } else {
            $asciiHeaderForSnake = Str::ascii($headerActuallyInUserExcelFile);
            $maatwebsiteKey = Str::snake($asciiHeaderForSnake);
        }

        if (array_key_exists($maatwebsiteKey, $rowDataFromExcel)) {
            return $rowDataFromExcel[$maatwebsiteKey];
        }
        return $default;
    }

    private function parseBoolean($value): bool
    {
        $result = false;
        if (is_bool($value)) {
            $result = $value;
        } elseif ($value === null || $value === '') {
            $result = false;
        } elseif (is_string($value)) {
            $val = strtolower(trim($value));
            if (in_array($val, ['true', '1', 'yes', 'prawda', 'tak', 'on', 't'])) {
                $result = true;
            } else {
                $result = false;
            }
        } elseif (is_numeric($value)) {
            $result = ((int)$value === 1);
        }
        return $result;
    }

    public function rules(): array
    {
        $rules = [
            'lp'                 => ['required', 'integer', 'min:1'],
            'imie_ucznia'        => ['nullable', 'string', 'max:255'],
            'nazwisko_ucznia'    => ['nullable', 'string', 'max:255'],
            'klasa'              => ['nullable', 'max:255'],
            'nazwa_szkoly'       => ['nullable', 'string', 'max:255'],
            'adres_szkoly'       => ['nullable', 'string', 'max:1000'],
            'oswiadczenie'       => ['nullable'],
            'nauczyciel'         => ['nullable', 'string', 'max:255'],
            'rodzic'             => ['nullable', 'string', 'max:255'],
            'kontakt'            => ['nullable', 'string', 'max:255'],
        ];

        if ($this->stagesByNumber) {
            foreach ($this->stagesByNumber as $stageNumber => $stageModel) {
                $stageMaatwebsiteKey = strtolower($stageNumber . '_etap');
                $rules[$stageMaatwebsiteKey] = ['nullable'];
            }
        }
        return $rules;
    }

    public function customValidationMessages()
    {
        $messages = [];
        $messages['lp.required'] = 'Kolumna "Lp" jest wymagana.';
        $messages['lp.integer'] = 'Wartość w kolumnie "Lp" musi być liczbą całkowitą.';
        $messages['lp.min'] = 'Wartość w kolumnie "Lp" musi być co najmniej 1.';

        return $messages;
    }
}