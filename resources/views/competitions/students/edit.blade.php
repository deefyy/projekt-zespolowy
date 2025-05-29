<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Edytuj ucznia: {{ $student->name }} {{ $student->last_name }}
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow rounded">

      {{-- Komunikaty walidacji --}}
      @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 rounded text-sm text-red-800">
          <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('students.update', $student) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Imię --}}
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Imię</label>
          <input type="text"
                 name="name"
                 value="{{ old('name', $student->name) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                 required>
        </div>

        {{-- Nazwisko --}}
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Nazwisko</label>
          <input type="text"
                 name="last_name"
                 value="{{ old('last_name', $student->last_name) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                 required>
        </div>

        {{-- Klasa – SELECT zamiast input --}}
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Klasa</label>
            <select name="class"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                    required>
                <option value="" disabled>– wybierz –</option>

                @foreach(($classes ?? []) as $c)
                    <option value="{{ $c }}"
                            {{ old('class', $student->class) == $c ? 'selected' : '' }}>
                        {{ $c }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Szkoła --}}
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Szkoła</label>
          <input type="text"
                 name="school"
                 value="{{ old('school', $student->school) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                 required>
        </div>
        
        {{-- Adres Szkoły --}}
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Adres Szkoły</label>
          <input type="text"
                 name="school_address"
                 value="{{ old('school_address', $student->school_address) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>

        {{-- Nauczyciel --}}
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Nauczyciel</label>
          <input type="text"
                 name="teacher"
                 value="{{ old('teacher', $student->teacher) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>

        {{-- Opiekun --}}
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Opiekun</label>
          <input type="text"
                 name="guardian"
                 value="{{ old('guardian', $student->guardian) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>

        {{-- Kontakt --}}
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Kontakt (e-mail lub tel.)</label>
          <input type="text"
                 name="contact"
                 value="{{ old('contact', $student->contact) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                 required>
        </div>

        {{-- Zgoda RODO --}}
        <div class="mb-6 flex items-center space-x-3">
          <input type="checkbox"
                 name="statement"
                 value="1"
                 class="form-checkbox h-5 w-5"
                 {{ old('statement', $student->statement) ? 'checked' : '' }}>
          <span class="text-sm">Wyrażam zgodę na przetwarzanie danych osobowych</span>
        </div>

        <div class="flex justify-end">
          <button type="submit"
                  class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Zapisz zmiany
          </button>
        </div>
      </form>

      {{-- Link powrotny --}}
      @php
        $registration = $student->competitionRegistrations()->first();
      @endphp

      @if ($registration && $registration->competition)
        <a href="{{ route('competitions.show', $registration->competition->id) }}"
           class="text-blue-500 hover:underline mt-4 inline-block">
          ← Wróć do konkursu
        </a>
      @else
        <a href="{{ route('competitions.index') }}"
           class="text-blue-500 hover:underline mt-4 inline-block">
          ← Wróć do listy konkursów
        </a>
      @endif
    </div>
  </div>
</x-app-layout>