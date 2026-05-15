<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletines anuales</title>
    <style>
        @page {
            margin: 10px 12px 14px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 7px;
            margin: 0;
        }

        .report-page {
            width: 100%;
        }

        .report-page.page-break {
            page-break-after: always;
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

        .brand-mark {
            font-size: 24px;
            font-weight: 700;
            color: #7f1d1d;
            letter-spacing: 1px;
        }

        .brand-name {
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #4b5563;
            line-height: 1.2;
        }

        .crest {
            border: 1px solid #6b7280;
            border-radius: 50%;
            width: 42px;
            height: 42px;
            margin-left: auto;
            text-align: center;
            line-height: 42px;
            font-weight: 700;
            color: #7f1d1d;
            font-size: 8px;
        }

        .report-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .report-subtitle {
            font-size: 8px;
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
            padding: 2px 3px;
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
            font-size: 6px;
            text-align: center;
            line-height: 1.1;
        }

        .grades-table td {
            font-size: 6.4px;
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
            font-size: 8px;
            line-height: 1.25;
            padding: 6px 8px;
        }

        .signatures td {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-line {
            width: 62%;
            margin: 0 auto 4px;
            border-top: 1px solid #111827;
            height: 12px;
        }

        .seal {
            width: 54px;
            height: 54px;
            margin: 0 auto;
            border: 1px solid #9ca3af;
            border-radius: 50%;
            line-height: 54px;
            color: #6b7280;
            font-size: 7px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    @foreach ($reports as $report)
        <div class="report-page {{ ! $loop->last ? 'page-break' : '' }}">
            <table class="page-header">
                <tr>
                    <td class="brand-left">
                        <div class="brand-mark">ACI</div>
                        <div class="brand-name">ACADEMIA<br>CRISTIANA<br>INTERNACIONAL</div>
                    </td>
                    <td class="brand-center">
                        <div class="report-title">Academic Report {{ $report['student']['academic_year'] - 1 }} - {{ $report['student']['academic_year'] }}</div>
                        <div class="report-subtitle">{{ $report['student']['section_name'] }}</div>
                    </td>
                    <td class="brand-right">
                        <div class="crest">ACI</div>
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
                        <th colspan="4">First Term</th>
                        <th colspan="4">Second Term</th>
                        <th rowspan="2">Semestral Eval.</th>
                        <th colspan="4">Third Term</th>
                        <th colspan="4">Fourth Term</th>
                        <th rowspan="2">Final Eval.</th>
                        <th rowspan="2">Final Conduct</th>
                        <th rowspan="2">Final Period</th>
                    </tr>
                    <tr>
                        @for ($term = 1; $term <= 4; $term++)
                            <th>P1</th>
                            <th>P2</th>
                            <th>Avg</th>
                            <th>Cond</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['subjects'] as $subject)
                        <tr>
                            <td class="subject-col">{{ $subject['subject'] }}</td>
                            @for ($term = 1; $term <= 4; $term++)
                                @php($termData = $subject['terms'][$term] ?? null)
                                <td class="score {{ ($termData['progress_1'] ?? null) === null ? 'empty' : '' }}">{{ $termData['progress_1'] ?? '-' }}</td>
                                <td class="score {{ ($termData['progress_2'] ?? null) === null ? 'empty' : '' }}">{{ $termData['progress_2'] ?? '-' }}</td>
                                <td class="score {{ ($termData['period_average'] ?? null) === null ? 'empty' : '' }}">{{ $termData['period_average'] ?? '-' }}</td>
                                <td class="score {{ ($termData['conduct'] ?? null) === null ? 'empty' : '' }}">{{ $termData['conduct'] ?? '-' }}</td>
                                @if ($term === 2)
                                    <td class="score {{ $subject['semester_evaluation'] === null ? 'empty' : '' }}">{{ $subject['semester_evaluation'] ?? '-' }}</td>
                                @endif
                                @if ($term === 4)
                                    <td class="score {{ $subject['final_evaluation'] === null ? 'empty' : '' }}">{{ $subject['final_evaluation'] ?? '-' }}</td>
                                    <td class="score {{ $subject['final_conduct'] === null ? 'empty' : '' }}">{{ $subject['final_conduct'] ?? '-' }}</td>
                                    <td class="score {{ $subject['final_period'] === null ? 'empty' : '' }}">{{ $subject['final_period'] ?? '-' }}</td>
                                @endif
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="comment-box">
                <tr>
                    <td class="comment-label">Teacher's comment:</td>
                    <td class="comment-text">{{ $report['teacher_comment'] }}</td>
                </tr>
            </table>

            <table class="signatures">
                <tr>
                    <td>
                        <div class="signature-line"></div>
                        <strong>{{ $report['signatures']['headmaster'] }}</strong><br>
                        Headmaster
                    </td>
                    <td>
                        <div class="seal">SELLO</div>
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
