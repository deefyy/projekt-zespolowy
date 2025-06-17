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
                    <h3 class="text-lg font-semibold mb-4">{{ __('Import Summary') }}</h3>
                    <p class="mb-6 text-gray-600 dark:text-gray-400">
                        {{ __('Below is a list of changes that will be made upon confirmation. Please verify everything is correct.') }}
                    </p>

                    @if(!empty($changes['updated']))
                        <div class="mb-6">
                            <h4 class="font-bold text-yellow-600 dark:text-yellow-400">{{ __('Records to be updated') }} ({{ count($changes['updated']) }}):</h4>
                            <ul class="mt-2 list-disc list-inside space-y-3">
                                @foreach($changes['updated'] as $change)
                                    <li>
                                        <strong>{{ $change['name'] }}</strong> (ID: {{ $change['id'] }})
                                        <ul class="ml-6 text-sm text-gray-500 dark:text-gray-400 border-l-2 border-gray-200 dark:border-gray-700 pl-4 mt-1">
                                            @foreach($change['diff'] as $field => $values)
                                                <li>
                                                    <span class="font-semibold">{{ __($field) }}:</span> 
                                                    <span class="text-red-600 dark:text-red-400 line-through">"{{ $values['old'] }}"</span> 
                                                    <span class="font-bold mx-1 text-gray-400 dark:text-gray-500">→</span> 
                                                    <span class="text-green-600 dark:text-green-400">"{{ $values['new'] }}"</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    @if(!empty($changes['created']))
                        <div class="mb-6">
                            <h4 class="font-bold text-blue-600 dark:text-blue-400">{{ __('Records to be created') }} ({{ count($changes['created']) }}):</h4>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach($changes['created'] as $change)
                                    <li><strong>{{ $change['name'] }}</strong> ({{ __($change['reason']) }})</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!empty($changes['no_change']))
                        <div class="mb-6">
                            <h4 class="font-bold text-gray-500 dark:text-gray-400">{{ __('Records with no changes') }} ({{ count($changes['no_change']) }}):</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @foreach($changes['no_change'] as $change)
                                    <span>{{ $change['name'] }}</span>@if(!$loop->last), @endif
                                @endforeach
                            </p>
                        </div>
                    @endif

                    @if(empty($changes['updated']) && empty($changes['created']))
                         <div class="mt-4 p-4 bg-gray-100 dark:bg-gray-700 rounded-md">
                            <p class="text-gray-600 dark:text-gray-300 italic">{{ __('No changes to import. The data in the file is identical to the data in the system.') }}</p>
                         </div>
                    @endif

                    <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6 flex items-center justify-end space-x-4">
                        <a href="{{ route('competitions.showImportForm', $competition) }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            {{ __('Cancel') }}
                        </a>
                        <form action="{{ route('competitions.processImport', $competition) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-white bg-green-700 hover:bg-green-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                {{ __('Confirm and Perform Import') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>