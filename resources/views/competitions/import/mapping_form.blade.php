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

                <p class="mb-6 text-gray-700 text-sm">
                    {{ __('System nie był w stanie automatycznie dopasować poniższych pól. Wybierz odpowiednią kolumnę z Twojego pliku, która odpowiada wymaganemu polu systemowemu.') }}
                </p>

                <form action="{{ route('competitions.handleMapping', $competition) }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @if(!empty($unmatchedHeaders))
                            @foreach($unmatchedHeaders as $expected)
                                <div>
                                    <label for="mapping_{{ Str::slug($expected) }}" class="block mb-2 text-sm font-semibold text-[#002d62]">
                                        {{ __('Wymagane pole:') }} <span class="italic font-normal text-gray-800">"{{ $expected }}"</span>
                                    </label>
                                    <select name="column_mappings[{{ $expected }}]" id="mapping_{{ Str::slug($expected) }}"
                                            class="w-full rounded-xl border border-[#cdd7e4] text-gray-800 text-sm p-2 shadow-sm focus:ring-2 focus:ring-[#002d62]">
                                        <option value="">-- {{ __('-- Wybierz kolumnę z pliku --') }} --</option>
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
                        <button type="submit"
                                class="bg-[#00703c] hover:bg-[#005e32] text-white font-semibold px-6 py-3 rounded-xl transition shadow">
                            {{ __('Przejdź do podsumowania') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
