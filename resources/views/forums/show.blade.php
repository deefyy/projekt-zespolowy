<x-app-layout>
  <x-slot name="header">
    <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-[#002d62] text-center">Post na forum</h1>
      </div>
    </header>
  </x-slot>

  <div class="py-10 bg-[#f9fbfd] min-h-screen">
    <div class="max-w-4xl mx-auto px-4 space-y-10">

      {{-- Powrót --}}
      <a href="{{ route('forums.index') }}" class="text-blue-600 hover:underline">&larr; Wróć do listy postów</a>

      {{-- Post --}}
      <section class="bg-white shadow p-6 rounded-xl border border-[#cdd7e4] space-y-2">
        <h2 class="text-2xl font-bold text-[#002d62]">{{ $forum->title }}</h2>
        <p class="text-sm text-gray-600">Konkurs: {{ $forum->competition->name }} | Dodano: {{ $forum->created_at->format('Y-m-d H:i') }}</p>
        @if($forum->content)
          <div class="mt-4 text-gray-800 break-words whitespace-pre-line">
            {{ $forum->content }}
          </div>
        @endif
      </section>

      {{-- Komentarze --}}
      <section class="bg-white shadow p-6 rounded-xl border border-[#cdd7e4]">
        <h3 class="text-xl font-bold text-[#002d62] mb-4">Komentarze</h3>

        @foreach($forum->comments->sortBy('created_at') as $comment)
          <div class="mb-4 p-4 bg-[#f9fbfd] border border-[#dce5f0] rounded-xl">
            <p class="text-gray-800 break-words whitespace-pre-line">{{ $comment->content }}</p>
            <p class="text-sm text-gray-500 mt-1">Dodano: {{ $comment->created_at->format('Y-m-d H:i') }}</p>

            @php
              $user = Auth::user();
              $isOwn = $user?->id === $comment->user_id;
              $isAdmin = $user?->role === 'admin';
            @endphp

            @if($isOwn || $isAdmin)
              <div class="mt-2">
                <a href="{{ route('forums.show', [$forum, 'edit_comment' => $comment->id]) }}"
                   class="text-blue-600 hover:underline mr-4">Edytuj</a>
              </div>

              @if(request('edit_comment') == $comment->id)
                <form method="POST" action="{{ route('forums.comments.update', [$forum, $comment]) }}" class="mt-2 space-y-2">
                  @csrf
                  @method('PUT')
                  <textarea name="content" rows="3" class="w-full p-3 border border-gray-300 rounded-md">{{ old('content', $comment->content) }}</textarea>
                  <div class="flex gap-2">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Zapisz</button>
                    <a href="{{ route('forums.show', $forum) }}" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">Anuluj</a>
                  </div>
                </form>
              @endif
            @endif
          </div>
        @endforeach

        {{-- Nowy komentarz --}}
        @php
          $userId        = Auth::id();
          $user          = Auth::user();
          $isAdmin       = $user?->role === 'admin';
          $isOwner       = $userId === $forum->competition->user_id;
          $isCoOrganizer = $forum->competition->coOrganizers()->where('user_id', $userId)->exists();
        @endphp

        @if($isOwner || $isCoOrganizer || $isAdmin)
          <div class="mt-8 pt-4 border-t border-[#cdd7e4]">
            <h4 class="text-lg font-medium text-[#002d62] mb-2">Dodaj nowy komentarz</h4>
            <form method="POST" action="{{ route('forums.comments.store', $forum) }}" class="space-y-2">
              @csrf
              <textarea name="content" rows="4" class="w-full p-3 border border-gray-300 rounded-md" placeholder="Treść komentarza...">{{ old('content') }}</textarea>
              <button type="submit" class="bg-[#002d62] text-white px-5 py-2 rounded-xl hover:bg-[#001b3c]">Opublikuj komentarz</button>
            </form>
          </div>
        @endif
      </section>
    </div>
  </div>
</x-app-layout>
