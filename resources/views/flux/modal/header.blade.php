@props([])

@php
$classes = Flux::classes('border-b border-zinc-200 dark:border-zinc-700 pb-4 mb-6');
@endphp

<div {{ $attributes->class($classes) }} data-flux-modal-header>
    {{ $slot }}
</div>