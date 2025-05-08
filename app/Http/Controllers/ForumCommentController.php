<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\ForumComment;
use App\Models\CompetitionRegistration;
use App\Notifications\ForumCommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class ForumCommentController extends Controller
{

    /**
     * Zapisanie nowego komentarza.
     */
    public function store(Request $request, Forum $forum)
    {
        // Sprawdzenie uprawnień: czy zalogowany użytkownik jest autorem konkursu powiązanego z tym postem forum
        if (Auth::id() !== $forum->competition->user_id) {
            abort(403, 'Brak uprawnień do dodawania komentarzy w tym poście.');
        }

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
        // Sprawdzenie spójności: czy komentarz należy do tego posta (ochrona przed manipulacją URL)
        if ($comment->forum_id !== $forum->id) {
            abort(404);
        }
        // Sprawdzenie uprawnień: tylko autor komentarza (konkursu) może edytować
        if (Auth::id() !== $comment->user_id) {
            abort(403, 'Brak uprawnień do edycji tego komentarza.');
        }

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
