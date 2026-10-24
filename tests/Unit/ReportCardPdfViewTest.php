<?php

namespace Tests\Unit;

use Illuminate\Support\Collection;
use Tests\TestCase;

class ReportCardPdfViewTest extends TestCase
{
    public function test_pdf_header_uses_configured_year_and_official_logos_without_seal(): void
    {
        $html = view('panel.reportcard-pdf', [
            'reports' => collect([$this->report()]),
            'periodLabel' => 'Anual',
            'academicYearLabel' => '2026 - 2027',
        ])->render();

        $this->assertStringContainsString('Academic Report 2026 - 2027', $html);
        $this->assertStringContainsString('aci-crest.jpeg', $html);
        $this->assertStringContainsString('aci-wordmark.jpeg', $html);
        $this->assertStringNotContainsString('SELLO', $html);
    }

    public function test_pdf_header_falls_back_to_section_academic_year(): void
    {
        $html = view('panel.reportcard-pdf', [
            'reports' => collect([$this->report()]),
            'periodLabel' => 'Anual',
            'academicYearLabel' => null,
        ])->render();

        $this->assertStringContainsString('Academic Report 2025 - 2026', $html);
    }

    private function report(): array
    {
        return [
            'student' => [
                'academic_year' => 2026,
                'section_name' => 'Segundo A',
                'full_name' => 'Estudiante de prueba',
            ],
            'teacher_name' => 'Docente de prueba',
            'terms' => new Collection,
            'subjects' => [],
            'homeroom' => [],
            'final_conduct' => null,
            'is_annual' => true,
            'teacher_comments' => [],
            'signatures' => [
                'headmaster' => 'Dirección Académica',
                'teacher' => 'Docente de prueba',
            ],
        ];
    }
}
