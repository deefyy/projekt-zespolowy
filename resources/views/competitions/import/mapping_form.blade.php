<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Import Submissions: Fill in Missing Mappings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if(session('error') || session('error_critical'))
                        <div class="mb-4 p-4 bg-red-100 dark:bg-red-700 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded">
                            {{ session('error') ?? session('error_critical') }}
                        </div>
                    @endif

                    <p class="mb-4 text-gray-600 dark:text-gray-400">
                        {{ __('The system could not automatically match the following fields. Please select the header from your file that corresponds to the required system field.') }}
                    </p>

                    <form action="{{ route('competitions.handleMapping', $competition) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @if(!empty($unmatchedHeaders))
                                @foreach($unmatchedHeaders as $expected)
                                    <div>
                                        <label for="mapping_{{ Str::slug($expected) }}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                            {{ __('Required field:') }} <strong class="italic">"{{ $expected }}"</strong>
                                        </label>
                                        <select name="column_mappings[{{ $expected }}]" id="mapping_{{ Str::slug($expected) }}"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                            <option value="">-- {{ __('-- Select a column from your file --') }} --</option>
                                            @if(!empty($availableHeadings))
                                                @foreach($availableHeadings as $actual)
                                                    <option value="{{ $actual }}" @if(old('column_mappings.'.$expected) === $actual) selected @endif>
                                                        {{ $actual }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="mt-8">
                            <button type="submit" class="text-white bg-green-700 hover:bg-green-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                {{ __('Proceed to Summary') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>