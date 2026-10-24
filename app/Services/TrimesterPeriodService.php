<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrimesterPeriodService
{
    public function periods(): Collection
    {
        $periods = DB::table('trimestres')
            ->orderBy('numero')
            ->get(['id', 'nombre', 'numero', 'fecha_inicio']);

        return $periods->values()->map(function (object $period, int $index) use ($periods): object {
            $nextStart = $periods->get($index + 1)?->fecha_inicio;
            $period->fecha_fin = $nextStart
                ? CarbonImmutable::parse($nextStart)->subDay()->toDateString()
                : null;

            return $period;
        });
    }

    public function periodForDate(string $date): ?object
    {
        return $this->periods()->first(function (object $period) use ($date): bool {
            if (! $period->fecha_inicio || $date < $period->fecha_inicio) {
                return false;
            }

            return $period->fecha_fin === null || $date <= $period->fecha_fin;
        });
    }
}
