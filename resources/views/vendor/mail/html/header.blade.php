<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
    DEELS
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
