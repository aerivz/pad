<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletines de notas</title>
    <style>
        @page {
            margin: 10px 12px 14px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 9.5px;
            margin: 0;
        }

        .report-page {
            width: 100%;
        }

        .report-page.page-break {
            page-break-before: always;
        }

        .page-header,
        .info-table,
        .grades-table,
        .comment-box,
        .signatures {
            width: 100%;
            border-collapse: collapse;
        }

        .page-header {
            margin-bottom: 6px;
        }

        .brand-left,
        .brand-right {
            width: 20%;
            vertical-align: middle;
        }

        .brand-center {
            width: 60%;
            text-align: center;
            vertical-align: middle;
        }

        .brand-left {
            text-align: left;
        }

        .brand-right {
            text-align: right;
        }

        .brand-logo-left {
            width: 58px;
            height: auto;
        }

        .brand-logo-right {
            width: 150px;
            height: auto;
        }

        .report-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .report-subtitle {
            font-size: 10.5px;
            font-weight: 700;
            margin-top: 2px;
        }

        .info-table {
            margin-bottom: 4px;
        }

        .info-table td,
        .grades-table th,
        .grades-table td,
        .comment-box td {
            border: 1px solid #4b5563;
            padding: 5px 6px;
        }

        .info-label {
            width: 11%;
            font-weight: 700;
            background: #f3f4f6;
        }

        .grades-table {
            table-layout: fixed;
        }

        .grades-table th {
            background: #f9fafb;
            text-transform: uppercase;
            font-size: 8.2px;
            text-align: center;
            line-height: 1.1;
        }

        .grades-table td {
            font-size: 9px;
            line-height: 1.2;
        }

        .report-page.density-spacious .grades-table th,
        .report-page.density-spacious .grades-table td {
            padding-top: 7px;
            padding-bottom: 7px;
        }

        .report-page.density-comfortable .grades-table th,
        .report-page.density-comfortable .grades-table td {
            padding-top: 6px;
            padding-bottom: 6px;
        }

        .report-page.density-compact .grades-table th,
        .report-page.density-compact .grades-table td {
            padding-top: 3.25px;
            padding-bottom: 3.25px;
            font-size: 7.6px;
        }

        .report-page.few-columns .subject-col {
            width: 18%;
        }

        .report-page.many-columns .grades-table th,
        .report-page.many-columns .grades-table td {
            font-size: 7.2px;
        }

        .report-page.density-compact.many-columns {
            font-size: 8px;
        }

        .report-page.density-compact.many-columns .report-title {
            font-size: 11px;
        }

        .report-page.density-compact.many-columns .report-subtitle {
            font-size: 9px;
        }

        .report-page.density-compact.many-columns .info-table td {
            padding: 3px 4px;
        }

        .report-page.density-compact.many-columns .grades-table th,
        .report-page.density-compact.many-columns .grades-table td {
            padding-top: 2.25px;
            padding-bottom: 2.25px;
            font-size: 6.5px;
        }

        .report-page.density-compact.many-columns .comment-box {
            margin-top: 5px;
            margin-bottom: 7px;
            min-height: 42px;
        }

        .report-page.density-compact.many-columns .comment-box td {
            padding: 5px 6px;
        }

        .report-page.density-compact.many-columns .comment-text,
        .report-page.density-compact.many-columns .signatures td {
            font-size: 8px;
        }

        .subject-col {
            width: 11%;
            text-align: left !important;
            font-weight: 700;
            word-break: break-word;
        }

        .score {
            text-align: center;
            font-weight: 700;
            color: #0f5132;
        }

        .empty {
            color: #9ca3af;
            font-weight: 400;
        }

        .comment-box {
            margin: 8px 0 10px;
            min-height: 54px;
        }

        .comment-label {
            width: 15%;
            font-weight: 700;
            vertical-align: top;
            background: #f9fafb;
        }

        .comment-text {
            font-size: 10px;
            line-height: 1.25;
            padding: 8px 10px;
        }

        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            font-size: 9.5px;
        }

        .signature-line {
            width: 62%;
            margin: 0 auto 4px;
            border-top: 1px solid #111827;
            height: 12px;
        }

    </style>
