<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{{ $slot }}


{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ __('Konkurs') }}. {{ __('Wszystkie prawa zastreżone.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
