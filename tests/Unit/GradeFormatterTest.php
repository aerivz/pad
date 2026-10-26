<?php

namespace Tests\Unit;

use App\Support\GradeFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GradeFormatterTest extends TestCase
{
    #[DataProvider('gradeCases')]
    public function test_it_rounds_grades_to_the_nearest_whole_number(mixed $value, string $expected): void
    {
        $this->assertSame($expected, GradeFormatter::whole($value));
    }

    public static function gradeCases(): array
    {
        return [
            'half rounds up' => [92.5, '93'],
            'below half rounds down' => [92.4, '92'],
            'more decimals below half round down' => [92.49, '92'],
            'whole grade remains unchanged' => [92, '92'],
            'null is empty' => [null, '-'],
            'empty string is empty' => ['', '-'],
        ];
    }
}
