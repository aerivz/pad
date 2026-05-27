<?php

namespace App\Services;

use App\Models\CollectorCategory;
use App\Models\StudentCategoryGrade;
use App\Models\StudentConduct;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class GradeCollectorImportService
{
    public function __construct(
        private readonly GradeCollectorService $collectorService,
    ) {
    }

    public function import(UploadedFile $file, int $assignmentId, int $trimesterId, bool $resetBeforeImport = false): array
    {
        $assignment = DB::table('asignaciones as ag')
            ->where('ag.activo', true)
            ->join('secciones as s', 's.id', '=', 'ag.seccion_id')
            ->join('materias as m', 'm.id', '=', 'ag.materia_id')
            ->select('ag.id', 'ag.seccion_id', 'ag.anio_escolar', 's.grado', 's.nombre as seccion', 'm.nombre as materia')
            ->where('ag.id', $assignmentId)
            ->first();

        if (! $assignment) {
            throw new RuntimeException('Asignacion no encontrada para importacion.');
        }

        $students = DB::table('alumnos')
            ->where('seccion_id', $assignment->seccion_id)
            ->where('activo', true)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos']);

        if ($students->isEmpty()) {
            throw new RuntimeException('Seccion sin alumnos activos. No se puede importar.');
        }

        $parsed = $this->parseFile($file);
        $categoryTotal = round(collect($parsed['categories'])->sum('percentage'), 2);

        if ($categoryTotal > 100) {
            throw new RuntimeException('Las categorias del archivo superan 100%.');
        }

        $studentMap = $this->buildStudentMap($students);

        return DB::transaction(function () use ($assignmentId, $trimesterId, $resetBeforeImport, $parsed, $studentMap, $categoryTotal): array {
            if ($resetBeforeImport) {
                $this->deactivateExistingData($assignmentId, $trimesterId);
            }

            $categories = [];
            $createdCategories = 0;
            $updatedCategories = 0;
            $createdGrades = 0;
            $updatedGrades = 0;
            $createdConduct = 0;
            $updatedConduct = 0;
            $studentHits = [];
            $errors = [];

            foreach ($parsed['categories'] as $categoryData) {
                $category = CollectorCategory::query()
                    ->where('asignacion_id', $assignmentId)
                    ->where('trimestre_id', $trimesterId)
                    ->whereRaw('LOWER(nombre) = ?', [Str::lower($categoryData['name'])])
                    ->first();

                $payload = [
                    'asignacion_id' => $assignmentId,
                    'trimestre_id' => $trimesterId,
                    'nombre' => $categoryData['name'],
                    'porcentaje' => $categoryData['percentage'],
                    'tipo_calculo' => $categoryData['type'],
                    'cantidad_notas' => $categoryData['quantity'],
                    'orden' => $categoryData['order'],
                    'activo' => true,
                ];

                if ($category) {
                    $category->update($payload);
                    $updatedCategories++;
                } else {
                    $category = CollectorCategory::query()->create($payload);
                    $createdCategories++;
                }

                $categories[Str::lower($categoryData['name'])] = $category;
            }

            foreach ($parsed['rows'] as $row) {
                $normalizedStudent = $this->normalizeText($row['student']);

                if (! isset($studentMap[$normalizedStudent])) {
                    $errors[] = 'Alumno no encontrado en seccion: '.$row['student'];
                    continue;
                }

                if ($studentMap[$normalizedStudent] === null) {
                    $errors[] = 'Alumno ambiguo en seccion: '.$row['student'];
                    continue;
                }

                $student = $studentMap[$normalizedStudent];
                $studentHits[$student->id] = true;

                foreach ($row['categories'] as $categoryName => $notes) {
                    $category = $categories[Str::lower($categoryName)] ?? null;

                    if (! $category) {
                        $errors[] = 'Categoria no encontrada en importacion: '.$categoryName;
                        continue;
                    }

                    $totals = $this->collectorService->calculateCategoryTotals($category, $notes);

                    $grade = StudentCategoryGrade::query()
                        ->where('categoria_id', $category->id)
                        ->where('alumno_id', $student->id)
                        ->first();

                    $payload = [
                        'nota_1' => $this->collectorService->nullableNumber($notes['nota_1'] ?? null),
                        'nota_2' => $this->collectorService->nullableNumber($notes['nota_2'] ?? null),
                        'nota_3' => $this->collectorService->nullableNumber($notes['nota_3'] ?? null),
                        'nota_4' => $this->collectorService->nullableNumber($notes['nota_4'] ?? null),
                        'promedio_1' => $totals['promedio_1'],
                        'promedio_2' => $totals['promedio_2'],
                        'activo' => true,
                    ];

                    if ($grade) {
                        $grade->update($payload);
                        $updatedGrades++;
                    } else {
                        StudentCategoryGrade::query()->create([
                            'categoria_id' => $category->id,
                            'alumno_id' => $student->id,
                            ...$payload,
                        ]);
                        $createdGrades++;
                    }
                }

                if (array_key_exists('conduct', $row)) {
                    $conductValue = $this->collectorService->nullableNumber($row['conduct']);
                    $conduct = StudentConduct::query()
                        ->where('asignacion_id', $assignmentId)
                        ->where('trimestre_id', $trimesterId)
                        ->where('alumno_id', $student->id)
                        ->first();

                    if ($conduct) {
                        $conduct->update([
                            'valor' => $conductValue,
                            'activo' => true,
                        ]);
                        $updatedConduct++;
                    } else {
                        StudentConduct::query()->create([
                            'asignacion_id' => $assignmentId,
                            'trimestre_id' => $trimesterId,
                            'alumno_id' => $student->id,
                            'valor' => $conductValue,
                            'activo' => true,
                        ]);
                        $createdConduct++;
                    }
                }
            }

            $errors = array_values(array_unique($errors));

            if ($categoryTotal < 100) {
                $errors[] = 'Categorias importadas suman '.$categoryTotal.'%. Falta completar 100% para Report Card.';
            }

            return [
                'created_categories' => $createdCategories,
                'updated_categories' => $updatedCategories,
                'created_grades' => $createdGrades,
                'updated_grades' => $updatedGrades,
                'created_conduct' => $createdConduct,
                'updated_conduct' => $updatedConduct,
                'matched_students' => count($studentHits),
                'errors' => $errors,
            ];
        });
    }

    private function parseFile(UploadedFile $file): array
    {
        $extension = Str::lower($file->getClientOriginalExtension());

        return match ($extension) {
            'xlsx' => $this->parseCollectorMatrix($this->xlsxRows($file->getRealPath())),
            'csv', 'txt' => $this->parseCollectorMatrix($this->csvRows($file->getRealPath())),
            default => throw new RuntimeException('Formato no soportado. Usa XLSX o CSV.'),
        };
    }

    private function csvRows(string $path): array
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (! $lines) {
            throw new RuntimeException('Archivo CSV vacio.');
        }

        $delimiter = $this->detectDelimiter($lines[0]);

        return array_map(
            fn (string $line) => array_map(
                fn ($value) => trim((string) $value),
                str_getcsv($line, $delimiter)
            ),
            $lines
        );
    }

    private function parseCollectorMatrix(array $rows): array
    {
        $studentHeaderRowIndex = null;
        $studentColumn = null;

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                if ($this->normalizeText($value) === 'estudiante') {
                    $studentHeaderRowIndex = $rowIndex;
                    $studentColumn = $columnIndex;
                    break 2;
                }
            }
        }

        if ($studentHeaderRowIndex === null || $studentColumn === null) {
            throw new RuntimeException('No se encontro columna ESTUDIANTE en archivo.');
        }

        $categoryRowIndex = null;

        for ($rowIndex = $studentHeaderRowIndex - 1; $rowIndex >= 0; $rowIndex--) {
            if ($this->rowHasContentAfterColumn($rows[$rowIndex] ?? [], $studentColumn + 1)) {
                $categoryRowIndex = $rowIndex;
                break;
            }
        }

        if ($categoryRowIndex === null) {
            throw new RuntimeException('No se detecto fila de categorias en archivo.');
        }

        $labelRowIndex = null;
        $dataStartRow = null;

        for ($rowIndex = $studentHeaderRowIndex + 1; $rowIndex < count($rows); $rowIndex++) {
            $studentValue = trim((string) ($rows[$rowIndex][$studentColumn] ?? ''));

            if ($studentValue !== '' && $this->parseNumeric($rows[$rowIndex][0] ?? null) !== null) {
                $dataStartRow = $rowIndex;
                break;
            }

            if ($labelRowIndex === null && $this->rowHasContentAfterColumn($rows[$rowIndex] ?? [], $studentColumn + 1)) {
                $labelRowIndex = $rowIndex;
            }
        }

        if ($labelRowIndex === null || $dataStartRow === null) {
            throw new RuntimeException('No se detecto estructura de colector en archivo.');
        }

        $categoryRow = $rows[$categoryRowIndex];
        $weightRow = $rows[$studentHeaderRowIndex];
        $labelRow = $rows[$labelRowIndex];
        $maxColumns = max(count($categoryRow), count($weightRow), count($labelRow));
        $currentCategory = '';
        $categoryColumns = [];
        $categories = [];
        $hasConductHeader = false;

        for ($rowIndex = 0; $rowIndex < $dataStartRow; $rowIndex++) {
            foreach (($rows[$rowIndex] ?? []) as $value) {
                if ($this->normalizeText((string) $value) === 'nota de conducta') {
                    $hasConductHeader = true;
                    break 2;
                }
            }
        }

        for ($columnIndex = $studentColumn + 1; $columnIndex < $maxColumns; $columnIndex++) {
            $categoryCell = trim((string) ($categoryRow[$columnIndex] ?? ''));

            if ($categoryCell !== '') {
                $currentCategory = $this->formatCategoryName($categoryCell);

                if ($this->normalizeText($currentCategory) === 'nota de conducta') {
                    continue;
                }
            }

            if ($currentCategory === '' || $this->shouldSkipCategory($currentCategory)) {
                continue;
            }

            $label = trim((string) ($labelRow[$columnIndex] ?? ''));

            if ($label === '' || $this->shouldSkipEvaluationLabel($label)) {
                continue;
            }

            $weight = $this->normalizeWeight($this->parseNumeric($weightRow[$columnIndex] ?? null));

            if (! isset($categoryColumns[$currentCategory])) {
                $categoryColumns[$currentCategory] = [
                    'percentage' => $weight,
                    'raw_columns' => [],
                ];
            }

            $categoryColumns[$currentCategory]['raw_columns'][] = $columnIndex;
        }

        $order = 1;

        foreach ($categoryColumns as $categoryName => $data) {
            $quantity = count($data['raw_columns']);
            $type = $quantity <= 2 ? 'laboratorio' : 'normal';

            $categories[] = [
                'name' => $categoryName,
                'percentage' => $data['percentage'] > 0 ? $data['percentage'] : 0,
                'type' => $type,
                'quantity' => $type === 'laboratorio' ? 2 : 4,
                'order' => $order++,
                'columns' => $data['raw_columns'],
            ];
        }

        if ($categories === []) {
            throw new RuntimeException('No se detectaron categorias importables en archivo.');
        }

        $parsedRows = [];

        for ($rowIndex = $dataStartRow; $rowIndex < count($rows); $rowIndex++) {
            $student = trim((string) ($rows[$rowIndex][$studentColumn] ?? ''));

            if ($student === '') {
                continue;
            }

            $rowData = [
                'student' => $student,
                'categories' => [],
            ];

            foreach ($categories as $category) {
                $notes = [
                    'nota_1' => null,
                    'nota_2' => null,
                    'nota_3' => null,
                    'nota_4' => null,
                ];

                foreach ($category['columns'] as $rawIndex => $columnIndex) {
                    $notes['nota_'.($rawIndex + 1)] = $this->parseNumeric($rows[$rowIndex][$columnIndex] ?? null);
                }

                $rowData['categories'][$category['name']] = $notes;
            }

            if ($hasConductHeader) {
                $rowData['conduct'] = $this->parseNumeric($this->lastFilledCellValue($rows[$rowIndex] ?? []));
            }

            $parsedRows[] = $rowData;
        }

        return [
            'categories' => array_map(fn ($category) => [
                'name' => $category['name'],
                'percentage' => $category['percentage'],
                'type' => $category['type'],
                'quantity' => $category['quantity'],
                'order' => $category['order'],
            ], $categories),
            'rows' => $parsedRows,
        ];
    }

    private function xlsxRows(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('No se pudo abrir archivo XLSX.');
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetPath = $this->firstWorksheetPath($zip);
        $sheetXml = $zip->getFromName($sheetPath);
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('No se pudo leer hoja XLSX.');
        }

        $dom = new \DOMDocument();
        $dom->loadXML($sheetXml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = [];

        foreach ($xpath->query('//main:sheetData/main:row') as $row) {
            $cells = [];

            foreach ($xpath->query('./main:c', $row) as $cell) {
                $reference = $cell->attributes->getNamedItem('r')?->nodeValue ?? '';
                $columnIndex = $this->columnReferenceToIndex(preg_replace('/\d+/', '', $reference));
                $cells[$columnIndex] = $this->xlsxCellValue($cell, $sharedStrings);
            }

            if ($cells !== []) {
                ksort($cells);
                $rows[] = $this->expandSparseRow($cells);
            }
        }

        return $rows;
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($sharedXml === false) {
            return [];
        }

        $dom = new \DOMDocument();
        $dom->loadXML($sharedXml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $values = [];

        foreach ($xpath->query('//main:si') as $item) {
            $parts = [];

            foreach ($xpath->query('.//main:t', $item) as $part) {
                $parts[] = $part->nodeValue;
            }

            $values[] = implode('', $parts);
        }

        return $values;
    }

    private function firstWorksheetPath(ZipArchive $zip): string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relsXml === false) {
            throw new RuntimeException('Estructura XLSX incompleta.');
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);

        if (! $workbook || ! $rels) {
            throw new RuntimeException('No se pudo leer metadata XLSX.');
        }

        $sheet = $workbook->sheets->sheet[0] ?? null;

        if (! $sheet) {
            throw new RuntimeException('XLSX sin hojas.');
        }

        $relationId = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->id;

        foreach ($rels->Relationship as $relationship) {
            if ((string) $relationship['Id'] === $relationId) {
                return 'xl/'.ltrim((string) $relationship['Target'], '/');
            }
        }

        throw new RuntimeException('No se encontro hoja de trabajo en XLSX.');
    }

    private function xlsxCellValue(object $cell, array $sharedStrings): string
    {
        $type = $cell->attributes->getNamedItem('t')?->nodeValue ?? '';
        $valueNode = null;
        $inlineNode = null;

        foreach ($cell->childNodes as $childNode) {
            if ($childNode->nodeName === 'v') {
                $valueNode = $childNode;
            }

            if ($childNode->nodeName === 'is') {
                $inlineNode = $childNode;
            }
        }

        $value = $valueNode?->nodeValue ?? '';

        return match ($type) {
            's' => $sharedStrings[(int) $value] ?? '',
            'inlineStr' => $inlineNode?->textContent ?? '',
            default => trim($value),
        };
    }

    private function expandSparseRow(array $row): array
    {
        $expanded = [];
        $maxIndex = max(array_keys($row));

        for ($index = 0; $index <= $maxIndex; $index++) {
            $expanded[$index] = trim((string) ($row[$index] ?? ''));
        }

        return $expanded;
    }

    private function columnReferenceToIndex(string $column): int
    {
        $column = Str::upper($column);
        $index = 0;

        for ($i = 0; $i < strlen($column); $i++) {
            $index = ($index * 26) + (ord($column[$i]) - 64);
        }

        return $index - 1;
    }

    private function detectDelimiter(string $line): string
    {
        $delimiters = [',' => substr_count($line, ','), ';' => substr_count($line, ';'), "\t" => substr_count($line, "\t")];
        arsort($delimiters);

        return array_key_first($delimiters);
    }

    private function buildStudentMap(Collection $students): array
    {
        $map = [];

        foreach ($students as $student) {
            $variants = [
                $this->normalizeText($student->nombres.' '.$student->apellidos),
                $this->normalizeText($student->apellidos.' '.$student->nombres),
                $this->normalizeText($student->apellidos.', '.$student->nombres),
            ];

            foreach (array_unique($variants) as $variant) {
                if ($variant === '') {
                    continue;
                }

                if (! isset($map[$variant])) {
                    $map[$variant] = $student;
                    continue;
                }

                if ($map[$variant]?->id !== $student->id) {
                    $map[$variant] = null;
                }
            }
        }

        return $map;
    }

    private function deactivateExistingData(int $assignmentId, int $trimesterId): void
    {
        $categoryIds = CollectorCategory::query()
            ->where('asignacion_id', $assignmentId)
            ->where('trimestre_id', $trimesterId)
            ->pluck('id');

        if ($categoryIds->isNotEmpty()) {
            StudentCategoryGrade::query()
                ->whereIn('categoria_id', $categoryIds)
                ->update(['activo' => false]);
        }

        CollectorCategory::query()
            ->where('asignacion_id', $assignmentId)
            ->where('trimestre_id', $trimesterId)
            ->update(['activo' => false]);

        StudentConduct::query()
            ->where('asignacion_id', $assignmentId)
            ->where('trimestre_id', $trimesterId)
            ->update(['activo' => false]);
    }

    private function normalizeText(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->value();
    }

    private function rowHasContentAfterColumn(array $row, int $startColumn): bool
    {
        for ($column = $startColumn; $column < count($row); $column++) {
            if (trim((string) ($row[$column] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    private function shouldSkipCategory(string $value): bool
    {
        return in_array($this->normalizeText($value), ['1', 'report card'], true);
    }

    private function shouldSkipEvaluationLabel(string $value): bool
    {
        return str_starts_with($this->normalizeText($value), 'pr')
            || in_array($this->normalizeText($value), ['progress 1', 'progress 2', 'report card', 'total', 'promedio'], true);
    }

    private function parseNumeric(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $text = str_replace([' ', '%'], '', trim((string) $value));

        if (preg_match('/^-?\d+,\d+$/', $text)) {
            $text = str_replace(',', '.', $text);
        } elseif (str_contains($text, ',') && str_contains($text, '.')) {
            $text = str_replace(',', '', $text);
        }

        return is_numeric($text) ? (float) $text : null;
    }

    private function normalizeWeight(?float $value): float
    {
        if ($value === null) {
            return 0;
        }

        return $value > 0 && $value <= 1 ? round($value * 100, 2) : round($value, 2);
    }

    private function formatCategoryName(string $value): string
    {
        $normalized = Str::of($value)->trim()->lower()->value();

        return match ($normalized) {
            'tareas' => 'Tareas',
            'examenes', 'exámenes' => 'Examenes',
            'laboratorios' => 'Laboratorios',
            'actividades' => 'Actividades',
            'participacion', 'participación' => 'Participacion',
            'expresion oral y escrita', 'expresión oral y escrita' => 'Expresion Oral y Escrita',
            'dominio conceptual y semantica', 'dominio conceptual y semántica' => 'Dominio Conceptual y Semantica',
            default => Str::title($normalized),
        };
    }

    private function lastFilledCellValue(array $row): mixed
    {
        for ($index = count($row) - 1; $index >= 0; $index--) {
            $value = $row[$index] ?? null;

            if (trim((string) $value) !== '') {
                return $value;
            }
        }

        return null;
    }
}
