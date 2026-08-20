<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletín de Calificaciones - {{ $student->name }} - {{ $academicPeriod->name }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #212529;
            background-color: #f8f9fa;
        }
        .print-container {
            max-width: 900px;
            margin: 20px auto;
            background: #ffffff;
            padding: 40px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-radius: 8px;
        }
        .table-print th {
            background-color: #f1f3f5 !important;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
            }
            .print-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>

<div class="no-print bg-dark text-white p-3 mb-4 shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-printer me-2"></i> <strong>Vista previa de impresión de calificaciones</strong>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary btn-sm me-2">
                <i class="bi bi-printer-fill me-1"></i> Imprimir Boletín
            </button>
            <button onclick="window.close()" class="btn btn-outline-light btn-sm">
                Cerrar Ventana
            </button>
        </div>
    </div>
</div>

<div class="container print-container">
    <!-- Header -->
    <div class="row border-bottom pb-4 mb-4 align-items-center">
        <div class="col-8">
            <h2 class="fw-bold text-uppercase mb-1" style="color: #1e3c72;">Sistema de Calificaciones</h2>
            <h5 class="text-secondary mb-0">Boletín Oficial de Calificaciones Estudiantiles</h5>
        </div>
        <div class="col-4 text-end">
            <span class="badge bg-light text-dark border p-2 d-block mb-1">
                Fecha de Emisión: {{ $generatedAt->format('d/m/Y H:i') }}
            </span>
            <span class="badge {{ $summary['enrollment']?->active ? 'bg-success' : 'bg-secondary' }} p-1 text-uppercase">
                Matrícula {{ $summary['enrollment']?->active ? 'Activa' : 'Histórica' }}
            </span>
        </div>
    </div>

    <!-- Student Metadata Grid -->
    <div class="row bg-light rounded-3 p-3 mb-4 border">
        <div class="col-md-6 mb-2 mb-md-0">
            <span class="text-muted text-uppercase fs-8 fw-bold d-block">Estudiante:</span>
            <strong class="fs-5 text-dark">{{ $student->name }}</strong>
            <small class="text-muted d-block">{{ $student->email }}</small>
        </div>
        <div class="col-md-6 text-md-end">
            <span class="text-muted text-uppercase fs-8 fw-bold d-block">Curso & Período:</span>
            <strong class="fs-6 text-dark">{{ $summary['course']?->name }} ({{ $summary['course']?->code }})</strong>
            <span class="d-block text-secondary fw-semibold">{{ $academicPeriod->name }}</span>
        </div>
    </div>

    <!-- General Average Summary Banner -->
    <div class="border rounded-3 p-3 mb-4 d-flex justify-content-between align-items-center bg-white">
        <div>
            <h6 class="fw-bold text-uppercase mb-0 text-dark">Promedio General Oficial del Período:</h6>
            <small class="text-muted">Calculado sobre la totalidad de asignaturas con P1 y P2 publicados.</small>
        </div>
        <div class="text-end">
            @if($summary['is_general_official'] && $summary['general_result'])
                <h3 class="fw-bold text-success mb-0">{{ $summary['general_result']['score_formatted'] }} / 10,00</h3>
                <span class="badge bg-success">CONFIRMADO</span>
            @else
                <h5 class="fw-bold text-secondary mb-0">Promedio general pendiente</h5>
                <small class="text-muted">Asignaturas pendientes de publicación</small>
            @endif
        </div>
    </div>

    <!-- Subjects Summary Table -->
    <h5 class="fw-bold text-dark mb-3">1. Resumen Consolidado de Asignaturas</h5>
    <table class="table table-bordered table-print align-middle mb-4">
        <thead>
            <tr class="text-center">
                <th class="text-start">Asignatura</th>
                <th>Docente</th>
                <th>Parcial 1</th>
                <th>Parcial 2</th>
                <th>Promedio Final</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['subjects'] as $item)
                <tr>
                    <td class="fw-bold text-dark">
                        {{ $item['subject']?->name }}
                        <small class="text-muted d-block">Código: {{ $item['subject']?->code }}</small>
                    </td>
                    <td>{{ $item['teacher']?->name ?? 'Sin asignar' }}</td>
                    <td class="text-center">
                        @if($item['p1_published'] && $item['p1_result'])
                            <strong class="text-dark">{{ $item['p1_result']['score_formatted'] }}</strong>
                        @else
                            <span class="text-muted fs-8">{{ $item['p1_status_label'] }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($item['p2_published'] && $item['p2_result'])
                            <strong class="text-dark">{{ $item['p2_result']['score_formatted'] }}</strong>
                        @else
                            <span class="text-muted fs-8">{{ $item['p2_status_label'] }}</span>
                        @endif
                    </td>
                    <td class="text-center fw-bold fs-6">
                        @if($item['final_result'])
                            <span class="text-primary">{{ $item['final_result']['score_formatted'] }}</span>
                        @else
                            <span class="text-muted fs-8">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Detailed Published Activities Breakdown -->
    <h5 class="fw-bold text-dark mb-3">2. Detalle de Actividades y Observaciones Publicadas</h5>

    @foreach($detailedSubjects as $item)
        <div class="border rounded-3 p-3 mb-4 bg-white">
            <h6 class="fw-bold text-primary mb-2 border-bottom pb-2">
                {{ $item['subject']?->name }} (Docente: {{ $item['teacher']?->name ?? 'Sin asignar' }})
            </h6>

            <!-- P1 Detail -->
            <div class="mb-3">
                <div class="fw-semibold text-dark fs-7 mb-1">
                    Parcial 1: 
                    @if($item['p1_published'] && isset($item['detail']['p1_detail']))
                        <span class="text-success fw-bold me-2">Publicado — Promedio: {{ $item['detail']['p1_detail']['partial_result']['score_formatted'] ?? '0.00' }} / 10,00</span>
                    @else
                        <span class="text-muted">{{ $item['p1_status_label'] }}</span>
                    @endif
                </div>

                @if($item['p1_published'] && !empty($item['detail']['p1_detail']['activities']))
                    <table class="table table-sm table-bordered fs-8 mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Actividad</th>
                                <th class="text-center">Porcentaje</th>
                                <th class="text-center">Nota</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item['detail']['p1_detail']['activities'] as $act)
                                <tr>
                                    <td>{{ $act['name'] }}</td>
                                    <td class="text-center">{{ number_format((float)$act['percentage'], 2) }} %</td>
                                    <td class="text-center fw-bold">{{ number_format((float)$act['score'], 2) }} / 10,00</td>
                                    <td>{{ $act['observation'] ?: 'Sin observaciones' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- P2 Detail -->
            <div>
                <div class="fw-semibold text-dark fs-7 mb-1">
                    Parcial 2: 
                    @if($item['p2_published'] && isset($item['detail']['p2_detail']))
                        <span class="text-success fw-bold me-2">Publicado — Promedio: {{ $item['detail']['p2_detail']['partial_result']['score_formatted'] ?? '0.00' }} / 10,00</span>
                    @else
                        <span class="text-muted">{{ $item['p2_status_label'] }}</span>
                    @endif
                </div>

                @if($item['p2_published'] && !empty($item['detail']['p2_detail']['activities']))
                    <table class="table table-sm table-bordered fs-8 mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Actividad</th>
                                <th class="text-center">Porcentaje</th>
                                <th class="text-center">Nota</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item['detail']['p2_detail']['activities'] as $act)
                                <tr>
                                    <td>{{ $act['name'] }}</td>
                                    <td class="text-center">{{ number_format((float)$act['percentage'], 2) }} %</td>
                                    <td class="text-center fw-bold">{{ number_format((float)$act['score'], 2) }} / 10,00</td>
                                    <td>{{ $act['observation'] ?: 'Sin observaciones' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endforeach

    <!-- Footer Signature Space -->
    <div class="row pt-5 mt-5 border-top text-center text-muted fs-8">
        <div class="col-6">
            <div class="border-top border-dark w-75 mx-auto pt-2">
                Firma del Estudiante
            </div>
        </div>
        <div class="col-6">
            <div class="border-top border-dark w-75 mx-auto pt-2">
                Sello Institucional / Secretaría Académica
            </div>
        </div>
    </div>
</div>

</body>
</html>
