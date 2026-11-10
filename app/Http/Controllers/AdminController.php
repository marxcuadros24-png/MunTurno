<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Turno;
use App\Models\Ventanilla;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();

        $totalEmitidos = Turno::whereDate('fecha_emision', $hoy)->count();
        $totalAtendidos = Turno::whereDate('fecha_emision', $hoy)->where('estado', 'finalizado')->count();
        $totalEspera = Turno::whereDate('fecha_emision', $hoy)->where('estado', 'espera')->count();
        $totalAusentes = Turno::whereDate('fecha_emision', $hoy)->where('estado', 'ausente')->count();

        // Tiempos promedio en minutos
        $promedioEsperaSegundos = Turno::whereDate('fecha_emision', $hoy)
            ->whereNotNull('tiempo_espera_segundos')
            ->avg('tiempo_espera_segundos') ?? 0;

        $promedioAtencionSegundos = Turno::whereDate('fecha_emision', $hoy)
            ->whereNotNull('tiempo_atencion_segundos')
            ->avg('tiempo_atencion_segundos') ?? 0;

        $promedioEsperaMinutos = round($promedioEsperaSegundos / 60, 1);
        $promedioAtencionMinutos = round($promedioAtencionSegundos / 60, 1);

        $areas = Area::withCount([
            'turnos as turnos_hoy_count' => function ($query) use ($hoy) {
                $query->whereDate('fecha_emision', $hoy);
            },
            'turnos as atendidos_hoy_count' => function ($query) use ($hoy) {
                $query->whereDate('fecha_emision', $hoy)->where('estado', 'finalizado');
            },
        ])->get();

        $ventanillas = Ventanilla::with(['area', 'operador'])
            ->withCount([
                'turnos as atendidos_count' => function ($query) use ($hoy) {
                    $query->whereDate('fecha_emision', $hoy)->where('estado', 'finalizado');
                },
            ])->get();

        $ultimosTurnos = Turno::with(['area', 'tramite', 'ventanilla'])
            ->whereDate('fecha_emision', $hoy)
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return view('admin.index', compact(
            'totalEmitidos',
            'totalAtendidos',
            'totalEspera',
            'totalAusentes',
            'promedioEsperaMinutos',
            'promedioAtencionMinutos',
            'areas',
            'ventanillas',
            'ultimosTurnos'
        ));
    }

    public function metricas(): JsonResponse
    {
        $hoy = Carbon::today();

        $totalEmitidos = Turno::whereDate('fecha_emision', $hoy)->count();
        $totalAtendidos = Turno::whereDate('fecha_emision', $hoy)->where('estado', 'finalizado')->count();
        $totalEspera = Turno::whereDate('fecha_emision', $hoy)->where('estado', 'espera')->count();
        $totalAusentes = Turno::whereDate('fecha_emision', $hoy)->where('estado', 'ausente')->count();

        $promedioEsperaSeg = Turno::whereDate('fecha_emision', $hoy)
            ->whereNotNull('tiempo_espera_segundos')
            ->avg('tiempo_espera_segundos') ?? 0;

        $promedioAtencionSeg = Turno::whereDate('fecha_emision', $hoy)
            ->whereNotNull('tiempo_atencion_segundos')
            ->avg('tiempo_atencion_segundos') ?? 0;

        return response()->json([
            'total_emitidos' => $totalEmitidos,
            'total_atendidos' => $totalAtendidos,
            'total_espera' => $totalEspera,
            'total_ausentes' => $totalAusentes,
            'promedio_espera_min' => round($promedioEsperaSeg / 60, 1),
            'promedio_atencion_min' => round($promedioAtencionSeg / 60, 1),
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    public function exportarCsv()
    {
        $hoy = Carbon::today();
        $turnos = Turno::with(['area', 'tramite', 'ventanilla', 'operador'])
            ->whereDate('fecha_emision', $hoy)
            ->orderBy('id', 'asc')
            ->get();

        $filename = 'munturno_reporte_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($turnos) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            fputcsv($file, [
                'ID',
                'Código',
                'Prioridad',
                'Ciudadano',
                'Tipo Doc',
                'Documento',
                'Área',
                'Trámite',
                'Ventanilla',
                'Operador',
                'Estado',
                'Llamados',
                'Hora Emisión',
                'Hora 1er Llamado',
                'Hora Fin Atención',
                'T. Espera (min)',
                'T. Atención (min)',
                'Observaciones',
            ]);

            foreach ($turnos as $t) {
                fputcsv($file, [
                    $t->id,
                    $t->codigo,
                    $t->es_preferencial ? 'Preferencial' : 'Regular',
                    $t->ciudadano_nombre,
                    $t->ciudadano_tipo_documento,
                    $t->ciudadano_documento,
                    $t->area?->nombre ?? '',
                    $t->tramite?->nombre ?? '',
                    $t->ventanilla?->numero ?? 'Sin asignar',
                    $t->operador?->name ?? 'Sin asignar',
                    strtoupper($t->estado),
                    $t->llamadas_contador,
                    $t->fecha_emision ? $t->fecha_emision->format('H:i:s') : '',
                    $t->fecha_primer_llamado ? $t->fecha_primer_llamado->format('H:i:s') : '',
                    $t->fecha_fin_atencion ? $t->fecha_fin_atencion->format('H:i:s') : '',
                    $t->tiempo_espera_segundos ? round($t->tiempo_espera_segundos / 60, 1) : '',
                    $t->tiempo_atencion_segundos ? round($t->tiempo_atencion_segundos / 60, 1) : '',
                    $t->observaciones ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

