<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edytuj konkurs</h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow rounded">
      <form action="{{ route('competitions.update', $competition) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
          <label for="name" class="block text-sm font-medium text-gray-700">Nazwa</label>
          <input type="text" name="name" id="name" value="{{ old('name', $competition->name) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>

        <div class="mb-4">
          <label for="description" class="block text-sm font-medium text-gray-700">Opis</label>
          <textarea name="description" id="description" rows="4"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $competition->description) }}</textarea>
        </div>

        <div class="mb-4">
          <label for="start_date" class="block text-sm font-medium text-gray-700">Data rozpoczęcia</label>
          <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $competition->start_date) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>

        <div class="mb-4">
          <label for="end_date" class="block text-sm font-medium text-gray-700">Data zakończenia</label>
          <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $competition->end_date) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>

        <div class="mb-4">
          <label for="registration_deadline" class="block text-sm font-medium text-gray-700">Termin zapisów</label>
          <input type="date" name="registration_deadline" id="registration_deadline" value="{{ old('registration_deadline', $competition->registration_deadline) }}"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
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
