<?php

namespace App\Imports;

use App\Models\Competition;
use App\Models\Student;
use App\Models\CompetitionRegistration;
use App\Models\StageCompetition;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CompetitionsImport implements ToCollection, WithHeadingRow //, WithValidation
{
    private Competition $competition;
    private array $userCustomMappings;
    private Collection $existingCompetitionRegistrations;

    const KEY_LP = 'internal_lp';
    const KEY_NAME = 'internal_name';
    const KEY_LAST_NAME = 'internal_last_name';
    const KEY_CLASS = 'internal_class';
    const KEY_SCHOOL = 'internal_school';
    const KEY_SCHOOL_ADDRESS = 'internal_school_address';
    const KEY_STATEMENT = 'internal_statement';
    const KEY_TEACHER = 'internal_teacher';
    const KEY_GUARDIAN = 'internal_guardian';
    const KEY_CONTACT = 'internal_contact';
    const KEY_SUM = 'internal_sum';
    const KEY_STAGE_RESULT_PREFIX = 'internal_stage_result_';

    private array $expectedExportHeadersToInternalKey = [];
    private array $finalNormalizedFileHeaderToInternalKey = [];
    private array $internalKeyToFinalNormalizedFileHeader = [];
    private array $internalStageKeysMapping = [];

    /**
     * @param Competition $competition
     * @param array $customColumnMappings Mapowanie od użytkownika w formacie:
     *                                   ['Nagłówek w Pliku Excel od Użytkownika' => 'Oczekiwany Nagłówek z Eksportu']
     *                                   Np.: ['Imię Stud.' => 'Imię ucznia', 'Etap I' => '1 ETAP']
     */
    public function __construct(Competition $competition, array $customColumnMappings = [])
    {
        $this->competition = $competition;
        $this->userCustomMappings = $customColumnMappings;

        $this->initializeMappings();

        $query = $this->competition->registrations()
                        ->with('student');

        if (Auth::check() && Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        $orderByClauses = [
            ['column' => 'created_at', 'direction' => 'asc'],
            ['column' => 'id', 'direction' => 'asc'],
        ];

        foreach ($orderByClauses as $clause) {
            if (strpos($clause['column'], '.') === false) {
                $query->orderBy($clause['column'], $clause['direction']);
            }
        }

        $this->existingCompetitionRegistrations = $query->get();
    }

    private function initializeMappings(): void
    {
        $this->expectedExportHeadersToInternalKey = [
            'L.p.' => self::KEY_LP,
            'Imię ucznia' => self::KEY_NAME,
            'Nazwisko ucznia' => self::KEY_LAST_NAME,
            'Klasa' => self::KEY_CLASS,
            'Nazwa szkoły' => self::KEY_SCHOOL,
            'Adres szkoły' => self::KEY_SCHOOL_ADDRESS, // Upewnij się, że to jest w Twoich oczekiwanych nagłówkach
            'Oświadczenie' => self::KEY_STATEMENT,
            'Nauczyciel' => self::KEY_TEACHER,
            'Rodzic' => self::KEY_GUARDIAN,
            'Kontakt' => self::KEY_CONTACT,
            'SUMA' => self::KEY_SUM,
        ];
        // Ten if jest trochę dziwny, lepiej mieć pewność, że Adres szkoły jest w tablicy powyżej, jeśli jest używany
        // if (!in_array('Adres Szkoły', array_keys($this->expectedExportHeadersToInternalKey))) {
        //    $this->expectedExportHeadersToInternalKey['Adres Szkoły'] = self::KEY_SCHOOL_ADDRESS;
        // }

        $stages = $this->competition->stages()->orderBy('stage')->get();
        foreach ($stages as $stage) {
            $exportHeader = "{$stage->stage} ETAP";
            $internalKey = self::KEY_STAGE_RESULT_PREFIX . $stage->id;
            $this->expectedExportHeadersToInternalKey[$exportHeader] = $internalKey;
            $this->internalStageKeysMapping[$internalKey] = $stage->id;
        }

        $processedExpectedHeaders = [];

        if (!empty($this->userCustomMappings)) {
            foreach ($this->userCustomMappings as $userFileHeader => $expectedExportHeader) {
                if (isset($this->expectedExportHeadersToInternalKey[$expectedExportHeader])) {
                    $internalKey = $this->expectedExportHeadersToInternalKey[$expectedExportHeader];
                    $normalizedUserFileHeader = Str::snake(Str::ascii(strtolower(trim($userFileHeader))));

                    $this->finalNormalizedFileHeaderToInternalKey[$normalizedUserFileHeader] = $internalKey;
                    $this->internalKeyToFinalNormalizedFileHeader[$internalKey] = $normalizedUserFileHeader;
                    $processedExpectedHeaders[$expectedExportHeader] = true;
                } else {
                    Log::warning("Import konkursu {$this->competition->id}: Użytkownik próbował zmapować nagłówek z pliku ('{$userFileHeader}') na nieznany oczekiwany nagłówek eksportu ('{$expectedExportHeader}').");
                }
            }
        }

        foreach ($this->expectedExportHeadersToInternalKey as $expectedExportHeader => $internalKey) {
            if (!isset($processedExpectedHeaders[$expectedExportHeader])) {
                $normalizedExpectedHeader = Str::snake(Str::ascii(strtolower(trim($expectedExportHeader))));
                $this->finalNormalizedFileHeaderToInternalKey[$normalizedExpectedHeader] = $internalKey;

                if (!isset($this->internalKeyToFinalNormalizedFileHeader[$internalKey])) {
                    $this->internalKeyToFinalNormalizedFileHeader[$internalKey] = $normalizedExpectedHeader;
                }
            }
        }
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $excelRowIndex => $row) {
            Log::channel('daily')->debug("Importer - Wiersz Excel (indeks $excelRowIndex): " . json_encode($row->toArray()));
            $rowDataAsArray = $row->toArray();
            $mappedData = [];

            foreach ($rowDataAsArray as $normalizedFileHeader => $value) {
                if (isset($this->finalNormalizedFileHeaderToInternalKey[$normalizedFileHeader])) {
                    $internalKey = $this->finalNormalizedFileHeaderToInternalKey[$normalizedFileHeader];
                    $mappedData[$internalKey] = $value;
                }
            }

            $lpFromExcel = $mappedData[self::KEY_LP] ?? null;
            $studentToProcess = null;
            $competitionRegistration = null; // Zmienna na CompetitionRegistration

            if ($lpFromExcel !== null) {
                $registrationIndexInCollection = (int)$lpFromExcel - 1;

                if ($registrationIndexInCollection >= 0 && $this->existingCompetitionRegistrations->has($registrationIndexInCollection)) {
                    $competitionRegistrationToUpdate = $this->existingCompetitionRegistrations->get($registrationIndexInCollection);

                    if ($competitionRegistrationToUpdate && $competitionRegistrationToUpdate->student) {
                        $studentToProcess = $competitionRegistrationToUpdate->student;
                        $competitionRegistration = $competitionRegistrationToUpdate; // Mamy istniejącą rejestrację

                        $studentDataForUpdate = [
                            'name' => $mappedData[self::KEY_NAME] ?? $studentToProcess->name,
                            'last_name' => $mappedData[self::KEY_LAST_NAME] ?? $studentToProcess->last_name,
                            'school' => $mappedData[self::KEY_SCHOOL] ?? $studentToProcess->school,
                            'school_address' => $mappedData[self::KEY_SCHOOL_ADDRESS] ?? $studentToProcess->school_address,
                            'class' => $mappedData[self::KEY_CLASS] ?? $studentToProcess->class,
                            'statement' => $mappedData[self::KEY_STATEMENT] ?? $studentToProcess->statement,
                            'teacher' => $mappedData[self::KEY_TEACHER] ?? $studentToProcess->teacher,
                            'guardian' => $mappedData[self::KEY_GUARDIAN] ?? $studentToProcess->guardian,
                            'contact' => $mappedData[self::KEY_CONTACT] ?? $studentToProcess->contact,
                        ];
                        $studentToProcess->update($studentDataForUpdate);
                        Log::info("Zaktualizowano studenta ID: {$studentToProcess->id} (rejestracja ID: {$competitionRegistration->id}) na podstawie L.p. {$lpFromExcel}.");
                    } else {
                        Log::warning("Import konkursu {$this->competition->id}: Błąd z CompetitionRegistration/studentem dla L.p. {$lpFromExcel}. Nie wykonano aktualizacji.");
                    }
                } else {
                    Log::info("Import konkursu {$this->competition->id}: Nie znaleziono istniejącej CompetitionRegistration dla L.p. {$lpFromExcel}. Rozważam jako nowy wpis (jeśli są dane).");
                }
            }

            // Jeśli nie zaktualizowano/znaleziono studenta przez L.p.
            // ORAZ jeśli mamy dane do stworzenia nowego studenta (np. imię i nazwisko)
            if ($studentToProcess === null && (!empty($mappedData[self::KEY_NAME] ?? null) || !empty($mappedData[self::KEY_LAST_NAME] ?? null))) {
                Log::info("Próba utworzenia nowego studenta dla wiersza Excel (indeks $excelRowIndex).");
                $studentToProcess = Student::updateOrCreate(
                    [ // Kryteria wyszukiwania
                        'name' => $mappedData[self::KEY_NAME] ?? null,
                        'last_name' => $mappedData[self::KEY_LAST_NAME] ?? null,
                        'school' => $mappedData[self::KEY_SCHOOL] ?? null,
                    ],
                    [ // Dane do utworzenia / aktualizacji
                        'school_address' => $mappedData[self::KEY_SCHOOL_ADDRESS] ?? null,
                        'class' => $mappedData[self::KEY_CLASS] ?? null,
                        'statement' => $mappedData[self::KEY_STATEMENT] ?? null,
                        'teacher' => $mappedData[self::KEY_TEACHER] ?? null,
                        'guardian' => $mappedData[self::KEY_GUARDIAN] ?? null,
                        'contact' => $mappedData[self::KEY_CONTACT] ?? null,
                    ]
                );

                // Utwórz nową CompetitionRegistration dla nowo utworzonego/znalezionego studenta
                $competitionRegistration = CompetitionRegistration::firstOrCreate( // <--- POPRAWIONA NAZWA MODELU
                    [
                        'student_id' => $studentToProcess->id,
                        'competition_id' => $this->competition->id,
                    ],
                    [
                        'user_id' => Auth::id(), // lub inny odpowiedni user_id
                    ]
                );
                Log::info("Utworzono/znaleziono studenta ID: {$studentToProcess->id} i CompetitionRegistration ID: {$competitionRegistration->id}.");
            }


            // Jeśli mamy studenta (zaktualizowanego lub nowo utworzonego), przetwarzamy wyniki etapów
            if ($studentToProcess && $competitionRegistration) { // Upewnij się, że mamy też rejestrację
                foreach ($this->internalStageKeysMapping as $internalStageKey => $stageId) {
                    $resultValue = $mappedData[$internalStageKey] ?? null;

                    if (array_key_exists($internalStageKey, $mappedData)) {
                        if ($resultValue !== null && $resultValue !== '') {
                            StageCompetition::updateOrCreate(
                                [
                                    'student_id' => $studentToProcess->id,
                                    'stage_id' => $stageId,
                                    'competition_id' => $this->competition->id, // Upewnij się, że to pole istnieje i jest potrzebne w StageCompetition
                                ],
                                [
                                    'result' => $resultValue,
                                ]
                            );
                        } else {
                            StageCompetition::where('student_id', $studentToProcess->id)
                                          ->where('stage_id', $stageId)
                                          ->where('competition_id', $this->competition->id)
                                          ->delete();
                            Log::info("Usunięto/zaktualizowano wynik dla studenta ID: {$studentToProcess->id}, Etap ID: {$stageId}.");
                        }
                    }
                }
            } else {
                Log::warning("Import konkursu {$this->competition->id}: Nie udało się przetworzyć studenta/rejestracji dla wiersza Excel (indeks $excelRowIndex).");
            }
        }
    }

    public function rules(): array
    {
        $rules = [];

        // Jeśli L.p. jest opcjonalne (bo mogą być nowe wpisy bez L.p.):
        if (isset($this->internalKeyToFinalNormalizedFileHeader[self::KEY_LP])) {
             $rules['*.' . $this->internalKeyToFinalNormalizedFileHeader[self::KEY_LP]] = 'nullable|integer|min:1';
        }

        // Jeśli L.p. nie ma, to imię, nazwisko, szkoła powinny być wymagane
        // To jest trudniejsze do wyrażenia w standardowych regułach, można dodać walidację warunkową
        // lub polegać na logice w collection(), która pomija wiersze bez wystarczających danych.

        // Ogólne reguły formatu, jeśli pola są obecne:
        if (isset($this->internalKeyToFinalNormalizedFileHeader[self::KEY_NAME])) {
             $rules['*.' . $this->internalKeyToFinalNormalizedFileHeader[self::KEY_NAME]] = 'nullable|string|max:255';
        }
        if (isset($this->internalKeyToFinalNormalizedFileHeader[self::KEY_LAST_NAME])) {
             $rules['*.' . $this->internalKeyToFinalNormalizedFileHeader[self::KEY_LAST_NAME]] = 'nullable|string|max:255';
        }
        if (isset($this->internalKeyToFinalNormalizedFileHeader[self::KEY_SCHOOL])) {
             $rules['*.' . $this->internalKeyToFinalNormalizedFileHeader[self::KEY_SCHOOL]] = 'nullable|string|max:255';
        }
        // ... (reszta pól jako nullable|typ|...)

        // Przykład wymagania imienia i nazwiska, jeśli L.p. nie ma (bardziej zaawansowane):
        // $rules['*.' . $this->internalKeyToFinalNormalizedFileHeader[self::KEY_NAME]] = [
        //     Rule::requiredIf(fn ($input) => empty($input[$this->internalKeyToFinalNormalizedFileHeader[self::KEY_LP]])),
        //     'nullable',
        //     'string',
        //     'max:255'
        // ];
        // Potrzebowałbyś `use Illuminate\Validation\Rule;`

        // Prostsze podejście do reguł na razie:
        if (isset($this->internalKeyToFinalNormalizedFileHeader[self::KEY_NAME])) {
             $rules['*.' . $this->internalKeyToFinalNormalizedFileHeader[self::KEY_NAME]] = 'required_without_all:' . ($this->internalKeyToFinalNormalizedFileHeader[self::KEY_LP] ?? 'non_existent_lp') . '|nullable|string|max:255';
        }
        if (isset($this->internalKeyToFinalNormalizedFileHeader[self::KEY_LAST_NAME])) {
             $rules['*.' . $this->internalKeyToFinalNormalizedFileHeader[self::KEY_LAST_NAME]] = 'required_without_all:' . ($this->internalKeyToFinalNormalizedFileHeader[self::KEY_LP] ?? 'non_existent_lp') . '|nullable|string|max:255';
        }
         if (isset($this->internalKeyToFinalNormalizedFileHeader[self::KEY_SCHOOL])) {
             $rules['*.' . $this->internalKeyToFinalNormalizedFileHeader[self::KEY_SCHOOL]] = 'required_without_all:' . ($this->internalKeyToFinalNormalizedFileHeader[self::KEY_LP] ?? 'non_existent_lp') . '|nullable|string|max:255';
        }


        $optionalStringFields = [self::KEY_CLASS, self::KEY_SCHOOL_ADDRESS, self::KEY_STATEMENT, self::KEY_TEACHER, self::KEY_GUARDIAN, self::KEY_CONTACT];
        foreach ($optionalStringFields as $internalKey) {
            if (isset($this->internalKeyToFinalNormalizedFileHeader[$internalKey])) {
                 $rules['*.' . $this->internalKeyToFinalNormalizedFileHeader[$internalKey]] = 'nullable|string|max:255';
            }
        }

        foreach ($this->internalStageKeysMapping as $internalStageKey => $stageId) {
            if (isset($this->internalKeyToFinalNormalizedFileHeader[$internalStageKey])) {
                $rules['*.' . $this->internalKeyToFinalNormalizedFileHeader[$internalStageKey]] = 'nullable|numeric';
            }
        }
        return $rules;
    }



    public function headingRow(): int
    {
        return 1;
    }
}