<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Rejestracja uczniów na: {{ $competition->name }}
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow rounded">

      <form method="POST" action="{{ route('competitions.registerStudents', $competition) }}">
        @csrf

        {{-- ▶︎ Dane szkoły / kontakt ◀︎ --}}
        <div class="mb-2">
          <label class="block text-sm font-medium">Szkoła</label>
          <input type="text" name="school" class="form-input w-full" required>
        </div>

        <div class="mb-2">
          <label class="block text-sm font-medium">Adres szkoły</label>
          <input type="text" name="school_address" class="form-input w-full" required>
        </div>

        <div class="mb-2">
          <label class="block text-sm font-medium">Nauczyciel</label>
          <input type="text" name="teacher" class="form-input w-full">
        </div>

        <div class="mb-2">
          <label class="block text-sm font-medium">Opiekun</label>
          <input type="text" name="guardian" class="form-input w-full">
        </div>

        <div class="mb-2">
          <label class="block text-sm font-medium">Kontakt (e-mail lub tel.)</label>
          <input type="text" name="contact" class="form-input w-full" required>
        </div>

        {{-- ▶︎ Uczniowie ◀︎ --}}
        <div id="students-wrapper">
          <div class="student-item mb-4 border p-4 rounded">

            {{-- Imię --}}
            <div class="mb-2">
              <label class="block text-sm font-medium">Imię</label>
              <input type="text" name="students[0][name]" class="form-input w-full" required>
            </div>

            {{-- Nazwisko --}}
            <div class="mb-2">
              <label class="block text-sm font-medium">Nazwisko</label>
              <input type="text" name="students[0][last_name]" class="form-input w-full" required>
            </div>

            {{-- Klasa – SELECT --}}
            <div class="mb-2">
              <label class="block text-sm font-medium">Klasa</label>
              <select name="students[0][class]" class="form-select w-full" required>
                <option value="" disabled selected>– wybierz –</option>
                @foreach($classes as $c)
                  <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
              </select>
            </div>

            {{-- Zgoda RODO --}}
            <div class="mb-2 flex items-center space-x-2">
              <input type="checkbox"
                     name="students[0][statement]"
                     value="1"
                     class="form-checkbox"
                     required>
              <span class="text-sm">Wyrażam zgodę na przetwarzanie danych osobowych</span>
            </div>

          </div>
        </div>

        <button type="button" id="add-student"
                class="bg-blue-500 text-white px-3 py-1 rounded mb-4">
          Dodaj kolejnego ucznia
        </button>

        <button type="submit"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          Zapisz uczniów
        </button>
      </form>

      <a href="{{ route('competitions.show', $competition) }}"
         class="text-blue-500 hover:underline mt-4 block">
        ← Wróć do szczegółów konkursu
      </a>
    </div>
  </div>

  {{-- ▶︎ Klonowanie kolejnych uczniów ◀︎ --}}
  <script>
    let idx = 1;
    document.getElementById('add-student').addEventListener('click', () => {
      const wrapper   = document.getElementById('students-wrapper');
      const prototype = document.querySelector('.student-item');
      const item      = prototype.cloneNode(true);

      item.querySelectorAll('input, select').forEach(el => {
        if (el.name && el.name.startsWith('students[')) {
          el.name = el.name.replace(/\d+/, idx);
        }
        if (el.tagName === 'SELECT') {
          el.selectedIndex = 0;  // reset <select>
        } else if (el.type === 'checkbox') {
          el.checked = false;   // reset checkbox
        } else {
          el.value = '';        // reset input text
        }
      });

      wrapper.appendChild(item);
      idx++;
    });
  </script>
</x-app-layout>