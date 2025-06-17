<x-app-layout>
    <x-slot name="header">
        <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[#002d62] text-center">
                    {{ __('Import: Summary and Confirmation') }}
                </h2>
            </div>
        </header>
    </x-slot>

    <div class="py-12 bg-[#f9fbfd] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white border border-[#cdd7e4] shadow rounded-2xl p-6">
                <h3 class="text-2xl font-bold text-[#002d62] mb-6">{{ __('Import Summary') }}</h3>
                <p class="mb-6 text-sm text-gray-700">
                    {{ __('Poniżej znajduje się lista zmian, które zostaną zastosowane po zatwierdzeniu. Zweryfikuj poprawność danych.') }}
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

                @if(!empty($changes['created']))
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-blue-600">{{ __('Records to be created') }} ({{ count($changes['created']) }})</h4>
                        <ul class="mt-3 list-disc list-inside text-gray-700 text-sm">
                            @foreach($changes['created'] as $change)
                                <li><strong>{{ $change['name'] }}</strong> ({{ __($change['reason']) }})</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($changes['no_change']))
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-500">{{ __('Records with no changes') }} ({{ count($changes['no_change']) }})</h4>
                        <p class="text-sm text-gray-600">
                            @foreach($changes['no_change'] as $change)
                                <span>{{ $change['name'] }}</span>@if(!$loop->last), @endif
                            @endforeach
                        </p>
                    </div>
                @endif

                @if(empty($changes['updated']) && empty($changes['created']))
                    <div class="p-4 bg-gray-100 text-gray-600 text-sm italic rounded-xl">
                        {{ __('Brak zmian do zaimportowania. Dane w pliku są identyczne z danymi w systemie.') }}
                    </div>
                @endif

                <div class="mt-10 pt-6 border-t border-[#cdd7e4] flex justify-end gap-4">
                    <a href="{{ route('competitions.showImportForm', $competition) }}"
                       class="text-sm text-gray-600 hover:text-[#002d62] transition">
                        {{ __('Anuluj') }}
                    </a>
                    <form action="{{ route('competitions.processImport', $competition) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="bg-[#00703c] hover:bg-[#005e32] text-white font-semibold px-6 py-3 rounded-xl transition shadow">
                            {{ __('Zatwierdź i zaimportuj') }}
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
