<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Competition;
use App\Models\Stage;
use App\Models\Student;
use App\Models\CompetitionRegistration;
use App\Models\Forum;
use App\Models\StageCompetition;
use App\Notifications\CompetitionCreatedNotification;

use App\Exports\CompetitionsExport;
use App\Imports\CompetitionsImport;
use Maatwebsite\Excel\Facades\Excel;

class CompetitionController extends Controller
{
    public function index()
    {
        $competitions = Competition::all();
        return view('competitions.index', compact('competitions'));
    }

    public function show(Competition $competition)
    {
        $userRegistrations = collect();
        $competition->load('stages');
        if (auth()->check()) {
            if (auth()->user()->role === 'admin' || auth()->user()->role === 'organizator') {
                $userRegistrations = $competition->registrations()->with('student')->get();
            } else {
                $userRegistrations = $competition->registrations()
                    ->where('user_id', auth()->id())
                    ->with(['student.stageCompetitions'])
                    ->get();
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
            'description'           => 'required|string',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after_or_equal:start_date',
            'registration_deadline' => 'required|date|before_or_equal:start_date',
            'stages_count'          => 'required|integer|min:1|max:10', 
        ]);

        $validated['user_id'] = $request->user()->id;

        $competition = Competition::create($validated);

        // Dodawanie etapów na podstawie 'stages_count'
        for ($i = 1; $i <= $validated['stages_count']; $i++) {
            Stage::create([
                'stage'            => $i,
                'date'            => now()->addWeeks($i),
                'competition_id'  => $competition->id,
            ]);
        }

        Forum::create([
            'topic'          => $competition->name,
            'added_date'     => now(),
            'description'    => $competition->description,
            'competition_id' => $competition->id,
        ]);

        auth()->user()->notify(new CompetitionCreatedNotification($competition));

        return redirect()->route('competitions.index')->with('success', 'Konkurs został dodany!');
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
            'description'           => 'nullable|string',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after_or_equal:start_date',
            'registration_deadline' => 'required|date|before_or_equal:end_date',
            'stages_count'          => 'required|integer|min:1|max:10',
        ]);

        // Aktualizacja danych konkursu
        $competition->update($validated);

        // 🔍 **Pobranie aktualnej liczby etapów**
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
        if ((!$isAdmin || !$isOrganizator) && !$isOwner ) {
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
        if ((!$isAdmin || !$isOrganizator) && !$isOwner ) {
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
        if ((!$isAdmin || !$isOrganizator) && !$isOwner ) {
            abort(403, 'Brak dostępu');
        }

        $student->delete();

        return redirect()->back()->with('success', 'Uczeń został usunięty.');
    }

    public function exportRegistrations(Competition $competition)
    {
        $fileName = 'konkurs.xlsx';
        return Excel::download(new CompetitionsExport($competition), $fileName);
    }

    private function getExpectedHeaders(Competition $competition): array
    {
        $baseHeaders = [
            'L.p.', 'Imię ucznia', 'Nazwisko ucznia', 'Klasa', 'Nazwa szkoły',
            'Oświadczenie', 'Nauczyciel', 'Rodzic', 'Kontakt',
        ];

        $stageHeaders = $competition->stages()->orderBy('stage')->get()->map(function ($stage) {
            return "{$stage->stage} ETAP";
        })->toArray();

        $sumHeader = ['SUMA'];

        return array_merge($baseHeaders, $stageHeaders, $sumHeader);
    }

    public function showImportRegistrationsForm(Competition $competition)
    {
        $expectedHeaders = $this->getExpectedHeaders($competition);
        return view('competitions.import_form', compact('competition', 'expectedHeaders'));
    }

    public function importRegistrations(Request $request, Competition $competition)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv',
            'column_mappings' => 'nullable|array',
            'column_mappings.*' => 'nullable|string',
        ]);

        $file = $request->file('excel_file');

        $mappingsForImporter = [];
        if ($request->has('column_mappings')) {
            foreach ($request->input('column_mappings') as $expectedHeader => $userProvidedHeader) {
                if (!empty(trim($userProvidedHeader))) {
                    $mappingsForImporter[trim($userProvidedHeader)] = $expectedHeader;
                }
            }
        }

        DB::beginTransaction();

        try {
            Excel::import(new CompetitionsImport($competition, $mappingsForImporter), $file);
            DB::commit();
            return back()->with('success', 'Dane zostały pomyślnie zaimportowane!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();
            return back()->withErrors(['excel_file' => 'Wystąpiły błędy walidacji podczas importu.'])
                         ->with('validation_failures', $failures);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Błąd importu Excela dla konkursu {$competition->id}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->with('error', 'Wystąpił nieoczekiwany błąd podczas importu: ' . $e->getMessage());
        }
    }

    public function editPoints(Competition $competition)
    {
        $userRole = auth()->user()?->role;
        if (! ($userRole === 'admin' || $userRole === 'organizator')) {
            abort(403, 'Brak dostępu');
        }
        $studentIds = $competition->registrations()->pluck('student_id');
        $students = Student::whereIn('id', $studentIds)->get();
        $stages = $competition->stages()->get();
        $points = [];
    StageCompetition::where('competition_id', $competition->id)
        ->get()
        ->each(function ($rec) use (&$points) {
            $points[$rec->student_id][$rec->stage_id] = $rec->result;
        });

        return view(
            'competitions.points.edit',
            compact('competition', 'students', 'stages', 'points')
        );
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

}
