<?php /** @noinspection PhpUnusedParameterInspection */

declare(strict_types=1);

namespace App\Livewire\Synthesizers;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Spatie\LaravelData\Data;

final class DataSynthesizer extends Synth
{
    public static string $key = 'data';

    public static function match($target): bool
    {
        return $target instanceof Data;
    }

    public function dehydrate($target, $dehydrateChild): array
    {
        return [
            'class' => get_class($target),
            'data'  => $target->toArray(),
        ];
    }

    public function hydrate($value, $hydrateChild): mixed
    {
        $class = $value['class'];

        return $class::from($value['data']);
    }
}
