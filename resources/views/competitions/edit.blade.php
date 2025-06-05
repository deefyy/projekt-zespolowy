<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Edytuj konkurs
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow rounded">

      {{-- komunikaty walidacji --}}
      @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 rounded text-sm text-red-800">
          <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- ⬇︎  DODAJ enctype="multipart/form-data" --}}
      <form action="{{ route('competitions.update', $competition) }}"
            method="POST"
            enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Nazwa --}}
        <div class="mb-4">
          <label for="name" class="block text-sm font-medium text-gray-700">
            Nazwa
          </label>
          <input type="text"
                 name="name"
                 id="name"
                 maxlength="255"
                 oninput="updateNameCount()"
                 value="{{ old('name', $competition->name) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                 required>
          <p class="text-xs text-gray-500 mt-1"><span id="name_count">0</span>/255 znaków</p>
        </div>

        {{-- Opis --}}
        <div class="mb-4">
          <label for="description" class="block text-sm font-medium text-gray-700">
            Opis
          </label>
          <textarea name="description"
                    id="description"
                    rows="4"
                    maxlength="255"
                    oninput="updateDescCount()"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                    required>{{ old('description', $competition->description) }}</textarea>
          <p class="text-xs text-gray-500 mt-1"><span id="desc_count">0</span>/255 znaków</p>
        </div>

        {{-- ⬇︎  AKTUALNY PLAKAT (jeśli istnieje) --}}
        @if($competition->poster_path)
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Aktualny plakat</label>
            <img src="{{ Storage::url($competition->poster_path) }}"
                 alt="Plakat konkursu {{ $competition->name }}"
                 class="w-full max-h-60 object-contain rounded-lg shadow mt-2">
          </div>
        @endif

        {{-- ⬇︎  NOWY PLAKAT --}}
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">
            Zmień plakat (opcjonalnie)
          </label>
          <input type="file"
                 name="poster"
                 accept="image/*"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
          <p class="text-xs text-gray-500 mt-1">JPG/PNG/WEBP, maks. 2&nbsp;MB. Pozostaw puste, aby zachować obecny plik.</p>
        </div>

        {{-- Ilość etapów --}}
        <div class="mb-4">
          <label for="stages_count" class="block text-sm font-medium text-gray-700">Ilość etapów</label>
          <input type="number"
                 name="stages_count"
                 id="stages_count"
                 min="1"
                 value="{{ old('stages_count', $competition->stages_count) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                 required>
        </div>

        {{-- Data rozpoczęcia --}}
        <div class="mb-4">
          <label for="start_date" class="block text-sm font-medium text-gray-700">Data rozpoczęcia</label>
          <input type="date"
                 name="start_date"
                 id="start_date"
                 value="{{ old('start_date', $competition->start_date) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                 required>
        </div>

        {{-- Data zakończenia --}}
        <div class="mb-4">
          <label for="end_date" class="block text-sm font-medium text-gray-700">Data zakończenia</label>
          <input type="date"
                 name="end_date"
                 id="end_date"
                 value="{{ old('end_date', $competition->end_date) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                 required>
        </div>

        {{-- Termin zapisów --}}
        <div class="mb-6">
          <label for="registration_deadline" class="block text-sm font-medium text-gray-700">Termin zapisów</label>
          <input type="date"
                 name="registration_deadline"
                 id="registration_deadline"
                 value="{{ old('registration_deadline', $competition->registration_deadline) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                 required>
        </div>

        <div class="flex justify-end">
          <button type="submit"
                  class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Zapisz zmiany
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ▶︎ JS liczniki znaków ◀︎ --}}
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