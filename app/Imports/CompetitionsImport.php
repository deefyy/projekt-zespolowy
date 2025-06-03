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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompetitionsImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected Competition $competition;
    protected array $columnMappings;
    protected $stagesByNumber;
    protected $registrationsOrdered;

    public function __construct(Competition $competition, array $columnMappingsFromController = [])
    {
        $this->competition = $competition;
        $this->columnMappings = $columnMappingsFromController;
        Log::info("CompetitionsImport Constructor: Competition ID {$this->competition->id}");

        if (method_exists($this->competition, 'stages')) {
            $this->stagesByNumber = $this->competition->stages()->orderBy('stage')->get()->keyBy('stage');
            Log::info("CompetitionsImport Constructor: Loaded " . ($this->stagesByNumber ? $this->stagesByNumber->count() : 0) . " stages. Keys: " . json_encode($this->stagesByNumber ? $this->stagesByNumber->keys()->toArray() : []));
        } else {
            Log::error("CompetitionsImport Constructor: Competition model does not have 'stages' method or relation is broken.");
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
            Log::info("CompetitionsImport loadRegistrations: Loaded " . ($this->registrationsOrdered ? $this->registrationsOrdered->count() : 0) . " ordered registrations.");
            if ($this->registrationsOrdered && $this->registrationsOrdered->isNotEmpty() && $this->registrationsOrdered->first()->student) {
                Log::info("CompetitionsImport loadRegistrations: First registration student ID: " . $this->registrationsOrdered->first()->student->id);
            }
        } else {
            Log::error("CompetitionsImport loadRegistrations: Competition model does not have 'registrations' method.");
            $this->registrationsOrdered = collect();
        }
    }

    public function collection(IlluminateCollection $rows)
    {
        DB::listen(function ($query) {
            Log::debug("SQL Query: " . $query->sql . " | Bindings: " . json_encode($query->bindings) . " | Time: " . $query->time . "ms");
        });

        DB::beginTransaction();
        try {
            $initialRegistrationsCount = $this->registrationsOrdered ? $this->registrationsOrdered->count() : 0;
            Log::info("CompetitionsImport collection: Starting with {$initialRegistrationsCount} initial registrations.");

            foreach ($rows as $rowIndex => $row) {
                $rowDataFromExcel = $row->toArray();
                $lpValue = $this->getRowValue($rowDataFromExcel, 'Lp');
                $lp = is_numeric($lpValue) ? (int)$lpValue : 0;

                Log::info("Processing L.p. {$lp} from Excel row " . ($rowIndex + 1) . " (Excel value: '{$lpValue}')");

                if ($lp <= 0) {
                    Log::warning("CompetitionsImport: Invalid L.p. (value: '{$lpValue}') for row " . ($rowIndex + 1) . ". Skipping.");
                    continue;
                }

                $student = null;
                $isNewStudentFlow = false;

                if ($this->registrationsOrdered && $lp <= $initialRegistrationsCount) {
                    $registration = $this->registrationsOrdered->get($lp - 1);
                    if (!$registration) {
                        Log::error("CompetitionsImport: CRITICAL - Failed to get registration for L.p. {$lp} (index " .($lp-1). ") from preloaded {$initialRegistrationsCount} registrations. Skipping.");
                        continue;
                    }
                    if (!$registration->student) {
                        Log::warning("CompetitionsImport: Registration for L.p. {$lp} (Reg ID: {$registration->id}) has no associated student. Skipping update for this L.p.");
                        continue;
                    }
                    $student = $registration->student;
                    Log::info("Found existing student ID {$student->id} ({$student->name} {$student->last_name}) for L.p. {$lp}. Proceeding with update.");

                    $studentDataToUpdate = [
                        'name'           => $this->getRowValue($rowDataFromExcel, 'Imię ucznia', $student->name),
                        'last_name'      => $this->getRowValue($rowDataFromExcel, 'Nazwisko ucznia', $student->last_name),
                        'class'          => (string) $this->getRowValue($rowDataFromExcel, 'Klasa', $student->class),
                        'school'         => $this->getRowValue($rowDataFromExcel, 'Nazwa szkoły', $student->school),
                        'school_address' => $this->getRowValue($rowDataFromExcel, 'Adres szkoły', $student->school_address),
                        'teacher'        => $this->getRowValue($rowDataFromExcel, 'Nauczyciel', $student->teacher),
                        'guardian'       => $this->getRowValue($rowDataFromExcel, 'Rodzic', $student->guardian),
                        'contact'        => $this->getRowValue($rowDataFromExcel, 'Kontakt', $student->contact),
                        'statement'      => $this->parseBoolean($this->getRowValue($rowDataFromExcel, 'Oświadczenie', $student->statement)),
                    ];
                    Log::info("Data for Student::update (ID {$student->id}): " . json_encode($studentDataToUpdate));
                    $student->update($studentDataToUpdate);
                    Log::info("Student ID {$student->id} update executed.");

                } elseif ($lp > $initialRegistrationsCount) {
                    $isNewStudentFlow = true;
                    Log::info("L.p. {$lp} is for a new student (initial count was {$initialRegistrationsCount}). Proceeding with creation.");

                    $newStudentName = $this->getRowValue($rowDataFromExcel, 'Imię ucznia');
                    $newStudentLastName = $this->getRowValue($rowDataFromExcel, 'Nazwisko ucznia');

                    if (empty($newStudentName) || empty($newStudentLastName)) {
                        Log::warning("CompetitionsImport: Missing required data (Imię ucznia, Nazwisko ucznia) to create new student for L.p. {$lp}. Skipping row. Name='{$newStudentName}', LastName='{$newStudentLastName}'");
                        continue;
                    }

                    $studentDataToCreate = [
                        'name'           => $newStudentName,
                        'last_name'      => $newStudentLastName,
                        'class'          => (string) $this->getRowValue($rowDataFromExcel, 'Klasa'),
                        'school'         => $this->getRowValue($rowDataFromExcel, 'Nazwa szkoły'),
                        'school_address' => $this->getRowValue($rowDataFromExcel, 'Adres szkoły'),
                        'teacher'        => $this->getRowValue($rowDataFromExcel, 'Nauczyciel'),
                        'guardian'       => $this->getRowValue($rowDataFromExcel, 'Rodzic'),
                        'contact'        => $this->getRowValue($rowDataFromExcel, 'Kontakt'),
                        'statement'      => $this->parseBoolean($this->getRowValue($rowDataFromExcel, 'Oświadczenie')),
                    ];
                    Log::info("Data for Student::create: " . json_encode($studentDataToCreate));
                    $student = Student::create($studentDataToCreate);
                    Log::info("New student created with ID {$student->id} for L.p. {$lp}.");

                    $userIdForRegistration = Auth::id() ?? $this->competition->user_id;
                    if (!$userIdForRegistration) {
                        $adminUser = User::where('role', 'admin')->orderBy('id', 'asc')->first();
                        if ($adminUser) {
                            $userIdForRegistration = $adminUser->id;
                            Log::info("Using first admin ID {$userIdForRegistration} for new student registration.");
                        } else {
                            Log::error("CRITICAL: Cannot determine user_id for new registration (student ID {$student->id}). Skipping registration to competition.");
                            $student = null;
                        }
                    }

                    if ($student) {
                        CompetitionRegistration::create([
                            'competition_id' => $this->competition->id,
                            'user_id'        => $userIdForRegistration,
                            'student_id'     => $student->id,
                        ]);
                        Log::info("New student ID {$student->id} registered for competition ID {$this->competition->id} by user ID {$userIdForRegistration}.");
                    }
                } else {
                    Log::warning("CompetitionsImport: Unhandled case for L.p. {$lp}. Initial count {$initialRegistrationsCount}. Skipping.");
                    continue;
                }

                if ($student && $this->stagesByNumber && $this->stagesByNumber->isNotEmpty()) {
                    $logPrefix = $isNewStudentFlow ? "create" : "update";
                    Log::info("Attempting to {$logPrefix} stage results for student ID {$student->id}.");
                    foreach ($this->stagesByNumber as $stageNumber => $stageModel) {
                        $stageSystemHeader = "{$stageNumber} ETAP";
                        $resultExcelValue = $this->getRowValue($rowDataFromExcel, $stageSystemHeader);
                        $finalResultValue = null;
                        if ($resultExcelValue !== null) {
                            $finalResultValue = ($resultExcelValue === '' || !is_numeric($resultExcelValue)) ? null : (int)$resultExcelValue;
                            $updateOrCreateCriteria = [
                                'competition_id' => $this->competition->id,
                                'stage_id'       => $stageModel->id,
                                'student_id'     => $student->id,
                            ];
                            $updateOrCreateValues = ['result' => $finalResultValue];
                            Log::info("Data for StageCompetition::updateOrCreate (Student ID {$student->id}, Stage ID {$stageModel->id}): Criteria=" . json_encode($updateOrCreateCriteria) . ", Values=" . json_encode($updateOrCreateValues));
                            StageCompetition::updateOrCreate($updateOrCreateCriteria, $updateOrCreateValues);
                            Log::info("StageCompetition " . ($isNewStudentFlow ? "created" : "update/create executed") . " for student ID {$student->id}, Stage ID {$stageModel->id}.");
                        }
                    }
                } elseif (!$student) {
                    Log::warning("CompetitionsImport: Student object is null after attempting to find/create for L.p. {$lp}. Cannot process stage results.");
                }
            }
            DB::commit();
            Log::info("CompetitionsImport: DB::commit() executed successfully.");
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            Log::error("CompetitionsImport: ValidationException. DB::rollBack() executed. Failures: " . json_encode($e->failures()) . " Message: " . $e->getMessage());
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            $errorContext = isset($rowDataFromExcel) ? json_encode($rowDataFromExcel) : 'Error occurred before or after row processing loop.';
            Log::error("CompetitionsImport: Critical Throwable caught. DB::rollBack() executed. Message: " . $e->getMessage(), [
                'trace_limit_10_lines' => implode("\n", array_slice(explode("\n", $e->getTraceAsString()), 0, 20)),
                'row_data_at_error' => $errorContext
            ]);
            throw new \Exception("Import failed due to an unexpected error: " . $e->getMessage() . " (See logs for details)", 0, $e);
        }
    }

    private function getRowValue(array $rowDataFromExcel, string $systemStandardHeaderName, $default = null)
    {
        $headerActuallyInUserExcelFile = null;

        if (!empty($this->columnMappings)) {
            $foundUserHeader = array_search($systemStandardHeaderName, $this->columnMappings, true);
            if ($foundUserHeader !== false) {
                $headerActuallyInUserExcelFile = $foundUserHeader;
            }
        }

        if ($headerActuallyInUserExcelFile === null) {
            $headerActuallyInUserExcelFile = $systemStandardHeaderName;
        }

        $maatwebsiteKey = null;
        $asciiHeaderForSnake = null;

        if (preg_match('/^(\d+)\sETAP$/i', $headerActuallyInUserExcelFile, $matches)) {
            $maatwebsiteKey = strtolower($matches[1] . '_etap');
        } elseif (strcasecmp($headerActuallyInUserExcelFile, 'Lp') == 0 || strcasecmp($headerActuallyInUserExcelFile, 'L.p.') == 0) {
            $maatwebsiteKey = 'lp';
        } else {
            $asciiHeaderForSnake = Str::ascii($headerActuallyInUserExcelFile);
            $maatwebsiteKey = Str::snake($asciiHeaderForSnake);
        }

        $debugFields = ['Imię ucznia', 'Nazwisko ucznia', 'Klasa', 'Nazwa szkoły', 'Adres szkoły', 'Oświadczenie', 'Nauczyciel', 'Rodzic', 'Kontakt', 'Lp'];
        if (in_array($systemStandardHeaderName, $debugFields) || preg_match('/^\d+ ETAP$/i', $systemStandardHeaderName)) {
            Log::info("getRowValue (FIELD '{$systemStandardHeaderName}'): UserExcelH='{$headerActuallyInUserExcelFile}', AsciiHForSnake='{$asciiHeaderForSnake}', MaatKey='{$maatwebsiteKey}'");
            if (array_key_exists($maatwebsiteKey, $rowDataFromExcel)) {
                Log::info("getRowValue (FIELD '{$systemStandardHeaderName}'): Klucz '{$maatwebsiteKey}' ZNALEZIONY. Wartość z Excela: " . json_encode($rowDataFromExcel[$maatwebsiteKey]) . ". Domyślna: " . json_encode($default));
            } else {
                 Log::warning("getRowValue (FIELD '{$systemStandardHeaderName}'): Klucz '{$maatwebsiteKey}' NIE ZNALEZIONY. Używam domyślnej: " . json_encode($default));
            }
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
                $rules[$stageMaatwebsiteKey] = ['nullable', 'integer', 'min:0'];
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

        if ($this->stagesByNumber) {
            foreach ($this->stagesByNumber as $stageNumber => $stageModel) {
                $stageMaatwebsiteKey = strtolower($stageNumber . '_etap');
                $messages["{$stageMaatwebsiteKey}.integer"] = "Wynik dla etapu {$stageNumber} musi być liczbą całkowitą.";
                $messages["{$stageMaatwebsiteKey}.min"] = "Wynik dla etapu {$stageNumber} nie może być ujemny.";
            }
        }
        return $messages;
    }
}