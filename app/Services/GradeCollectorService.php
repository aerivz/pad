<?php

namespace App\Services;

use App\Models\CollectorCategory;

class GradeCollectorService
{
    public function quantityForType(string $type): int
    {
        return $type === 'laboratorio' ? 2 : 4;
    }

    public function calculateCategoryTotals(object $category, array $notes): array
    {
        $percentage = (float) $category->porcentaje;
        $type = (string) $category->tipo_calculo;

        $note1 = $this->nullableNumber($notes['nota_1'] ?? null);
        $note2 = $this->nullableNumber($notes['nota_2'] ?? null);
        $note3 = $this->nullableNumber($notes['nota_3'] ?? null);
        $note4 = $this->nullableNumber($notes['nota_4'] ?? null);

        if ($type === 'laboratorio') {
            return [
                'promedio_1' => $note1 !== null ? round($note1 * $percentage / 100, 2) : null,
                'promedio_2' => $note2 !== null ? round($note2 * $percentage / 100, 2) : null,
            ];
        }

        return [
            'promedio_1' => $note1 !== null && $note2 !== null ? round((($note1 + $note2) / 2) * $percentage / 100, 2) : null,
            'promedio_2' => $note3 !== null && $note4 !== null ? round((($note3 + $note4) / 2) * $percentage / 100, 2) : null,
        ];
    }

    public function nullableNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
