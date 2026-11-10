<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\Ventanilla;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class PantallaTvController extends Controller
{
    public function index()
    {
        return view('pantalla.tv');
    }

    /**
     * Devuelve el estado actual para la carga inicial de la pantalla de TV
     */
    public function estadoActual(): JsonResponse
    {
        $hoy = Carbon::today();

        // Último turno llamado prioritariamente
        $ultimoLlamado = Turno::with(['area', 'tramite', 'ventanilla'])
            ->whereDate('fecha_emision', $hoy)
            ->where('estado', 'llamado')
            ->orderByDesc('updated_at')
            ->first();

        // Si no hay ninguno en 'llamado', tomamos el más reciente en atención como referencia secundaria
        if (!$ultimoLlamado) {
            $ultimoLlamado = Turno::with(['area', 'tramite', 'ventanilla'])
                ->whereDate('fecha_emision', $hoy)
                ->where('estado', 'atendiendo')
                ->orderByDesc('updated_at')
                ->first();
        }

        // Todos los turnos actualmente en atención en ventanillas
        $turnosEnAtencion = Turno::with(['area', 'tramite', 'ventanilla', 'operador'])
            ->whereDate('fecha_emision', $hoy)
            ->where('estado', 'atendiendo')
            ->orderByDesc('fecha_inicio_atencion')
            ->get();

        // Historial de los últimos 6 turnos llamados
        $historialLlamados = Turno::with(['area', 'ventanilla'])
            ->whereDate('fecha_emision', $hoy)
            ->whereIn('estado', ['llamado', 'atendiendo', 'finalizado'])
            ->whereNotNull('fecha_primer_llamado')
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        // Próximos turnos en espera
        $proximosEnEspera = Turno::with('area')
            ->whereDate('fecha_emision', $hoy)
            ->where('estado', 'espera')
            ->orderByDesc('es_preferencial')
            ->orderBy('id', 'asc')
            ->limit(8)
            ->get();

        // Estado de ventanillas activas
        $ventanillas = Ventanilla::with(['area', 'operador'])
            ->where('estado', '!=', 'inactiva')
            ->get();

        return response()->json([
            'ultimo_llamado' => $ultimoLlamado,
            'turnos_en_atencion' => $turnosEnAtencion,
            'historial' => $historialLlamados,
            'en_espera' => $proximosEnEspera,
            'total_espera' => Turno::whereDate('fecha_emision', $hoy)->where('estado', 'espera')->count(),
            'ventanillas' => $ventanillas,
            'hora' => now()->format('H:i:s'),
        ]);
    }
}

