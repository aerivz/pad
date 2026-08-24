<?php

namespace App\Support;

class GeneratedDocumentCatalog
{
    /**
     * @return array<int, array{code:string,label:string,description:string}>
     */
    public function all(): array
    {
        return [
            [
                'code' => 'report_card_annual_student',
                'label' => 'Boletin anual del alumno',
                'description' => 'Renderiza el PDF anual del alumno usando el formato actual de boletines del sistema.',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function codes(): array
    {
        return array_column($this->all(), 'code');
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        $labels = [];

        foreach ($this->all() as $item) {
            $labels[$item['code']] = $item['label'];
        }

        return $labels;
    }
}
