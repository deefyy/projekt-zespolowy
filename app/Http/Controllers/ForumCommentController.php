<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\ForumComment;
use App\Models\CompetitionRegistration;
use App\Notifications\ForumCommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Models\User;  

class ForumCommentController extends Controller
{

    /**
     * Zapisanie nowego komentarza.
     */
    public function store(Request $request, Forum $forum)
    {
        $user = Auth::user();

        // Uprawnienia
        $isAdmin        = $user->role === 'admin';
        $isOwner        = $user->id === $forum->competition->user_id;
        $isCoOrganizer  = $forum->competition
                            ->coOrganizers()
                            ->where('user_id', $user->id)
                            ->exists();

        abort_unless($isAdmin || $isOwner || $isCoOrganizer, 403,
            'Brak uprawnień do dodawania komentarzy w tym poście.');

        // Walidacja danych formularza
        $validatedData = $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        // Utworzenie nowego komentarza przypisanego do posta forum i użytkownika
        $comment = new ForumComment();
        $comment->forum_id = $forum->id;
        $comment->user_id = Auth::id();
        $comment->content = $validatedData['content'];
        $comment->save();

        // Wysłanie powiadomień e-mail do wszystkich użytkowników, którzy dodali uczniów do konkursu
        $registrations = CompetitionRegistration::where('competition_id', $forum->competition_id)->get();
        $usersToNotify = [];
        foreach ($registrations as $registration) {
            // Dodaj użytkownika rejestrującego (np. nauczyciela) do listy powiadomień
            if ($registration->user_id !== Auth::id()) {  // pomiń autora (opcjonalnie, jeśli nie chcemy powiadamiać samego siebie)
                $usersToNotify[$registration->user_id] = $registration->user; 
            }
        }
        // pobierz użytkowników do powiadomienia
        $usersToNotify = CompetitionRegistration::where('competition_id', $forum->competition_id)
            ->with('user')
            ->get()
            ->pluck('user')     // kolekcja User
            ->filter()          // usuń null, gdyby się trafił
            ->unique('id');     // unikalni po ID

        // wyślij każdemu osobno
        foreach ($usersToNotify as $user) {
            $user->notify(new ForumCommentNotification($comment));
        }
        // Przekierowanie z komunikatem
        return redirect()->route('forums.show', $forum)
                         ->with('success', 'Komentarz został dodany.');
    }

    /**
     * Aktualizacja istniejącego komentarza.
     */
    public function update(Request $request, Forum $forum, ForumComment $comment)
    {
        // Czy komentarz należy do danego posta?
        abort_if($comment->forum_id !== $forum->id, 404);

        $user = Auth::user();
        $isAdmin        = $user->role === 'admin';
        $isOwner        = $user->id === $forum->competition->user_id;
        $isCoOrganizer  = $forum->competition
                            ->coOrganizers()
                            ->where('user_id', $user->id)
                            ->exists();

        // Uprawniony jest: autor komentarza LUB admin LUB owner LUB co-organizer
        abort_unless(
            $user->id === $comment->user_id || $isAdmin || $isOwner || $isCoOrganizer,
            403,
            'Brak uprawnień do edycji tego komentarza.'
        );

        // Walidacja nowej treści
        $validatedData = $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        // Aktualizacja komentarza
        $comment->content = $validatedData['content'];
        $comment->save();

        return redirect()->route('forums.show', $forum)
                         ->with('success', 'Komentarz został zmieniony.');
    }
}
