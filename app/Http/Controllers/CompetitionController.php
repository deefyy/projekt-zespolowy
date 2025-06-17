<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Competition;
use App\Models\Stage;
use App\Models\Student;
use App\Models\CompetitionRegistration;
use App\Models\Forum;
use App\Models\StageCompetition;
use App\Notifications\CompetitionCreatedNotification;
use App\Models\User;
use App\Notifications\CoOrganizerInvitation;
use App\Exports\CompetitionsExport;
use App\Imports\CompetitionsImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Validator;

class CompetitionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $competitions = Competition::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        })->get();

        return view('competitions.index', compact('competitions', 'search'));
    }

    public function show(Request $request, Competition $competition)
    {
        $perPage = $request->integer('perPage', 10);
    $search = $request->input('search');
    $sort = $request->input('sort', 'created_at');
    $direction = $request->input('direction', 'asc');

    $allowedSorts = ['created_at', 'student_id', 'student.class'];

    if (!in_array($sort, $allowedSorts)) {
        $sort = 'created_at';
    }

    $userRegistrations = collect();
    $competition->load('stages');

    if (auth()->check()) {
        $query = $competition->registrations()->with('student');

        // Admin i organizator
        if (auth()->user()->role === 'admin' || auth()->user()->role === 'organizator') {
            $query
                ->when($search, function ($q) use ($search) {
                    $q->whereHas('student', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('class', 'like', "%{$search}%")
                            ->orWhere('school', 'like', "%{$search}%");
                    });
                });
                if ($sort === 'student.class') {
                    $query->join('students', 'competition_registrations.student_id', '=', 'students.id')
                        ->orderByRaw("CAST(SUBSTRING_INDEX(students.class, ',', 1) AS UNSIGNED) {$direction}")
                        ->select('competition_registrations.*');
                } else {
                    $query->orderBy($sort, $direction);
                }

            $userRegistrations = $query->paginate($perPage)->withQueryString();
            // User
        } else {
            $query->where('user_id', auth()->id())
                ->when($search, function ($q) use ($search) {
                    $q->whereHas('student', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('class', 'like', "%{$search}%")
                            ->orWhere('school', 'like', "%{$search}%");
                    });
                });
                if ($sort === 'student.class') {
                    $query->join('students', 'competition_registrations.student_id', '=', 'students.id')
                            ->orderByRaw("CAST(SUBSTRING_INDEX(students.class, ',', 1) AS UNSIGNED) {$direction}")
                            ->select('competition_registrations.*');
                    } else {
                        $query->orderBy($sort, $direction);
                    }

            $userRegistrations = $query->paginate($perPage)->withQueryString();
        }
    }

    return view('competitions.show', compact('competition', 'userRegistrations'));
    }

    public function create()
    {
        $students = Student::all();
        return view('competitions.create', compact('students'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'name'                  => 'required|string',
        'description'           => 'required|string|max:1000',
        'start_date'            => 'required|date',
        'end_date'              => 'required|date|after_or_equal:start_date',
        'registration_deadline' => 'required|date|before_or_equal:start_date',
        'stages_count'          => 'required|integer|min:1|max:10',
        'poster'                => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    /* 1️⃣ zapis pliku (jeśli jest) */
    if ($request->hasFile('poster')) {
        // posters/20250603_abcdef.jpg  – na dysku domyślnym
        $path = $request->file('poster')->store('posters');
        $validated['poster_path'] = $path;
    }

    /* 2️⃣ pozostała logika – bez zmian */
    $validated['user_id'] = $request->user()->id;
    $competition = Competition::create($validated);

    for ($i = 1; $i <= $validated['stages_count']; $i++) {
        Stage::create([
            'stage'          => $i,
            'date'           => now()->addWeeks($i),
            'competition_id' => $competition->id,
        ]);
    }

    Forum::create([
        'topic'          => $competition->name,
        'added_date'     => now(),
        'description'    => $competition->description,
        'competition_id' => $competition->id,
    ]);

    auth()->user()->notify(new CompetitionCreatedNotification($competition));

    return redirect()
        ->route('competitions.index')
        ->with('success', 'Konkurs został dodany!');
}
    public function edit(Competition $competition)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'organizator') {
            abort(403, 'Brak dostępu');
        }

        return view('competitions.edit', compact('competition'));
    }

    public function update(Request $request, Competition $competition)
    {
        // Sprawdzenie uprawnień
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'organizator') {
            abort(403, 'Brak dostępu');
        }

        // Walidacja danych wejściowych
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'description'           => 'nullable|string|max:1000',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after_or_equal:start_date',
            'registration_deadline' => 'required|date|before_or_equal:end_date',
            'stages_count'          => 'required|integer|min:1|max:10',
            'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

        ]);
            if ($request->hasFile('poster')) {
                // (opcjonalnie) usuń stary plik: Storage::delete($competition->poster_path);
                $path = $request->file('poster')->store('posters');
                $validated['poster_path'] = $path;
            }
        // Aktualizacja danych konkursu
        $competition->update($validated);

        $competition->update($validated);

            $competition->forum->update([
                'topic'       => $competition->name,
                'description' => $competition->description,
            ]);

        $currentStages  = $competition->stages()->count();
        $newStagesCount = $validated['stages_count'];

        if ($newStagesCount > $currentStages) {
            // Dodawanie brakujących etapów
            for ($i = $currentStages + 1; $i <= $newStagesCount; $i++) {
                $newStage = Stage::create([
                    'stage'           => $i,
                    'date'            => now()->addWeeks($i),
                    'competition_id'  => $competition->id,
                ]);

                // ► DODAJ dla każdego ucznia rekord stages_competition
                $competition->registrations->each(function($reg) use($competition, $newStage) {
                    StageCompetition::create([
                        'competition_id' => $competition->id,
                        'stage_id'       => $newStage->id,
                        'student_id'     => $reg->student_id,
                        'result'         => null,
                    ]);
                });
            }
        }
        elseif ($newStagesCount < $currentStages) {
            // Oblicz, które etapy usuwamy
            $toDeleteNumbers = range($newStagesCount + 1, $currentStages);

            // Pobierz ID etapów do usunięcia
            $stagesToDelete = $competition->stages()
                ->whereIn('stage', $toDeleteNumbers)
                ->pluck('id');

            // Usuń wpisy pivot
            StageCompetition::where('competition_id', $competition->id)
                ->whereIn('stage_id', $stagesToDelete)
                ->delete();

            // Usuń same etapy
            $competition->stages()
                ->whereIn('id', $stagesToDelete)
                ->delete();
        }

        // Odświeżenie widoku z odpowiednim komunikatem
        return redirect()->route('competitions.show', $competition)
            ->with('success', 'Konkurs został zaktualizowany.');
    }


    public function destroy(Competition $competition)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'organizator') {
            abort(403, 'Brak dostępu');
        }

        $competition->delete();

        return redirect()->route('competitions.index')->with('success', 'Konkurs został usunięty.');
    }


    private function schoolClasses(): array
    {
        return [
            '1, Szkoła podstawowa','2, Szkoła podstawowa','3, Szkoła podstawowa',
            '4, Szkoła podstawowa','5, Szkoła podstawowa','6, Szkoła podstawowa',
            '7, Szkoła podstawowa','8, Szkoła podstawowa',
            '1, Szkoła średnia','2, Szkoła średnia','3, Szkoła średnia',
            '4, Szkoła średnia','5, Szkoła średnia',
        ];
    }
    public function showRegistrationForm(Competition $competition)
    {
        $classes = $this->schoolClasses();
        return view('competitions.register', compact('competition','classes'));
    }

    public function registerStudents(Request $request, Competition $competition)
    {
        $data = $request->validate([
            'school'               => 'required|string',
            'school_address'       => 'nullable|string',
            'teacher'              => 'nullable|string',
            'guardian'             => 'nullable|string',
            'contact'              => 'required|string',
            'students'             => 'required|array|min:1',
            'students.*.name'      => 'required|string',
            'students.*.last_name' => 'required|string',
            'students.*.class'     => 'required|string',
            'students.*.statement' => 'required|boolean',
        ]);

        foreach ($data['students'] as $stu) {
            $student = Student::create([
                'name'      => $stu['name'],
                'last_name' => $stu['last_name'],
                'class'     => $stu['class'],
                'school'    => $data['school'],
                'school_address' => $data['school_address'] ?? null,
                'teacher'   => $data['teacher'] ?? null,
                'guardian'  => $data['guardian'] ?? null,
                'contact'   => $data['contact'],
                'statement' => $stu['statement'],
            ]);

            CompetitionRegistration::create([
                'competition_id' => $competition->id,
                'user_id'        => auth()->id(),
                'student_id'     => $student->id,
            ]);
            foreach ($competition->stages as $stage) {
            StageCompetition::create([
                'competition_id' => $competition->id,
                'stage_id'       => $stage->id,
                'student_id'     => $student->id,
                'result'         => null,
            ]);
            }
        }



        if (now()->greaterThan($competition->registration_deadline)) {
            return redirect()->back()->with('error', 'Termin rejestracji minął.');
        }

        return redirect()
            ->route('competitions.show', $competition)
            ->with('success', 'Uczniowie zostali zapisani na konkurs!');
    }

    public function editStudent(Student $student)
    {
        $classes = $this->schoolClasses();
        $isAdmin = auth()->user()->role === 'admin';
        $isOwner = CompetitionRegistration::where('student_id', $student->id)
            ->where('user_id', auth()->id())
            ->exists();
        $isOrganizator = auth()->user()->role === 'organizator';
        if (!($isAdmin || $isOrganizator || $isOwner)) {
            abort(403, 'Brak dostępu');
        }


        return view('competitions.students.edit', compact('student', 'classes'));
    }

    public function updateStudent(Request $request, Student $student)
    {
        $classes = $this->schoolClasses();
        $isAdmin = auth()->user()->role === 'admin';
        $isOwner = CompetitionRegistration::where('student_id', $student->id)
            ->where('user_id', auth()->id())
            ->exists();
        $isOrganizator = auth()->user()->role === 'organizator';
        if (!($isAdmin || $isOrganizator || $isOwner)) {
            abort(403, 'Brak dostępu');
        }

        $data = $request->validate([
            'name'      => 'required|string',
            'last_name' => 'required|string',
            'class'     => 'required|string',
            'school'    => 'required|string',
            'school_address'  => 'nullable|string',
            'contact'   => 'required|string',
            'teacher'   => 'nullable|string',
            'guardian'  => 'nullable|string',
            'statement' => 'required|boolean',
        ]);

        $student->update($data);

        $registration = $student->competitionRegistrations()->first();

        if ($registration && $registration->competition) {
            return redirect()
                ->route('competitions.show', $registration->competition->id)
                ->with('success', 'Uczeń został zaktualizowany.');
        }

        return redirect()
            ->route('competitions.index')
            ->with('success', 'Uczeń został zaktualizowany, ale nie przypisano konkursu.');
    }

    public function deleteStudent(Student $student)
    {
        $isAdmin = auth()->user()->role === 'admin';
        $isOwner = CompetitionRegistration::where('student_id', $student->id)
            ->where('user_id', auth()->id())
            ->exists();
        $isOrganizator = auth()->user()->role === 'organizator';
        if (!($isAdmin || $isOrganizator || $isOwner)) {
            abort(403, 'Brak dostępu');
        }

        $student->delete();

        return redirect()->back()->with('success', 'Uczeń został usunięty.');
    }

        private function normalizeHeader(string $header): string
    {
        $header = trim($header);
        if (preg_match('/^(\d+)\s*ETAP$/i', $header, $matches)) {
            return strtolower($matches[1] . '_etap');
        } elseif (strcasecmp($header, 'ID Systemowe') == 0) {
            return 'id_systemowe';
        }
        return Str::snake(Str::ascii(strtolower($header)));
    }

    private function getExpectedHeaders(Competition $competition): array
    {
        $baseHeaders = ['ID Systemowe', 'Imię ucznia', 'Nazwisko ucznia', 'Klasa', 'Nazwa szkoły', 'Adres szkoły', 'Oświadczenie', 'Nauczyciel', 'Rodzic', 'Kontakt'];
        $stageHeaders = $competition->stages()->orderBy('stage')->get()->map(fn($stage) => "{$stage->stage} ETAP")->toArray();
        $sumHeader = ['SUMA'];
        return array_merge($baseHeaders, $stageHeaders, $sumHeader);
    }

    public function showImportForm(Competition $competition)
    {
        return view('competitions.import.upload_form', compact('competition'));
    }

    public function handleImportUpload(Request $request, Competition $competition)
    {
        $request->validate(['excel_file' => 'required|mimes:xlsx,xls,csv']);
        $file = $request->file('excel_file');
        $path = $file->store('temp_imports', 'local');
        $filePath = Storage::disk('local')->path($path);

        if (!Storage::disk('local')->exists($path)) {
            return back()->with('error', 'Nie udało się zapisać pliku na serwerze.');
        }

        $headingsInFile = (new HeadingRowImport)->toArray($filePath)[0][0] ?? [];
        $expectedHeaders = $this->getExpectedHeaders($competition);
        
        $normalizedFileHeadingsMap = [];
        foreach ($headingsInFile as $header) {
            $normalizedFileHeadingsMap[$this->normalizeHeader($header)] = $header;
        }

        $autoMappings = [];
        $unmatchedExpectedHeaders = [];
        $availableHeadingsFromUserFile = $headingsInFile;

        foreach ($expectedHeaders as $expected) {
            $normalizedExpected = $this->normalizeHeader($expected);

            if (isset($normalizedFileHeadingsMap[$normalizedExpected])) {
                $actualHeader = $normalizedFileHeadingsMap[$normalizedExpected];
                $autoMappings[$expected] = $actualHeader;
                if (($key = array_search($actualHeader, $availableHeadingsFromUserFile)) !== false) {
                    unset($availableHeadingsFromUserFile[$key]);
                }
            } else {
                $unmatchedExpectedHeaders[] = $expected;
            }
        }
        
        session([
            'import_file_path' => $path,
            'import_disk_name' => 'local',
            'import_headings_in_file' => $headingsInFile,
            'import_unmatched_expected_headers' => $unmatchedExpectedHeaders,
            'import_available_headings_in_file' => array_values($availableHeadingsFromUserFile),
            'import_auto_mappings' => $autoMappings,
        ]);

        if (empty($unmatchedExpectedHeaders)) {
            return redirect()->route('competitions.showSummary', $competition);
        } else {
            return redirect()->route('competitions.showMappingForm', $competition);
        }
    }

    public function showMappingForm(Competition $competition)
    {
        if (!session()->has('import_file_path')) {
            return redirect()->route('competitions.showImportForm', $competition)->with('error', 'Najpierw wgraj plik.');
        }
        return view('competitions.import.mapping_form', [
            'competition' => $competition,
            'unmatchedHeaders' => session('import_unmatched_expected_headers'),
            'availableHeadings' => session('import_available_headings_in_file'),
        ]);
    }
    
    public function handleMapping(Request $request, Competition $competition)
    {
        if (!session()->has('import_file_path')) {
            return redirect()->route('competitions.showImportForm', $competition)->with('error', 'Sesja wygasła. Proszę wgrać plik ponownie.');
        }
        $request->validate(['column_mappings' => 'required|array']);
        $autoMappings = session('import_auto_mappings', []);
        $manualMappings = $request->input('column_mappings', []);
        $finalMappings = array_merge($autoMappings, $manualMappings);
        session(['import_final_mappings' => $finalMappings]);
        return redirect()->route('competitions.showSummary', $competition);
    }
    
    public function showSummary(Competition $competition)
    {
        if (!session()->has('import_file_path')) {
            return redirect()->route('competitions.showImportForm', $competition)->with('error', 'Najpierw wgraj plik.');
        }

        $finalMappings = session('import_final_mappings', session('import_auto_mappings', []));
        $mappingsForImporter = [];
        foreach ($finalMappings as $expected => $actual) {
            if (!empty($actual)) {
                $mappingsForImporter[$actual] = $expected;
            }
        }

        $path = session('import_file_path');
        $disk = session('import_disk_name', 'local');
        $filePath = Storage::disk($disk)->path($path);
        
        $importer = new CompetitionsImport($competition, $mappingsForImporter);
        $rows = Excel::toCollection($importer, $filePath)[0];

        $changes = ['updated' => [], 'created' => [], 'no_change' => []];
        $expectedHeadersForDiff = array_diff($this->getExpectedHeaders($competition), ['ID Systemowe', 'SUMA']);
        $competitionStages = $competition->stages()->orderBy('stage')->get();

        foreach ($rows as $row) {
            $excelRowData = $row->toArray();
            $systemId = $importer->getRowValueForSummary($excelRowData, 'ID Systemowe');
            $studentName = $importer->getRowValueForSummary($excelRowData, 'Imię ucznia');
            $studentLastName = $importer->getRowValueForSummary($excelRowData, 'Nazwisko ucznia');
            $displayName = trim("{$studentName} {$studentLastName}");

            if (is_numeric($systemId)) {
                $registration = CompetitionRegistration::with('student')->find((int)$systemId);
                // --- POCZĄTEK POPRAWKI ---
                if ($registration && $registration->student && $registration->competition_id === $competition->id) {
                // --- KONIEC POPRAWKI ---
                    $diff = [];
                    $studentInDb = $registration->student;
                    foreach ($expectedHeadersForDiff as $header) {
                        $excelValue = (string)$importer->getRowValueForSummary($excelRowData, $header, '');
                        $dbValue = '';
                        if (str_contains($header, 'ETAP')) {
                            preg_match('/(\d+)\s*ETAP/', $header, $matches);
                            $stageNumber = $matches[1];
                            $stage = $competitionStages->where('stage', $stageNumber)->first();
                            if ($stage) {
                               $stageResult = StageCompetition::where('student_id', $studentInDb->id)->where('stage_id', $stage->id)->where('competition_id', $competition->id)->first();
                               $dbValue = (string)($stageResult->result ?? '');
                            }
                        } else {
                            $attribute = match($header) {
                                'Imię ucznia' => 'name', 'Nazwisko ucznia' => 'last_name',
                                'Klasa' => 'class', 'Nazwa szkoły' => 'school',
                                'Adres szkoły' => 'school_address', 'Nauczyciel' => 'teacher',
                                'Rodzic' => 'guardian', 'Kontakt' => 'contact',
                                'Oświadczenie' => 'statement', default => null
                            };
                            if ($attribute) {
                               if ($attribute === 'statement') {
                                   $dbValue = $studentInDb->statement ? 'true' : 'false';
                                   $excelValue = $importer->parseBoolean($importer->getRowValueForSummary($excelRowData, $header, '')) ? 'true' : 'false';
                               } else {
                                   $dbValue = (string)($studentInDb->$attribute ?? '');
                               }
                            }
                        }
                        if ($excelValue !== $dbValue) {
                            $diff[$header] = ['old' => $dbValue, 'new' => $excelValue];
                        }
                    }
                    if (!empty($diff)) {
                        $changes['updated'][] = ['name' => $displayName, 'id' => $systemId, 'diff' => $diff];
                    } else {
                        $changes['no_change'][] = ['name' => $displayName];
                    }
                } else {
                    $changes['created'][] = ['name' => $displayName, 'reason' => 'Nowy wpis (ID z pliku nie znaleziono w bazie)'];
                }
            } else {
                if(!empty($displayName)) $changes['created'][] = ['name' => $displayName, 'reason' => 'Nowy wpis (brak ID w pliku)'];
            }
        }
        return view('competitions.import.summary', ['competition' => $competition, 'changes' => $changes]);
    }

    public function processImport(Request $request, Competition $competition)
    {
        if (!session()->has('import_file_path')) {
            return redirect()->route('competitions.showImportForm', $competition)->with('error', 'Sesja wygasła. Proszę wgrać plik ponownie.');
        }
        $path = session('import_file_path');
        $disk = session('import_disk_name', 'local');
        $filePath = Storage::disk($disk)->path($path);

        $finalMappingsFromSession = session('import_final_mappings', []);
        $manualMappings = $request->input('column_mappings', []);
        $finalMappings = array_merge($finalMappingsFromSession, $manualMappings);

        if (empty($finalMappings)) {
            $finalMappings = session('import_auto_mappings', []);
        }

        $mappingsForImporter = [];
        foreach ($finalMappings as $expectedHeader => $userProvidedHeader) {
            if (!empty($userProvidedHeader)) {
                $mappingsForImporter[$userProvidedHeader] = $expectedHeader;
            }
        }
        
        if (empty($mappingsForImporter)) {
            return redirect()->route('competitions.showImportForm', $competition)->with('error', 'Wystąpił błąd sesji lub nie zmapowano żadnych kolumn. Spróbuj ponownie.');
        }

        DB::beginTransaction();
        try {
            Excel::import(new CompetitionsImport($competition, $mappingsForImporter), $filePath);
            DB::commit();
            session()->forget(['import_file_path', 'import_disk_name', 'import_headings_in_file', 'import_unmatched_expected_headers', 'import_available_headings_in_file', 'import_auto_mappings', 'import_final_mappings']);
            Storage::delete($path);
            return redirect()->route('competitions.show', $competition)->with('success', 'Dane zostały pomyślnie zaimportowane!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Błąd importu Excela dla konkursu {$competition->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $errorMessage = $e->getMessage();
            $redirectRoute = 'competitions.showMappingForm'; 
            if (str_contains($errorMessage, "Import przerwany: Weryfikacja wstępna pliku nie powiodła się")) {
                return redirect()->route($redirectRoute, $competition)->with('error_critical', $errorMessage);
            }
            return redirect()->route($redirectRoute, $competition)->with('error', 'Wystąpił nieoczekiwany błąd podczas importu: ' . $errorMessage);
        }
    }
    
    public function exportRegistrations(Competition $competition)
    {
        return Excel::download(new CompetitionsExport($competition), 'rejestracje.xlsx');
    }

    public function editPoints(Request $request, Competition $competition)
{
    $userRole = auth()->user()?->role;
    if (! ($userRole === 'admin' || $userRole === 'organizator')) {
        abort(403, 'Brak dostępu');
    }

    $search = $request->input('search');

    $studentIds = $competition->registrations()->pluck('student_id');

    $studentsQuery = Student::whereIn('id', $studentIds);

    if ($search) {
        $studentsQuery->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('class', 'like', "%{$search}%");
        });
    }

    $students = $studentsQuery->get();

    $stages = $competition->stages()->get();

    $points = [];
    StageCompetition::where('competition_id', $competition->id)
        ->get()
        ->each(function ($rec) use (&$points) {
            $points[$rec->student_id][$rec->stage_id] = $rec->result;
        });

    return view('competitions.points.edit', compact(
        'competition', 'students', 'stages', 'points', 'search'
    ));
}

    public function updatePoints(Request $request, Competition $competition)
    {
        $userRole = auth()->user()?->role;
        if (! ($userRole === 'admin' || $userRole === 'organizator')) {
            abort(403, 'Brak dostępu');
        }

        $request->validate([
            'points.*.*' => 'nullable|integer|min:0',
    ]);

    foreach ($request->input('points', []) as $studentId => $stagePoints) {
        foreach ($stagePoints as $stageId => $value) {

            if ($value === null || $value === '') {
                continue;
            }

            StageCompetition::updateOrCreate(
                [
                    'competition_id' => $competition->id,
                    'student_id'     => $studentId,
                    'stage_id'       => $stageId,
                ],
                [
                    'result' => $value 
                ]
            );
        }
    }

    return redirect()
        ->route('competitions.show', $competition)
        ->with('success', 'Punkty zostały zapisane.');
    }   

    public function inviteCoorganizer(Request $request, Competition $competition)
    {
        // Only allow owner or admin
        $userRole = auth()->user()?->role;
        $user = auth()->user();
        if (!($user->id === $competition->user_id || $userRole === 'admin')) {
            abort(403, 'Unauthorized');
        }

        // Validate email
        $data = $request->validate([
            'email' => 'required|email'
        ]);

        // Find the user by email
        $invitee = User::where('email', $data['email'])->first();
        if (! $invitee) {
            return back()->withErrors(['email' => 'No user found with that email.']);
        }

        if ($invitee->role !== 'organizator') {
        return back()->withErrors(['email' => 'User must have the “organizator” role.']);
    }

        // Prevent duplicate co-organizer entry
        // Option 1: check collection
        if ($competition->coOrganizers()->where('user_id', $invitee->id)->exists()) {
            return back()->with('status', 'User is already a co-organizer.');
        }

        // Option 2: attach without duplicates using syncWithoutDetaching:contentReference[oaicite:1]{index=1}
        $competition->coOrganizers()->syncWithoutDetaching($invitee->id);

        // Send notification with a link to the competition
        $invitee->notify(new CoOrganizerInvitation($competition));

        return back()->with('status', 'Co-organizer added and notified.');
    }
}
