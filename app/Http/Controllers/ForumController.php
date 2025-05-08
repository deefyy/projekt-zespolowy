<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForumController extends Controller
{
    /**
     * Lista postów forum należących do konkursów,
     * których autorem (user_id) jest aktualny użytkownik.
     * Obsługuje wyszukiwanie i sortowanie.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $search = $request->input('search');
        $sortDir = $request->input('sort') === 'oldest' ? 'asc' : 'desc';
    
        // 1. Posty z MOICH konkursów (gdzie jestem właścicielem)
        $ownerQuery = Forum::with('competition')
            ->whereHas('competition', fn($q) => $q->where('user_id', $userId));
    
        if ($search) {
            $ownerQuery->where('topic', 'like', "%{$search}%");
        }
        $ownerForums = $ownerQuery
            ->orderBy('added_date', $sortDir)
            ->paginate(5, ['*'], 'owner_page')
            ->withQueryString();
    
        // 2. Posty konkursów, do których zarejestrowałem uczniów
        $compIds = CompetitionRegistration::where('user_id', $userId)
            ->pluck('competition_id')
            ->unique()
            ->toArray();
    
        $partQuery = Forum::with('competition')
            ->whereIn('competition_id', $compIds);
    
        if ($search) {
            $partQuery->where('topic', 'like', "%{$search}%");
        }
        $participantForums = $partQuery
            ->orderBy('added_date', $sortDir)
            ->paginate(5, ['*'], 'part_page')
            ->withQueryString();
    
        return view('forums.index', compact('ownerForums', 'participantForums'));
    }

    /**
     * Widok pojedynczego posta + komentarze.
     * Dostęp:
     *   – autor konkursu (competition.user_id),
     *   – admin,
     *   – lub użytkownik, który dodał ucznia do konkursu.
     */
    public function show(Forum $forum)
    {
        $user   = Auth::user();
        $isAdmin = $user->role === 'admin';
        $isOwner = $forum->competition->user_id === $user->id;
        $isParticipant = CompetitionRegistration::where('competition_id', $forum->competition_id)
                          ->where('user_id', $user->id)
                          ->exists();

        if (! $isAdmin && ! $isOwner && ! $isParticipant) {
            abort(403, 'Brak dostępu do tego posta forum.');
        }

        // Załaduj komentarze + autora komentarzy, posortowane rosnąco
        $forum->load([
            'comments.user',           // komentarze i ich autorzy
            'competition'              // konkurs – potrzebny w widoku
        ]);

        return view('forums.show', compact('forum'));
    }
}
