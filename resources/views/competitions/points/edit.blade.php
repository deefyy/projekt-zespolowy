{{-- resources/views/competitions/points/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Punkty – {{ $competition->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow rounded">

            {{-- komunikaty --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @elseif($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('competitions.points.update', $competition) }}">
                @csrf

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-2 border-b text-left font-medium">Uczeń</th>
                                @foreach($stages as $stage)
                                    <th class="px-4 py-2 border-b text-center font-medium">
                                        Etap&nbsp;{{ $stage->stage }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($students as $student)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b whitespace-nowrap">
                                        {{ $student->name }} {{ $student->last_name }}
                                        <span class="text-xs text-gray-500">({{ $student->class }})</span>
                                    </td>

                                    @foreach($stages as $stage)
                                        @php
                                            $current = $points[$student->id][$stage->id] ?? null;
                                        @endphp
                                        <td class="px-4 py-2 border-b text-center">
                                            <input
                                                type="number"
                                                min="0"
                                                name="points[{{ $student->id }}][{{ $stage->id }}]"
                                                value="{{ old('points.' . $student->id . '.' . $stage->id, $current) }}"
                                                class="w-24 border-gray-300 rounded text-center
                                                    focus:border-indigo-500 focus:ring-indigo-500" />
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex items-center gap-4">
                    <button type="submit"
                            class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                        Zapisz
                    </button>

                    <a href="{{ route('competitions.show', $competition) }}"
                       class="text-blue-500 underline">
                        ← Wróć do konkursu
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
