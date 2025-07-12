@props([
    'selected' => null,
    'value' => null,
])

@php
$classes = Flux::classes('');
@endphp

<option
    {{ $attributes->class($classes) }}
    @if($value !== null) value="{{ $value }}" @endif
    @if($selected) selected @endif
>
    {{ $slot }}
</option>