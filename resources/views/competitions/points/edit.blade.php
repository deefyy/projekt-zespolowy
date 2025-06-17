<x-app-layout>
    <x-slot name="header">
        <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[#002d62] text-center break-words">
                    {{ __('Manage points -') }} {{ $competition->name }}
                </h2>
            </div>
        </header>
    </x-slot>

    <div class="py-10 bg-[#f9fbfd]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-xl shadow">

                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                        {{ session('success') }}
                    </div>
                @elseif($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('competitions.points.update', $competition) }}">
                    @csrf

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded">
                            <thead class="bg-gray-100 text-[#002d62] text-sm font-semibold">
                                <tr>
                                    <th class="px-4 py-3 border-b text-left">{{ __('Student') }}</th>
                                    @foreach($stages as $stage)
                                        <th class="px-4 py-3 border-b text-center">{{ __('Stage') }} {{ $stage->stage }}</th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($students as $student)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 border-b">
                                            {{ $student->name }} {{ $student->last_name }}
                                            <span class="text-xs text-gray-500">({{ $student->class }})</span>
                                        </td>

                                        @foreach($stages as $stage)
                                            @php
                                                $current = $points[$student->id][$stage->id] ?? null;
                                            @endphp
                                            <td class="px-4 py-3 border-b text-center">
                                                <input
                                                    type="number"
                                                    min="0"
                                                    name="points[{{ $student->id }}][{{ $stage->id }}]"
                                                    value="{{ old('points.' . $student->id . '.' . $stage->id, $current) }}"
                                                    class="w-24 border-gray-300 rounded-lg text-center shadow-sm focus:border-[#002d62] focus:ring-[#002d62]" />
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex items-center gap-4">
                        <button type="submit"
                                class="bg-[#002d62] text-white px-6 py-2 rounded-xl hover:bg-[#001b3e]">
                            {{ __('Save') }}
                        </button>

                        <a href="{{ route('competitions.show', $competition) }}"
                           class="text-blue-500 underline">
                            ← {{ __('Back to competition') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>