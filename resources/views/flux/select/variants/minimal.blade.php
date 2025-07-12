@props([
    'clearable' => null,
    'multiple' => null,
    'attributes' => [],
])

@php
$classes = Flux::classes('text-sm')
    ->add('border-0 bg-transparent text-zinc-800 dark:text-zinc-200 focus:ring-0')
    ->add('px-0 py-1')
    ->add('[&>option]:bg-white [&>option]:dark:bg-zinc-800');
@endphp

<select {{ $attributes->class($classes) }}>
    {{ $slot }}
</select>