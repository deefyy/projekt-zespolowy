@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://i.imgur.com/T8STLoV.png class="logo" alt="Emu Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
