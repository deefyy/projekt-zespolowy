<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Competition;
use App\Models\Stage;
use App\Models\Student;
use App\Models\CompetitionRegistration;
use App\Models\Forum;
use App\Models\StageCompetition;
use App\Notifications\CompetitionCreatedNotification;

use App\Exports\CompetitionsExport;
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
            if (auth()->user()->role === 'admin') {
                $userRegistrations = $competition->registrations()->with('student')->get();
            } else {
                $userRegistrations = $competition->registrations()
                    ->where('user_id', auth()->id())
                    ->with('student')
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
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Brak dostępu');
        }

        return view('competitions.edit', compact('competition'));
    }

    public function update(Request $request, Competition $competition)
{
    // Sprawdzenie uprawnień
    if (auth()->user()->role !== 'admin') {
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
    $currentStages = $competition->stages()->count();
    $newStagesCount = $validated['stages_count'];

    // 🔄 **Aktualizacja ilości etapów**
    if ($newStagesCount > $currentStages) {
        // Dodawanie brakujących etapów
        for ($i = $currentStages + 1; $i <= $newStagesCount; $i++) {
            \App\Models\Stage::create([
                'stage'            => $i,
                'date'            => now()->addWeeks($i),
                'competition_id'  => $competition->id,
            ]);
        }
    } elseif ($newStagesCount < $currentStages) {
        // Usuwanie nadmiarowych etapów
        $competition->stages()
            ->orderByDesc('id')
            ->take($currentStages - $newStagesCount)
            ->delete();
    }

    // Odświeżenie widoku z odpowiednim komunikatem
    return redirect()->route('competitions.show', $competition)
        ->with('success', 'Konkurs został zaktualizowany.');
}


    public function destroy(Competition $competition)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Brak dostępu');
        }

        $competition->delete();

        return redirect()->route('competitions.index')->with('success', 'Konkurs został usunięty.');
    }

    public function showRegistrationForm(Competition $competition)
    {
        return view('competitions.register', compact('competition'));
    }

    public function registerStudents(Request $request, Competition $competition)
    {
        $data = $request->validate([
            'school'               => 'required|string',
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
        }

        foreach ($competition->stages as $stage) {
            StageCompetition::create([
                'competition_id' => $competition->id,
                'stage_id'       => $stage->id,
                'student_id'     => $student->id,
                'result'         => null,   // lub '' albo 'pending'
            ]);
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
        $isAdmin = auth()->user()->role === 'admin';
        $isOwner = CompetitionRegistration::where('student_id', $student->id)
            ->where('user_id', auth()->id())
            ->exists();

        if (!$isAdmin && !$isOwner) {
            abort(403, 'Brak dostępu');
        }

        return view('competitions.students.edit', compact('student'));
    }

    public function updateStudent(Request $request, Student $student)
    {
        $isAdmin = auth()->user()->role === 'admin';
        $isOwner = CompetitionRegistration::where('student_id', $student->id)
            ->where('user_id', auth()->id())
            ->exists();

        if (!$isAdmin && !$isOwner) {
            abort(403, 'Brak dostępu');
        }

        $data = $request->validate([
            'name'      => 'required|string',
            'last_name' => 'required|string',
            'class'     => 'required|string',
            'school'    => 'required|string',
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

        if (!$isAdmin && !$isOwner) {
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
}
