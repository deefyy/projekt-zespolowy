<x-app-layout>
  <x-slot name="header">
    <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-[#002d62] text-center">{{ __('Competition Forum Posts') }}</h1>
      </div>
    </header>
  </x-slot>

  <div class="py-10 bg-[#f9fbfd] min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

      <form method="GET" action="{{ route('forums.index') }}" class="flex flex-wrap gap-4 items-end bg-white shadow p-6 rounded-xl border border-[#cdd7e4]">
        <div class="flex-1 min-w-[200px]">
          <label for="search" class="block text-sm font-semibold text-[#002d62] mb-1">{{ __('Search in titles') }}</label>
          <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Enter a title...') }}"
                 class="w-full border border-[#cdd7e4] rounded-xl px-4 py-2 shadow-sm">
        </div>

        <div>
          <label for="sort" class="block text-sm font-semibold text-[#002d62] mb-1">{{ __('Sort') }}</label>
          <select name="sort" class="border border-[#cdd7e4] rounded-xl px-4 py-2">
            <option value="newest" {{ request('sort') !== 'oldest' ? 'selected' : '' }}>{{ __('Newest') }}</option>
            <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>{{ __('Oldest') }}</option>
          </select>
        </div>

        <div>
          <button type="submit" class="bg-[#002d62] text-white px-5 py-2 rounded-xl hover:bg-[#001b3e] transition mt-6 sm:mt-0">
            🔍 {{ __('Filter') }}
          </button>
        </div>
      </form>

      <section class="bg-white shadow p-6 rounded-xl border border-[#cdd7e4]">
        <h2 class="text-xl font-bold text-[#002d62] mb-4">{{ __('Posts from my competitions') }}</h2>
        @if($ownerForums->count())
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
              <thead class="bg-[#f1f5fb] text-[#002d62]">
                <tr>
                  <th class="px-4 py-3">{{ __('Competition Name') }}</th>
                  <th class="px-4 py-3">{{ __('Description') }}</th>
                  <th class="px-4 py-3">{{ __('Added') }}</th>
                  <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                @foreach($ownerForums as $forum)
                  <tr class="hover:bg-[#f9fbfd]">
                      <td class="px-4 py-2 max-w-[18rem] truncate" title="{{ $forum->competition->name }}">{{ $forum->competition->name }}</td>
                    <td class="px-4 py-2 max-w-[18rem] truncate" title="{{ $forum->competition->description }}">{{ $forum->competition->description }}</td>
                  <td class="px-4 py-2">{{ $forum->added_date }}</td>
                    <td class="px-4 py-2 text-right">
                      <a href="{{ route('forums.show', $forum) }}" class="text-blue-600 hover:underline">{{ __('View') }}</a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="mt-4">{{ $ownerForums->links() }}</div>
        @else
          <p class="text-gray-500 italic">{{ __("You don't have any posts in your competitions yet.") }}</p>
        @endif
      </section>

      <section class="bg-white shadow p-6 rounded-xl border border-[#cdd7e4]">
        <h2 class="text-xl font-bold text-[#002d62] mb-4">{{ __("Posts from competitions you've added students to") }}</h2>
        @if($participantForums->count())
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
              <thead class="bg-[#f1f5fb] text-[#002d62]">
                <tr>
                  <th class="px-4 py-3">{{ __('Post Topic') }}</th>
                  <th class="px-4 py-3">{{ __('Competition') }}</th>
                  <th class="px-4 py-3">{{ __('Added') }}</th>
                  <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                @foreach($participantForums as $forum)
                  <tr class="hover:bg-[#f9fbfd]">
                    <td class="px-4 py-2 max-w-[18rem] truncate" title="{{ $forum->topic }}">{{ $forum->topic }}</td>
                    <td class="px-4 py-2 max-w-[18rem] truncate" title="{{ $forum->competition->name }}">{{ $forum->competition->name }}</td>
                    <td class="px-4 py-2">{{ $forum->added_date }}</td>
                    <td class="px-4 py-2 text-right">
                      <a href="{{ route('forums.show', $forum) }}" class="text-blue-600 hover:underline">{{ __('View') }}</a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="mt-4">{{ $participantForums->links('pagination::tailwind', ['pageName'=>'part_page']) }}</div>
        @else
          <p class="text-gray-500 italic">{{ __('There are no posts in the competitions you have added students to.') }}</p>
        @endif
      </section>
    </div>
  </div>
</x-app-layout>