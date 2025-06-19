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
    protected array $headerMap = [];

    private const INITIAL_VERIFICATION_THRESHOLD_PERCENT = 0.5;
    private const INITIAL_VERIFICATION_ROWS_TO_CHECK_PERCENT = 0.1;
    private const MIN_ROWS_TO_CHECK = 2;
    private const MAX_ROWS_TO_CHECK = 20;

    public function __construct(Competition $competition, array $columnMappingsFromController = [])
    {
        $this->competition = $competition;
        $this->columnMappings = $columnMappingsFromController;
        $this->stages = $competition->stages()->orderBy('stage')->get();
        $this->buildHeaderMap();
    }

    private function buildHeaderMap()
    {
        $systemHeaders = ['L.p.', 'ID Systemowe', 'Imię ucznia', 'Nazwisko ucznia', 'Klasa', 'Nazwa szkoły', 'Adres szkoły', 'Oświadczenie', 'Nauczyciel', 'Rodzic', 'Kontakt'];
        foreach ($this->stages as $stage) {
            $systemHeaders[] = "{$stage->stage} ETAP";
        }
        $systemHeaders[] = 'SUMA';
        foreach ($systemHeaders as $systemHeader) {
            $headerInUserFile = array_search($systemHeader, $this->columnMappings) ?: $systemHeader;
            $this->headerMap[$systemHeader] = Str::snake(Str::ascii(strtolower(trim($headerInUserFile))));
        }
    }

    public function getRowValue(array $excelRowData, string $systemStandardHeaderName, $default = null)
    {
        $maatwebsiteKey = $this->headerMap[$systemStandardHeaderName] ?? null;
        if (preg_match('/^(\d+)\s*ETAP$/i', trim($systemStandardHeaderName), $matches)) {
            $maatwebsiteKey = strtolower($matches[1] . '_etap');
        } elseif (strcasecmp(trim($systemStandardHeaderName), 'ID Systemowe') == 0) {
            $maatwebsiteKey = 'id_systemowe';
        } elseif (strcasecmp(trim($systemStandardHeaderName), 'L.p.') == 0) {
            $maatwebsiteKey = 'l_p';
        }
        if ($maatwebsiteKey && array_key_exists($maatwebsiteKey, $excelRowData)) {
            return $excelRowData[$maatwebsiteKey];
        }
        return $default;
    }
    
    public function prepareForValidation(array $data, int $index): array
    {
        foreach ($data as $key => &$value) {
            if ($value === null) {
                $value = '';
            } else {
                $value = (string)$value;
            }
        }
        return $data;
    }

    public function collection(IlluminateCollection $rows)
    {
        $logChannel = Log::channel('importer');
        if ($rows->isEmpty()) {
            $logChannel->info("Plik dla konkursu ID: {$this->competition->id} jest pusty.");
            return;
        }
        
        $totalRows = $rows->count();
        $rowsToCheckCount = $totalRows > 1 ? max(self::MIN_ROWS_TO_CHECK, min(self::MAX_ROWS_TO_CHECK, (int)ceil($totalRows * self::INITIAL_VERIFICATION_ROWS_TO_CHECK_PERCENT))) : $totalRows;
        if ($totalRows < self::MIN_ROWS_TO_CHECK) $rowsToCheckCount = $totalRows;
        $sampleRows = ($totalRows <= $rowsToCheckCount) ? $rows : $rows->random($rowsToCheckCount);
        $verifiedMatchCount = 0;

        foreach ($sampleRows as $sampleRow) {
            $excelRowData = $sampleRow->toArray();
            $systemId = $this->getRowValue($excelRowData, 'ID Systemowe');
            if (is_numeric($systemId)) {
                $registration = CompetitionRegistration::with('student')->find((int)$systemId);
                if ($registration && $registration->student && $registration->competition_id === $this->competition->id) {
                    if (strtolower(trim($registration->student->name)) === strtolower(trim($this->getRowValue($excelRowData, 'Imię ucznia'))) && strtolower(trim($registration->student->last_name)) === strtolower(trim($this->getRowValue($excelRowData, 'Nazwisko ucznia'))) && (string)$registration->student->class === (string)$this->getRowValue($excelRowData, 'Klasa') && strtolower(trim($registration->student->school)) === strtolower(trim($this->getRowValue($excelRowData, 'Nazwa szkoły')))) {
                        $verifiedMatchCount++;
                    }
                }
            }
        }
        
        $failureThreshold = $rowsToCheckCount * (1 - self::INITIAL_VERIFICATION_THRESHOLD_PERCENT);
        if (($rowsToCheckCount - $verifiedMatchCount) > $failureThreshold && $totalRows > 1) {
            throw new Exception("Import przerwany: Plik wydaje się niespójny. Z {$rowsToCheckCount} sprawdzonych wierszy, tylko {$verifiedMatchCount} pasuje do danych w systemie.");
        }
        $logChannel->info("Weryfikacja wstępna dla konkursu ID: {$this->competition->id} zakończona sukcesem. Przetwarzanie {$totalRows} wierszy.");

        foreach ($rows as $rowIndex => $row) {
            $excelRowData = $row->toArray();
            $systemId = $this->getRowValue($excelRowData, 'ID Systemowe');
            $logContext = ['row' => $rowIndex + 2];
            
            if (!is_numeric($systemId)) {
                $logChannel->warning("Pominięto wiersz: brak poprawnego ID Systemowego.", $logContext);
                continue;
            }

            $registrationToUpdate = CompetitionRegistration::with('student')->where('id', (int)$systemId)->where('competition_id', $this->competition->id)->first();

            if ($registrationToUpdate && $registrationToUpdate->student) {
                $student = $registrationToUpdate->student;
                $studentDataToUpdate = ['name' => $this->getRowValue($excelRowData, 'Imię ucznia', $student->name) ?? '', 'last_name' => $this->getRowValue($excelRowData, 'Nazwisko ucznia', $student->last_name) ?? '', 'class' => (string)($this->getRowValue($excelRowData, 'Klasa', $student->class) ?? ''), 'school' => $this->getRowValue($excelRowData, 'Nazwa szkoły', $student->school) ?? '', 'school_address' => $this->getRowValue($excelRowData, 'Adres szkoły', $student->school_address) ?? '', 'teacher' => $this->getRowValue($excelRowData, 'Nauczyciel', $student->teacher) ?? '', 'guardian' => $this->getRowValue($excelRowData, 'Rodzic', $student->guardian) ?? '', 'contact' => $this->getRowValue($excelRowData, 'Kontakt', $student->contact) ?? '', 'statement' => $this->parseBoolean($this->getRowValue($excelRowData, 'Oświadczenie', $student->statement))];
                $student->update($studentDataToUpdate);
                $logChannel->info("Zaktualizowano studenta.", array_merge($logContext, ['student_id' => $student->id]));

                if ($this->stages->isNotEmpty()) {
                    foreach ($this->stages as $stage) {
                        $stageSystemHeader = "{$stage->stage} ETAP";
                        $resultValue = $this->getRowValue($excelRowData, $stageSystemHeader);

                        StageCompetition::updateOrCreate(
                            ['competition_id' => $this->competition->id, 'stage_id' => $stage->id, 'student_id' => $student->id],
                            ['result' => (string)$resultValue]
                        );
                    }
                }
            } else {
                $logChannel->warning("Pominięto wiersz: nie znaleziono rekordu w bazie dla ID Systemowego.", array_merge($logContext, ['system_id' => $systemId]));
                continue;
            }
        }
    }

    public function parseBoolean($value): bool
    {
        if (is_bool($value)) return $value;
        if (is_string($value)) {
            $val = strtolower(trim($value));
            if (in_array($val, ['true', '1', 'yes', 'prawda', 'tak', 'on'])) return true;
        }
        if (is_numeric($value)) return ((int)$value === 1);
        return false;
    }

    public function rules(): array
    {
        $rules = [];
        foreach($this->headerMap as $systemHeader => $maatwebsiteKey) {
            $rule = null;
            switch ($systemHeader) {
                case 'ID Systemowe': $rule = ['required', 'integer', 'min:1']; break;
                case 'L.p.': $rule = ['nullable', 'integer']; break;
                case 'Imię ucznia': case 'Nazwisko ucznia': case 'Nazwa szkoły':
                case 'Nauczyciel': case 'Rodzic': case 'Kontakt': case 'Klasa':
                    $rule = ['nullable', 'string', 'max:255']; break;
                case 'Adres szkoły': $rule = ['nullable', 'string', 'max:1000']; break;
                case 'Oświadczenie': $rule = ['nullable']; break;
                default:
                    if (str_contains($systemHeader, 'ETAP')) {
                        $rule = ['nullable', 'string'];
                    }
                    break;
            }
            if ($rule) $rules[$maatwebsiteKey] = $rule;
        }
        return $rules;
    }
}