@props([])

@php
$classes = Flux::classes('bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-sm');
@endphp

<div {{ $attributes->class($classes) }} data-flux-card>
    {{ $slot }}
</div>