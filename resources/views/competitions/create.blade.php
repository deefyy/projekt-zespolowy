<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dodaj nowy konkurs
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 bg-white shadow rounded p-6">

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
            <form method="POST"
                  action="{{ route('competitions.store') }}"
                  enctype="multipart/form-data">
                @csrf

                {{-- Nazwa konkursu --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">
                        Nazwa konkursu
                    </label>
                    <input type="text"
                        name="name"
                        id="name"
                        maxlength="255"
                        oninput="updateNameCount()"
                        value="{{ old('name') }}"
                        class="form-input rounded-md shadow-sm mt-1 block w-full"
                        required>
                    <p class="text-xs text-gray-500 mt-1">
                        <span id="name_count">0</span>/255 znaków
                    </p>
                </div>

                {{-- Opis --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Opis</label>
                    <textarea name="description"
                              id="description"
                              class="form-input rounded-md shadow-sm mt-1 block w-full"
                              maxlength="255"
                              oninput="updateDescCount()"
                              required>{{ old('description') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1"><span id="desc_count">0</span>/255 znaków</p>
                </div>

                {{-- Plakat (JPG/PNG/WEBP) --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">
                        Plakat (max 2&nbsp;MB)
                    </label>
                    <input type="file"
                           name="poster"
                           accept="image/*"
                           class="form-input rounded-md shadow-sm mt-1 block w-full">
                </div>

                {{-- Ilość etapów --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Ilość etapów</label>
                    <input type="number"
                           name="stages_count"
                           min="1"
                           value="{{ old('stages_count', 1) }}"
                           class="form-input rounded-md shadow-sm mt-1 block w-full"
                           required>
                </div>

                @php($today = now()->toDateString())

                {{-- Data rozpoczęcia --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Data rozpoczęcia</label>
                    <input type="date"
                           name="start_date"
                           value="{{ old('start_date') }}"
                           min="{{ $today }}"
                           class="form-input rounded-md shadow-sm mt-1 block w-full"
                           required>
                </div>

                {{-- Data zakończenia --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Data zakończenia</label>
                    <input type="date"
                           name="end_date"
                           value="{{ old('end_date') }}"
                           min="{{ $today }}"
                           class="form-input rounded-md shadow-sm mt-1 block w-full"
                           required>
                </div>

                {{-- Koniec zapisów --}}
                <div class="mb-6">
                    <label class="block font-medium text-sm text-gray-700">Koniec zapisów</label>
                    <input type="date"
                           name="registration_deadline"
                           value="{{ old('registration_deadline') }}"
                           min="{{ $today }}"
                           class="form-input rounded-md shadow-sm mt-1 block w-full"
                           required>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        Zapisz konkurs
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ▶︎ JS licznik znaków ◀︎ --}}
    <script>
      function updateDescCount () {
        const textarea = document.getElementById('description');
        const counter  = document.getElementById('desc_count');
        counter.textContent = textarea.value.length;
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