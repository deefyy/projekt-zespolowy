<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Edytuj ucznia
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow rounded">
      @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
          <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('students.update', $student) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
          <label for="name" class="block text-sm font-medium text-gray-700">Imię</label>
          <input type="text" name="name" id="name" value="{{ old('name', $student->name) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
        </div>

        <div class="mb-4">
          <label for="last_name" class="block text-sm font-medium text-gray-700">Nazwisko</label>
          <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $student->last_name) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
        </div>

        <div class="mb-4">
          <label for="class" class="block text-sm font-medium text-gray-700">Klasa</label>
          <input type="text" name="class" id="class" value="{{ old('class', $student->class) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
        </div>

        <div class="mb-4">
          <label for="school" class="block text-sm font-medium text-gray-700">Szkoła</label>
          <input type="text" name="school" id="school" value="{{ old('school', $student->school) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
        </div>

        <div class="flex justify-end">
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Zapisz zmiany
          </button>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
