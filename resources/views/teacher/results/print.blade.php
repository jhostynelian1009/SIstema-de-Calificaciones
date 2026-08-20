<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Calificaciones - {{ $assignment->subject?->name }} ({{ $assignment->course?->code }})</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #000;
            background-color: #fff;
        }
        .printable-header {
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .table-print {
            width: 100%;
            border-collapse: collapse;
        }
        .table-print th, .table-print td {
            border: 1px solid #333;
            padding: 6px 10px;
        }
        .table-print th {
            background-color: #f0f0f0 !important;
            text-transform: uppercase;
            font-size: 10px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body class="p-4">
    <div class="no-print mb-3 text-end">
        <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold">
            <i class="bi bi-printer"></i> Imprimir / Guardar PDF
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm me-2">Cerrar Ventana</button>
    </div>

    <div class="printable-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">SISTEMA DE CALIFICACIONES ACADÉMICAS</h4>
            <h6 class="text-uppercase fw-bold text-secondary mb-0">Acta Consolidada de Resultados</h6>
        </div>
        <div class="text-end fs-8">
            <div><strong>Fecha de Emisión:</strong> {{ $generated_at->format('d/m/Y H:i:s') }}</div>
            <div><strong>Docente Responsable:</strong> {{ $assignment->teacher?->name }}</div>
        </div>
    </div>

    <!-- Metadata Grid -->
    <div class="row g-2 mb-4 fs-7 border p-3 bg-light rounded-2">
        <div class="col-4">
            <strong>Curso:</strong> {{ $assignment->course?->name }} ({{ $assignment->course?->code }})
        </div>
        <div class="col-4">
            <strong>Asignatura:</strong> {{ $assignment->subject?->name }} ({{ $assignment->subject?->code }})
        </div>
        <div class="col-4">
            <strong>Período Académico:</strong> {{ $assignment->academicPeriod?->name }}
        </div>
        <div class="col-6">
            <strong>Parcial 1 (50%):</strong> {{ $p1_official ? 'OFICIAL (Publicado)' : 'PROVISIONAL (Borrador/En progreso)' }}
        </div>
        <div class="col-6">
            <strong>Parcial 2 (50%):</strong> {{ $p2_official ? 'OFICIAL (Publicado)' : 'PROVISIONAL (Borrador/En progreso)' }}
        </div>
    </div>

    <table class="table-print fs-7 mb-4">
        <thead>
            <tr>
                <th style="width: 40px;" class="text-center">#</th>
                <th>Estudiante</th>
                <th style="width: 130px;" class="text-center">Parcial 1 (50%)</th>
                <th style="width: 130px;" class="text-center">Parcial 2 (50%)</th>
                <th style="width: 140px;" class="text-center">Promedio Final</th>
                <th style="width: 130px;" class="text-center">Estado Acta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
                <tr>
                    <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                    <td>
                        <div class="fw-bold">{{ $row['student']->name }}</div>
                        <small class="text-muted">{{ $row['student']->email }}</small>
                    </td>
                    <td class="text-center">
                        @if($row['p1_calc'] && $row['p1_calc']['calculable'])
                            <strong>{{ $row['p1_calc']['score_formatted'] }}</strong>
                            <small class="d-block text-muted">({{ $row['p1_official'] ? 'Oficial' : 'Prov.' }})</small>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($row['p2_calc'] && $row['p2_calc']['calculable'])
                            <strong>{{ $row['p2_calc']['score_formatted'] }}</strong>
                            <small class="d-block text-muted">({{ $row['p2_official'] ? 'Oficial' : 'Prov.' }})</small>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($row['final_calc']['calculable'])
                            <strong class="fs-6">{{ $row['final_calc']['score_formatted'] }}</strong>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center fw-bold">
                        @if($row['is_final_official'])
                            OFICIAL
                        @elseif($row['final_calc']['calculable'])
                            PROVISIONAL
                        @else
                            INCOMPLETO
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="row mt-5 pt-4 text-center fs-7">
        <div class="col-6 mx-auto">
            <div class="border-top border-dark pt-2">
                <strong>{{ $assignment->teacher?->name }}</strong><br>
                Firma del Docente Responsable
            </div>
        </div>
    </div>
</body>
</html>
