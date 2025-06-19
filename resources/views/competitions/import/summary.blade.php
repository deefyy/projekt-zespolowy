<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Import: Summary and Confirmation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-2xl font-bold text-[#002d62] mb-6">{{ __('Import Summary') }}</h3>
                    <p class="mb-6 text-sm text-gray-700">
                        {{ __('Below is a list of changes that will be applied upon confirmation. Please verify the data is correct.') }}
                    </p>

                    @if(!empty($changes['updated']))
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold text-yellow-600">{{ __('Records to be updated') }} ({{ count($changes['updated']) }})</h4>
                            <ul class="mt-3 space-y-4">
                                @foreach($changes['updated'] as $change)
                                    <li>
                                        <strong class="text-[#002d62]">{{ $change['name'] }}</strong> (ID: {{ $change['id'] }})
                                        <ul class="ml-4 mt-2 pl-4 border-l-4 border-[#cdd7e4] text-sm text-gray-600 space-y-1">
                                            @foreach($change['diff'] as $field => $values)
                                                <li>
                                                    <span class="font-semibold">{{ __($field) }}:</span>
                                                    <span class="line-through text-red-600">"{{ $values['old'] }}"</span>
                                                    <span class="mx-1 text-gray-400">→</span>
                                                    <span class="text-green-700 font-semibold">"{{ $values['new'] }}"</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- ZMIENIONA SEKCJA --}}
                    @if(!empty($changes['skipped']))
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold text-red-600">{{ __('Pominięte wiersze (nie zostaną zaimportowane)') }} ({{ count($changes['skipped']) }})</h4>
                            <ul class="mt-3 list-disc list-inside text-gray-700 text-sm">
                                @foreach($changes['skipped'] as $change)
                                    <li><strong>{{ $change['name'] }}</strong> ({{ __($change['reason']) }})</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!empty($changes['no_change']))
                        <div class="mb-8">
                            <h4 class="font-semibold text-gray-500">{{ __('Records with no changes') }} ({{ count($changes['no_change']) }})</h4>
                            <p class="text-sm text-gray-600">
                                @foreach($changes['no_change'] as $change)
                                    <span>{{ $change['name'] }}</span>@if(!$loop->last), @endif
                                @endforeach
                            </p>
                        </div>
                    @endif

                    @if(empty($changes['updated']) && empty($changes['skipped']))
                         <div class="p-4 bg-gray-100 text-gray-600 text-sm italic rounded-xl">
                            {{ __('No changes to import. The data in the file is identical to the data in the system.') }}
                         </div>
                    @endif

                    <div class="mt-10 pt-6 border-t border-[#cdd7e4] flex justify-end gap-4">
                        <a href="{{ route('competitions.showImportForm', $competition) }}"
                           class="text-sm text-gray-600 hover:text-[#002d62] transition">
                            {{ __('Cancel') }}
                        </a>
                        <form action="{{ route('competitions.processImport', $competition) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="bg-[#00703c] hover:bg-[#005e32] text-white font-semibold px-6 py-3 rounded-xl transition shadow">
                                {{ __('Confirm and Import') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>