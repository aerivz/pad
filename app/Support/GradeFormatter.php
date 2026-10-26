<?php

namespace App\Support;

final class GradeFormatter
{
    public static function whole(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return (string) (int) round((float) $value, 0, PHP_ROUND_HALF_UP);
    }
}
