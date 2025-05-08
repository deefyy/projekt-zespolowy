{{-- resources/views/forums/show.blade.php --}}
<x-app-layout>
    <div class="max-w-4xl mx-auto py-6 px-4">
        <!-- Sekcja posta forum -->
        <div class="mb-6">
            <a href="{{ route('forums.index') }}" class="text-blue-600 hover:underline">&larr; Wróć do listy postów</a>
            <h1 class="text-2xl font-bold mt-2">{{ $forum->title }}</h1>
            <!-- Możemy wyświetlić nazwę konkursu i datę dodania posta -->
            <p class="text-sm text-gray-600">Konkurs: {{ $forum->competition->name }} | Dodano: {{ $forum->created_at->format('Y-m-d H:i') }}</p>
            @if($forum->content ?? false)
                <div class="mt-4 prose">{{ $forum->content }}</div>
            @endif
        </div>

        <!-- Sekcja komentarzy -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="text-xl font-semibold mb-4">Komentarze</h2>

            @foreach($forum->comments->sortBy('created_at') as $comment)
                <div class="mb-4 p-4 bg-white rounded-md shadow">
                    <p class="text-gray-800">{{ $comment->content }}</p>
                    <p class="text-sm text-gray-500">Dodano: {{ $comment->created_at->format('Y-m-d H:i') }}</p>
                    @if(Auth::id() === $comment->user_id)
                        <!-- Przyciski edycji (dla autora komentarza) -->
                        <div class="mt-2">
                            <a href="{{ route('forums.show', [$forum, 'edit_comment' => $comment->id]) }}" 
                               class="text-blue-600 hover:underline mr-4">Edytuj</a>
                        </div>
                        <!-- Formularz edycji komentarza (pokazywany, jeśli w URL jest ?edit_comment=id tego komentarza) -->
                        @if(request('edit_comment') == $comment->id)
                            <form method="POST" action="{{ route('forums.comments.update', [$forum, $comment]) }}" class="mt-2">
                                @csrf
                                @method('PUT')
                                <textarea name="content" rows="3" class="w-full p-2 border rounded-md">{{ old('content', $comment->content) }}</textarea>
                                <div class="mt-2 space-x-2">
                                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md">Zapisz</button>
                                    <a href="{{ route('forums.show', $forum) }}" class="px-4 py-2 bg-gray-300 text-black rounded-md">Anuluj</a>
                                </div>
                            </form>
                        @endif
                    @endif
                </div>
            @endforeach

            <!-- Formularz dodawania nowego komentarza (tylko dla autora konkursu) -->
            @if(Auth::id() === $forum->competition->user_id)
                <div class="mt-6">
                    <h3 class="text-lg font-medium mb-2">Dodaj nowy komentarz</h3>
                    <form method="POST" action="{{ route('forums.comments.store', $forum) }}">
                        @csrf
                        <textarea name="content" rows="4" class="w-full p-2 border rounded-md" placeholder="Treść komentarza...">{{ old('content') }}</textarea>
                        <div class="mt-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Opublikuj komentarz</button>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
