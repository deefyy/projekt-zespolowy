<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Import Zgłoszeń (Krok 1/2): Wgraj Plik') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 dark:bg-red-700 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <p class="mb-4 text-gray-600 dark:text-gray-400">
                        {{ __('Wybierz plik Excel do zaimportowania. W następnym kroku system przeanalizuje nagłówki kolumn i pozwoli Ci je dopasować.') }}
                    </p>

                    <form action="{{ route('competitions.handleImportUpload', $competition) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-6">
                            <label for="excel_file" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Plik Excel (.xlsx, .xls, .csv):') }}</label>
                            <input type="file" name="excel_file" id="excel_file" required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            @error('excel_file')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                {{ __('Przejdź do analizy pliku') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>