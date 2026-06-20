<tr @if ($style !== null) style="{{ $style }}" @endif>
    <th scope="row">{{ $label }}</th>
    @foreach ($amounts as $amount)
        <td class="text-end">{{ number_format($amount) }}</td>
    @endforeach
</tr>
