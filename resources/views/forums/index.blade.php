<x-app-layout>
  <div class="max-w-7xl mx-auto py-6 px-4 space-y-12">
    <h1 class="text-3xl font-bold">Posty na forum konkursów</h1>

    <!-- FORMULARZ FILTRÓW (stosowany dla obu sekcji) -->
    <form method="GET" action="{{ route('forums.index') }}"
          class="mb-6 flex flex-wrap gap-2 items-center">
      <input type="text" name="search" value="{{ request('search') }}"
             placeholder="Szukaj w tytułach..."
             class="px-4 py-2 border rounded-md flex-grow" />
      <select name="sort" class="border rounded-md">
        <option value="newest" {{ request('sort') !== 'oldest' ? 'selected' : '' }}>
          Najnowsze
        </option>
        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>
          Najstarsze
        </option>
      </select>
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">
        Filtruj
      </button>
    </form>

    <!-- SEKCJA 1: MOJE KONKURSY -->
    <section>
      <h2 class="text-2xl font-semibold mb-4">Posty z moich konkursów</h2>
      @if($ownerForums->count())
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left font-medium ">Nazwa Konkursu</th>
                <th class="px-4 py-2 text-left font-medium">Opis</th>
                <th class="px-4 py-2 text-left font-medium">Dodano</th>
                <th class="px-4 py-2"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach($ownerForums as $forum)
                <tr>
                  <td class="px-4 py-2 max-w-[18rem] truncate" title="{{ $forum->competition->name }}">{{ $forum->competition->name }}</td>
                  <td class="px-4 py-2 max-w-[18rem] truncate" title="{{ $forum->competition->description }}">{{ $forum->competition->description }}</td>
                  <td class="px-4 py-2">{{ $forum->added_date }}</td>
                  <td class="px-4 py-2 text-right">
                    <a href="{{ route('forums.show', $forum) }}"
                       class="text-blue-600 hover:underline">Zobacz</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="mt-4">{{ $ownerForums->links() }}</div>
      @else
        <p class="text-gray-500">Nie masz jeszcze postów w swoich konkursach.</p>
      @endif
    </section>

    <!-- SEKCJA 2: UDZIAŁ W KONKURSACH -->
    <section>
      <h2 class="text-2xl font-semibold mb-4">Posty konkursów, do których dodałem uczniów</h2>
      @if($participantForums->count())
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left font-medium">Temat posta</th>
                <th class="px-4 py-2 text-left font-medium">Konkurs</th>
                <th class="px-4 py-2 text-left font-medium">Dodano</th>
                <th class="px-4 py-2"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach($participantForums as $forum)
                <tr>
                  <td class="px-4 py-2 max-w-[18rem] truncate" title="{{ $forum->topic }}">{{ $forum->topic }}</td>
                  <td class="px-4 py-2 max-w-[18rem] truncate" title="{{ $forum->competition->name }}">{{ $forum->competition->name }}</td>
                  <td class="px-4 py-2">{{ $forum->added_date }}</td>
                  <td class="px-4 py-2 text-right">
                    <a href="{{ route('forums.show', $forum) }}"
                       class="text-blue-600 hover:underline">Zobacz</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="mt-4">{{ $participantForums->links('pagination::tailwind', ['pageName'=>'part_page']) }}</div>
      @else
        <p class="text-gray-500">Nie ma postów w konkursach, do których dodałeś uczniów.</p>
      @endif
    </section>

  </div>
</x-app-layout>
