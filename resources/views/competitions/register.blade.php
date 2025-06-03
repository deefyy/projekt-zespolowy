<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Rejestracja uczniów na: {{ $competition->name }}
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow rounded">

      <form id="students-form" method="POST" action="{{ route('competitions.registerStudents', $competition) }}">
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

        {{-- ▶︎ Nauczyciel / Opiekun (opcjonalnie) ◀︎ --}}
        <div class="mb-2 flex items-center space-x-3">
          <input type="checkbox" id="toggle_teacher" class="form-checkbox">
          <label for="toggle_teacher" class="text-sm">Dodaj nauczyciela</label>
        </div>
        <div class="mb-2 hidden" id="teacher_wrapper">
          <label class="block text-sm font-medium">Nauczyciel</label>
          <input type="text" name="teacher" id="teacher_input" class="form-input w-full" disabled>
        </div>

        <div class="mb-2 flex items-center space-x-3">
          <input type="checkbox" id="toggle_guardian" class="form-checkbox">
          <label for="toggle_guardian" class="text-sm">Dodaj opiekuna</label>
        </div>
        <div class="mb-2 hidden" id="guardian_wrapper">
          <label class="block text-sm font-medium">Opiekun</label>
          <input type="text" name="guardian" id="guardian_input" class="form-input w-full" disabled>
        </div>

        {{-- ▶︎ Kontakt (e-mail lub tel.) ◀︎ --}}
        <div class="mb-2">
          <label class="block text-sm font-medium">Kontakt (e-mail lub tel.)</label>
          <input type="text" name="contact" class="form-input w-full" required>
        </div>

        {{-- ▶︎ Uczniowie ◀︎ --}}
        <div id="students-wrapper">
          <div class="student-item mb-4 border p-4 rounded relative">
            <button
              type="button"
              class="remove-student absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-7 h-7 flex items-center justify-center hidden"
              aria-label="Usuń ucznia"
            >&times;</button>

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

  {{-- ▶︎ Skrypty ◀︎ --}}
  <script>
    function setupToggle(checkId, wrapperId, inputId) {
      const check   = document.getElementById(checkId);
      const wrapper = document.getElementById(wrapperId);
      const input   = document.getElementById(inputId);

      check.addEventListener('change', () => {
        if (check.checked) {
          wrapper.classList.remove('hidden');
          input.disabled = false;
          input.focus();
        } else {
          wrapper.classList.add('hidden');
          input.disabled = true;
          input.value = '';
        }
      });
    }

    setupToggle('toggle_teacher',  'teacher_wrapper',  'teacher_input');
    setupToggle('toggle_guardian', 'guardian_wrapper', 'guardian_input');

    const wrapper = document.getElementById('students-wrapper');
    const addBtn  = document.getElementById('add-student');
    let idx = 1;

    function resetFields(item) {
      item.querySelectorAll('input, select').forEach(el => {
        if (el.name && el.name.startsWith('students[')) {
          el.name = el.name.replace(/students\[\d+]/, `students[${idx}]`);
        }

        if (el.tagName === 'SELECT') {
          el.selectedIndex = 0;
        } else if (el.type === 'checkbox') {
          el.checked = false;
        } else {
          el.value = '';
        }
      });
    }

    function enableRemove(item) {
      const btn = item.querySelector('.remove-student');
      btn.classList.remove('hidden');
      btn.addEventListener('click', () => item.remove());
    }

    wrapper.querySelector('.student-item .remove-student').classList.add('hidden');

    addBtn.addEventListener('click', () => {
      const prototype = wrapper.querySelector('.student-item');
      const item = prototype.cloneNode(true);

      resetFields(item);
      wrapper.appendChild(item);
      enableRemove(item);

      idx++;
    });
  </script>
</x-app-layout>
