<x-app-layout>
  <x-slot name="header">
    <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-[#002d62] text-center">{{ __('Edit competition') }}</h2>
      </div>
    </header>
  </x-slot>

  <div class="py-10 bg-[#f9fbfd]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 bg-white p-6 rounded-xl shadow">

      @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
          <ul class="list-disc pl-5 space-y-1 text-sm">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('competitions.update', $competition) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
          <input type="text" name="name" id="name" maxlength="255" oninput="updateNameCount()" value="{{ old('name', $competition->name) }}" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>
          <p class="text-xs text-gray-500 mt-1"><span id="name_count">0</span>/255 {{ __('characters') }}</p>
        </div>

        <div>
          <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
          <textarea name="description" id="description" rows="4" maxlength="255" oninput="updateDescCount()" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>{{ old('description', $competition->description) }}</textarea>
          <p class="text-xs text-gray-500 mt-1"><span id="desc_count">0</span>/255 {{ __('characters') }}</p>
        </div>

        @if($competition->poster_path)
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Current poster') }}</label>
            <img src="{{ Storage::url($competition->poster_path) }}" alt="{{ __('Poster for') }} {{ $competition->name }}" class="w-full max-h-60 object-contain rounded-xl shadow mt-2">
          </div>
        @endif

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Change poster (optional)') }}</label>
          <input type="file" name="poster" accept="image/*" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
          <p class="text-xs text-gray-500 mt-1">{{ __('JPG/PNG/WEBP, max 2MB. Leave empty to keep the current file.') }}</p>
        </div>

        <div>
          <label for="stages_count" class="block text-sm font-medium text-gray-700">{{ __('Number of stages') }}</label>
          <input type="number" name="stages_count" id="stages_count" min="1" value="{{ old('stages_count', $competition->stages_count) }}" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>
        </div>

        <div>
          <label for="start_date" class="block text-sm font-medium text-gray-700">{{ __('Start date') }}</label>
          <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $competition->start_date) }}" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>
        </div>

        <div>
          <label for="end_date" class="block text-sm font-medium text-gray-700">{{ __('End date') }}</label>
          <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $competition->end_date) }}" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>
        </div>

        <div>
          <label for="registration_deadline" class="block text-sm font-medium text-gray-700">{{ __('Registration deadline') }}</label>
          <input type="date" name="registration_deadline" id="registration_deadline" value="{{ old('registration_deadline', $competition->registration_deadline) }}" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>
        </div>

        <div class="flex justify-end">
          <button type="submit" class="bg-[#002d62] text-white px-6 py-2 rounded hover:bg-[#001b3c]">
            {{ __('Save changes') }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function updateDescCount () {
      const textarea = document.getElementById('description');
      document.getElementById('desc_count').textContent = textarea.value.length;
    }

    function updateNameCount () {
      const input = document.getElementById('name');
      document.getElementById('name_count').textContent = input.value.length;
    }

    document.addEventListener('DOMContentLoaded', () => {
      updateDescCount();
      updateNameCount();
    });
  </script>
</x-app-layout>
