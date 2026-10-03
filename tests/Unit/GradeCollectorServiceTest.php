<?php

namespace Tests\Unit;

use App\Services\GradeCollectorService;
use PHPUnit\Framework\TestCase;

class GradeCollectorServiceTest extends TestCase
{
    public function test_progress_with_eighty_percent_base_is_normalized_to_one_hundred(): void
    {
        $result = (new GradeCollectorService)->normalizeProgress(80, 80);

        $this->assertSame(100.0, $result);
    }

    public function test_each_progress_uses_its_own_base_weight(): void
    {
        $service = new GradeCollectorService;

        $this->assertSame(87.5, $service->normalizeProgress(70, 80));
        $this->assertSame(87.5, $service->normalizeProgress(87.5, 100));
    }

    public function test_progress_without_assigned_weight_returns_zero(): void
    {
        $this->assertSame(0.0, (new GradeCollectorService)->normalizeProgress(0, 0));
    }
}
