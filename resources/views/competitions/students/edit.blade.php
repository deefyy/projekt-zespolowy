<x-app-layout>
  <x-slot name="header">
    <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-[#002d62] text-center">
          Edytuj ucznia: {{ $student->name }} {{ $student->last_name }}
        </h2>
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

      <form action="{{ route('students.update', $student) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        @php
          $fields = [
            ['name', 'Imię', true],
            ['last_name', 'Nazwisko', true],
            ['school', 'Szkoła', true],
            ['school_address', 'Adres Szkoły', false],
            ['teacher', 'Nauczyciel', false],
            ['guardian', 'Opiekun', false],
            ['contact', 'Kontakt (e-mail lub tel.)', true],
          ];
        @endphp

        @foreach ($fields as [$field, $label, $required])
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
            <input type="text"
                   name="{{ $field }}"
                   value="{{ old($field, $student->$field) }}"
                   maxlength="255"
                   oninput="updateCounter(this)"
                   class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 char-field"
                   @if($required) required @endif>
            <p class="text-xs text-gray-500 mt-1"><span class="count">0</span>/255 znaków</p>
          </div>
        @endforeach

        <div>
          <label class="block text-sm font-medium text-gray-700">Klasa</label>
          <select name="class" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>
            <option value="" disabled>– wybierz –</option>
            @foreach(($classes ?? []) as $c)
              <option value="{{ $c }}" {{ old('class', $student->class) == $c ? 'selected' : '' }}>
                {{ $c }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="flex items-center space-x-2">
          <input type="checkbox" name="statement" value="1"
                 class="form-checkbox h-5 w-5 text-blue-600"
                 {{ old('statement', $student->statement) ? 'checked' : '' }}>
          <label class="text-sm text-gray-700">Wyrażam zgodę na przetwarzanie danych osobowych</label>
        </div>

        <div class="flex justify-end">
          <button type="submit" class="bg-[#002d62] text-white px-6 py-2 rounded hover:bg-[#001b3c]">
            Zapisz zmiany
          </button>
        </div>
      </form>

      @php $registration = $student->competitionRegistrations()->first(); @endphp
      <a href="{{ $registration && $registration->competition
                  ? route('competitions.show', $registration->competition->id)
                  : route('competitions.index') }}"
         class="text-blue-500 hover:underline mt-6 inline-block">
        ← Wróć do {{ $registration && $registration->competition ? 'konkursu' : 'listy konkursów' }}
      </a>
    </div>
  </div>

  <script>
    function updateCounter(el) {
      const wrapper = el.closest('div');
      const counter = wrapper?.querySelector('.count');
      if (counter) counter.textContent = el.value.length;
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.char-field').forEach(updateCounter);
    });
  </script>
</x-app-layout>
