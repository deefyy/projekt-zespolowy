<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Importuj Zgłoszenia do Konkursu: ') }} {{ $competition->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 dark:bg-green-700 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 dark:bg-red-700 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any() && !$errors->has('excel_file_specific') && !session('validation_failures'))
                        <div class="mb-4 p-4 bg-red-100 dark:bg-red-700 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded">
                            <strong class="font-bold">{{ __('Wystąpiły błędy:') }}</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    @if ($errors->has('excel_file') && !session('validation_failures')) {{-- Nie pokazuj tego jeśli są błędy walidacji pliku --}}
                         <div class="mb-4 p-4 bg-red-100 dark:bg-red-700 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded">
                             {{ $errors->first('excel_file') }}
                         </div>
                    @endif

                    @if(session('validation_failures'))
                        <div class="mb-4 p-4 bg-red-100 dark:bg-red-700 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded">
                            <strong class="font-bold">{{ __('Błędy walidacji w pliku Excel:') }}</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach (session('validation_failures') as $failure)
                                    <li>
                                        {{ __('Wiersz') }}: {{ $failure->row() }}, {{ __('Kolumna') }}: {{ $failure->attribute() }} ({{ __('wartość') }}: '{{ $failure->values()[$failure->attribute()] ?? __('brak') }}')
                                        <ul class="ml-4 list-disc list-inside">
                                            @foreach ($failure->errors() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('competitions.importRegistrations', $competition) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-6">
                            <label for="excel_file" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Wybierz plik Excel (.xlsx, .xls, .csv):') }}</label>
                            <input type="file" name="excel_file" id="excel_file" required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                             @error('excel_file') {{-- Dodatkowa obsługa błędu bezpośrednio pod polem --}}
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">{{ __('Mapowanie Kolumn (opcjonalne)') }}</h3>
                            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Jeśli nazwy kolumn w Twoim pliku Excel różnią się od standardowych, możesz je tutaj zmapować. Wpisz dokładną nazwę kolumny z Twojego pliku obok odpowiadającego jej standardowego pola.') }}
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($expectedHeaders as $header)
                                    <div class="mb-3">
                                        <label for="mapping_{{ Str::slug($header) }}" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ __('Standardowe pole:') }} <strong class="italic">"{{ $header }}"</strong>
                                        </label>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('Twoja nazwa kolumny w pliku (jeśli inna):') }}</span>
                                        <input type="text"
                                               name="column_mappings[{{ $header }}]"
                                               id="mapping_{{ Str::slug($header) }}"
                                               value="{{ old('column_mappings.' . $header, '') }}"
                                               placeholder="{{ __('Np.') }} {{ $header }}"
                                               class="mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <button type="submit"
                                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                {{ __('Importuj Plik') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>