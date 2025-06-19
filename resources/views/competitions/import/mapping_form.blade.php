<x-app-layout>
    <x-slot name="header">
        <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[#002d62] text-center">
                    {{ __('Import Submissions: Fill in Missing Mappings') }}
                </h2>
            </div>
        </header>
    </x-slot>

    <div class="py-12 bg-[#f9fbfd] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-[#cdd7e4] shadow rounded-2xl p-6">
                
                @if(session('error') || session('error_critical'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-xl">
                        {{ session('error') ?? session('error_critical') }}
                    </div>
                @endif

                @if(!empty($unmatchedHeaders) && !empty($availableHeadings))
                    <p class="mb-6 text-gray-700 text-sm">
                        {{ __('The system could not automatically match the following fields. Please select the header from your file that corresponds to the required system field.') }}
                    </p>

                    <form action="{{ route('competitions.handleMapping', $competition) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($unmatchedHeaders as $expected)
                                <div>
                                    <label for="mapping_{{ Str::slug($expected) }}" class="block mb-2 text-sm font-semibold text-[#002d62]">
                                        {{ __('Required field:') }} <span class="italic font-normal text-gray-800">"{{ $expected }}"</span>
                                    </label>
                                    <select name="column_mappings[{{ $expected }}]" id="mapping_{{ Str::slug($expected) }}"
                                            class="w-full rounded-xl border border-[#cdd7e4] text-gray-800 text-sm p-2 shadow-sm focus:ring-2 focus:ring-[#002d62]">
                                        <option value="">-- {{ __('-- Select a column from your file --') }} --</option>
                                        @foreach($availableHeadings as $actual)
                                            <option value="{{ $actual }}" @if(old('column_mappings.'.$expected) === $actual) selected @endif>
                                                {{ $actual }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8">
                            <button type="submit"
                                    class="bg-[#00703c] hover:bg-[#005e32] text-white font-semibold px-6 py-3 rounded-xl transition shadow">
                                {{ __('Proceed to Summary') }}
                            </button>
                        </div>
                    </form>
                @else
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">{{ __('File analysis error') }}</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('The system could not find matching columns in your file. Please make sure the file has correct headers and try again.') }}
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('competitions.showImportForm', $competition) }}"
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                ← {{ __('Go back and upload another file') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>