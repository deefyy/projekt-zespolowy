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
        $userRegistrations = $competition->registrations()
        ->where('user_id', auth()->id())
        ->with('student')
        ->get();

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
            'student_id' => 'exists:students,id',
        ]);

        Competition::create($validated);

        return redirect()->route('competitions.index')->with('success', 'Konkurs został dodany!');
    }

    public function showRegistrationForm(Competition $competition)
    {
        return view('competitions.register', compact('competition'));
    }
    public function registerStudents(Request $request, Competition $competition)
    {
        $data = $request->validate([
            'students'              => 'required|array|min:1',
            'students.*.name'       => 'required|string',
            'students.*.last_name'  => 'required|string',
            'students.*.class'      => 'required|string',
            'students.*.school'     => 'required|string',
        ]);
    
        foreach ($data['students'] as $stu) {
            $student = Student::create([
                'name'      => $stu['name'],
                'last_name' => $stu['last_name'],
                'class'     => $stu['class'],
                'school'    => $stu['school'],
            ]);
    
            CompetitionRegistration::create([
                'competition_id' => $competition->id,
                'user_id'        => auth()->id(),
                'student_id'     => $student->id,
            ]);
        }
    
        return redirect()
        ->route('competitions.show', $competition)
        ->with('success', 'Uczniowie zostali zapisani na konkurs!');
    }
    
}
