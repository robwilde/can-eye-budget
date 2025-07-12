<?php
/** @noinspection CallableParameterUseCaseInTypeContextInspection */

declare(strict_types=1);

namespace App\Helpers;

use Carbon\Carbon;

final class CalendarHelper
{
    /**
     * Get date range for a specific view period
     *
     * @param  Carbon  $date  The reference date
     * @param  string  $view  The view type ('day', 'week', 'month', 'year')
     * @return array{start: Carbon, end: Carbon}
     */
    public static function getDateRange(Carbon $date, string $view): array
    {
        $date = $date->copy();

        return match ($view) {
            'day' => [
                'start' => $date->startOfDay(),
                'end'   => $date->copy()->endOfDay(),
            ],
            'week' => [
                'start' => $date->startOfWeek(),
                'end'   => $date->copy()->endOfWeek(),
            ],
            'year' => [
                'start' => $date->startOfYear(),
                'end'   => $date->copy()->endOfYear(),
            ],
            default => [
                'start' => $date->startOfMonth(),
                'end'   => $date->copy()->endOfMonth(),
            ],
        };
    }

    /**
     * Get formatted title for a view period
     *
     * @param  Carbon  $date  The reference date
     * @param  string  $view  The view type ('day', 'week', 'month', 'year')
     */
    public static function getViewTitle(Carbon $date, string $view): string
    {
        return match ($view) {
            'day'   => $date->format('F j, Y'),
            'week'  => 'Week of '.$date->copy()->startOfWeek()->format('M j').' - '.$date->copy()->endOfWeek()->format('M j, Y'),
            'year'  => $date->format('Y'),
            default => $date->format('F Y'),
        };
    }

    /**
     * Get the next period date for navigation
     *
     * @param  Carbon  $date  The current date
     * @param  string  $view  The view type ('day', 'week', 'month', 'year')
     */
    public static function getNextPeriod(Carbon $date, string $view): Carbon
    {
        return match ($view) {
            'day'   => $date->addDay(),
            'week'  => $date->addWeek(),
            'year'  => $date->addYear(),
            default => $date->addMonth(),
        };
    }

    /**
     * Get the previous period date for navigation
     *
     * @param  Carbon  $date  The current date
     * @param  string  $view  The view type ('day', 'week', 'month', 'year')
     */
    public static function getPreviousPeriod(Carbon $date, string $view): Carbon
    {
        return match ($view) {
            'day'   => $date->subDay(),
            'week'  => $date->subWeek(),
            'year'  => $date->subYear(),
            default => $date->subMonth(),
        };
    }
}