</head>
<body>
    @foreach ($reports as $report)
        @php
            $subjectCount = count($report['subjects']);
            $densityClass = $subjectCount <= 12 ? 'density-spacious' : ($subjectCount <= 17 ? 'density-comfortable' : 'density-compact');
            $columnClass = count($report['terms']) <= 2 ? 'few-columns' : 'many-columns';
        @endphp
        <div class="report-page {{ $densityClass }} {{ $columnClass }} {{ ! $loop->first ? 'page-break' : '' }}">
            <table class="page-header">
                <tr>
                    <td class="brand-left">
                        <img class="brand-logo-left" src="{{ public_path('images/aci-crest.jpeg') }}" alt="Escudo ACI">
                    </td>
                    <td class="brand-center">
                        <div class="report-title">Academic Report {{ filled($academicYearLabel ?? null) ? $academicYearLabel : (($report['student']['academic_year'] - 1).' - '.$report['student']['academic_year']) }}</div>
                        <div class="report-subtitle">{{ $report['student']['section_name'] }}</div>
                        <div>{{ $periodLabel ?? ($report['is_annual'] ? 'Anual' : 'Período seleccionado') }}</div>
                    </td>
                    <td class="brand-right">
                        <img class="brand-logo-right" src="{{ public_path('images/aci-wordmark.jpeg') }}" alt="Academia Cristiana Internacional">
                    </td>
                </tr>
            </table>

            <table class="info-table">
                <tr>
                    <td class="info-label">Student:</td>
                    <td>{{ $report['student']['full_name'] }}</td>
                </tr>
                <tr>
                    <td class="info-label">Teacher:</td>
                    <td>{{ $report['teacher_name'] }}</td>
                </tr>
            </table>

            <table class="grades-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="subject-col">Subjects</th>
                        @foreach ($report['terms'] as $term)
                            <th colspan="3">{{ $term->nombre }}</th>
                            @if ((int) $term->numero === 2)<th rowspan="2">Semestral Eval.</th>@endif
                            @if ((int) $term->numero === 4)<th rowspan="2">Final Eval.</th>@endif
                        @endforeach
                        @if ($report['is_annual'])<th rowspan="2">Final Period</th>@endif
                    </tr>
                    <tr>
                        @foreach ($report['terms'] as $term)
                            <th>P1</th>
                            <th>P2</th>
                            <th>Avg</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['subjects'] as $subject)
                        <tr>
                            <td class="subject-col">{{ $subject['subject'] }}</td>
                            @foreach ($report['terms'] as $term)
                                @php($termData = $subject['terms'][$term->numero] ?? null)
                                <td class="score {{ ($termData['progress_1'] ?? null) === null ? 'empty' : '' }}">{{ \App\Support\GradeFormatter::whole($termData['progress_1'] ?? null) }}</td>
                                <td class="score {{ ($termData['progress_2'] ?? null) === null ? 'empty' : '' }}">{{ \App\Support\GradeFormatter::whole($termData['progress_2'] ?? null) }}</td>
                                <td class="score {{ ($termData['period_average'] ?? null) === null ? 'empty' : '' }}">{{ \App\Support\GradeFormatter::whole($termData['period_average'] ?? null) }}</td>
                                @if ((int) $term->numero === 2)
                                    <td class="score {{ $subject['semester_evaluation'] === null ? 'empty' : '' }}">{{ \App\Support\GradeFormatter::whole($subject['semester_evaluation']) }}</td>
                                @endif
                                @if ((int) $term->numero === 4)
                                    <td class="score {{ $subject['final_evaluation'] === null ? 'empty' : '' }}">{{ \App\Support\GradeFormatter::whole($subject['final_evaluation']) }}</td>
                                @endif
                            @endforeach
                            @if ($report['is_annual'])<td class="score {{ $subject['final_period'] === null ? 'empty' : '' }}">{{ \App\Support\GradeFormatter::whole($subject['final_period']) }}</td>@endif
                        </tr>
                    @endforeach
                    <tr>
                        <td class="subject-col">Conduct</td>
                        @foreach ($report['terms'] as $term)
                            <td colspan="3" class="score">{{ \App\Support\GradeFormatter::whole($report['homeroom'][$term->numero]['conduct'] ?? null) }}</td>
                            @if ((int) $term->numero === 2)<td></td>@endif
                            @if ((int) $term->numero === 4)<td></td>@endif
                        @endforeach
                        @if ($report['is_annual'])<td class="score">{{ \App\Support\GradeFormatter::whole($report['final_conduct']) }}</td>@endif
                    </tr>
                </tbody>
            </table>

            <table class="comment-box">
                <tr>
                    <td class="comment-label">Teacher's comment:</td>
                    <td class="comment-text">
                        @forelse ($report['teacher_comments'] as $term => $comment)
                            <strong>Trimestre {{ $term }}:</strong> {{ $comment }}@if (! $loop->last)<br>@endif
                        @empty
                            Sin comentarios para el período mostrado.
                        @endforelse
                    </td>
                </tr>
            </table>

            <table class="signatures">
                <tr>
                    <td>
                        <div class="signature-line"></div>
                        <strong>Dirección</strong>
                    </td>
                    <td>
                        <div class="signature-line"></div>
                        <strong>{{ $report['signatures']['teacher'] }}</strong><br>
                        Teacher
                    </td>
                </tr>
            </table>
        </div>
    @endforeach
</body>
</html>
