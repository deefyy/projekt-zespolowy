<x-app-layout>
   <x-slot name="header">
        <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
            <div class="px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[#002d62] text-center">Dodaj nowy konkurs</h2>
            </div>
        </header>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-md rounded-xl p-8">

                {{-- komunikaty walidacji --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-100 rounded text-sm text-red-800 border border-red-300">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('competitions.store') }}"
                      enctype="multipart/form-data"
                      class="space-y-6">
                    @csrf

                    {{-- Nazwa --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nazwa konkursu</label>
                        <input type="text"
                               name="name"
                               id="name"
                               maxlength="255"
                               value="{{ old('name') }}"
                               oninput="updateNameCount()"
                               class="mt-1 w-full border border-gray-300 rounded px-4 py-2 shadow-sm focus:ring-[#002d62] focus:border-[#002d62]"
                               required>
                        <p class="text-xs text-gray-500 mt-1"><span id="name_count">0</span>/255 znaków</p>
                    </div>

                    {{-- Opis --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Opis</label>
                        <textarea name="description"
                                  id="description"
                                  maxlength="255"
                                  rows="4"
                                  oninput="updateDescCount()"
                                  class="mt-1 w-full border border-gray-300 rounded px-4 py-2 shadow-sm focus:ring-[#002d62] focus:border-[#002d62]"
                                  required>{{ old('description') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1"><span id="desc_count">0</span>/255 znaków</p>
                    </div>

                    {{-- Plakat --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Plakat (max 2&nbsp;MB)</label>
                        <input type="file"
                               name="poster"
                               accept="image/*"
                               class="mt-1 w-full border border-gray-300 rounded px-4 py-2 shadow-sm">
                    </div>

                    {{-- Ilość etapów --}}
                    <div>
                        <label for="stages_count" class="block text-sm font-medium text-gray-700">Ilość etapów</label>
                        <input type="number"
                               name="stages_count"
                               id="stages_count"
                               min="1"
                               value="{{ old('stages_count', 1) }}"
                               class="mt-1 w-full border border-gray-300 rounded px-4 py-2 shadow-sm"
                               required>
                    </div>

                    {{-- Daty --}}
                    @php($today = now()->toDateString())

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data rozpoczęcia</label>
                            <input type="date"
                                   name="start_date"
                                   min="{{ $today }}"
                                   value="{{ old('start_date') }}"
                                   class="mt-1 w-full border border-gray-300 rounded px-4 py-2 shadow-sm"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data zakończenia</label>
                            <input type="date"
                                   name="end_date"
                                   min="{{ $today }}"
                                   value="{{ old('end_date') }}"
                                   class="mt-1 w-full border border-gray-300 rounded px-4 py-2 shadow-sm"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Koniec zapisów</label>
                            <input type="date"
                                   name="registration_deadline"
                                   min="{{ $today }}"
                                   value="{{ old('registration_deadline') }}"
                                   class="mt-1 w-full border border-gray-300 rounded px-4 py-2 shadow-sm"
                                   required>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                                class="bg-[#002d62] hover:bg-[#001b3c] text-white font-semibold px-6 py-2 rounded-lg transition">
                            ➕ Zapisz konkurs
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Skrypt liczników --}}
    <script>
        function updateNameCount() {
            const input = document.getElementById('name');
            document.getElementById('name_count').textContent = input.value.length;
        }
        function updateDescCount() {
            const textarea = document.getElementById('description');
            document.getElementById('desc_count').textContent = textarea.value.length;
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateNameCount();
            updateDescCount();
        });
    </script>
</x-app-layout>
