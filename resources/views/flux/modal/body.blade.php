@props([])

@php
$classes = Flux::classes('');
@endphp

<div {{ $attributes->class($classes) }} data-flux-modal-body>
    {{ $slot }}
</div>