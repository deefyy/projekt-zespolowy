<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Competition;
use App\Models\Student;
use App\Models\CompetitionRegistration;

class CompetitionController extends Controller
{
    public function index()
    {
        $competitions = Competition::all();
        return view('competitions.index', compact('competitions'));
    }

    public function show(Competition $competition)
{
    $userRegistrations = collect(); // domyślnie pusto

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
            'name' => 'required|string',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'registration_deadline' => 'required|date|before_or_equal:start_date',
        ]);

        Competition::create($validated);

        

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
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Brak dostępu');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'registration_deadline' => 'required|date|before_or_equal:end_date',
        ]);

        $competition->update($validated);

        return redirect()->route('competitions.show', $competition)->with('success', 'Konkurs został zaktualizowany.');
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
            'students'             => 'required|array|min:1',
            'students.*.name'      => 'required|string',
            'students.*.last_name' => 'required|string',
            'students.*.class'     => 'required|string',
        ]);
        
    
        foreach ($data['students'] as $stu) {
            $student = Student::create([
                'name'      => $stu['name'],
                'last_name' => $stu['last_name'],
                'class'     => $stu['class'],
                'school'    => $data['school'],
            ]);
        
            CompetitionRegistration::create([
                'competition_id' => $competition->id,
                'user_id'        => auth()->id(),
                'student_id'     => $student->id,
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

        if (! $isAdmin && ! $isOwner) {
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

        if (! $isAdmin && ! $isOwner) {
            abort(403, 'Brak dostępu');
        }

        $data = $request->validate([
            'name' => 'required|string',
            'last_name' => 'required|string',
            'class' => 'required|string',
            'school' => 'required|string',
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

        if (! $isAdmin && ! $isOwner) {
            abort(403, 'Brak dostępu');
        }

        $student->delete();

        return redirect()->back()->with('success', 'Uczeń został usunięty.');
    }

    
}
