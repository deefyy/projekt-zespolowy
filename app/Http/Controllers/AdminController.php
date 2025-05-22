<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminController extends Controller
{
    public function index(Request $request)
{
    $user = Auth::user();

    if (!$user || $user->role !== 'admin') {
        abort(403, 'Brak dostępu - tylko dla administratorów.');
    }

    $search = $request->input('search');
    $sort = $request->input('sort', 'name'); // domyślnie sortujemy po imieniu
    $direction = $request->input('direction', 'asc');

    // Lista dozwolonych kolumn do sortowania – dla bezpieczeństwa
    $allowedSorts = ['name', 'last_name', 'email', 'role'];
    if (!in_array($sort, $allowedSorts)) {
        $sort = 'name';
    }

    $users = User::query()
        ->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })
        ->orderBy($sort, $direction)
        ->paginate(10)
        ->withQueryString(); // zachowaj sort/search w URL przy paginacji

    return view('admin.dashboard', compact('users'));
}


    public function create()
    {
        return view('admin.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'last_name' => 'nullable',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:user,employee,admin',
        ]);

        User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Użytkownik został pomyślnie dodany!');

    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'last_name' => 'nullable',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|in:user,organizator,admin',
        ]);

        $user->update([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        return redirect()->route('admin.dashboard');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.dashboard');
    }
}
