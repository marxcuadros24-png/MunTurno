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
}

