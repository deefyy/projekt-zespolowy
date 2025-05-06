<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dodaj nowy konkurs
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 bg-white shadow rounded p-6">
            <form method="POST" action="{{ route('competitions.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Nazwa konkursu</label>
                    <input type="text" name="name" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Opis</label>
                    <textarea name="description" class="form-input rounded-md shadow-sm mt-1 block w-full" required></textarea>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Data rozpoczęcia</label>
                    <input type="date" name="start_date" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Data zakończenia</label>
                    <input type="date" name="end_date" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Koniec zapisów</label>
                    <input type="date" name="registration_deadline" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        Zapisz konkurs
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
