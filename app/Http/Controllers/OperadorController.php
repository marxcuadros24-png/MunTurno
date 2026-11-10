<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Turno;
use App\Models\Ventanilla;
use App\Services\TurnoService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperadorController extends Controller
{
    public function __construct(protected TurnoService $turnoService)
    {
    }

    public function index(Request $request)
    {
        $ventanillas = Ventanilla::with(['area', 'operador'])->get();
        $ventanillaSeleccionada = null;

        if ($request->has('ventanilla_id')) {
            $ventanillaSeleccionada = Ventanilla::with(['area', 'operador'])->find($request->ventanilla_id);
        } elseif ($ventanillas->isNotEmpty()) {
            $ventanillaSeleccionada = $ventanillas->first();
        }

        $areas = Area::where('activo', true)->get();

        $hoy = Carbon::today();
        foreach ($ventanillas as $v) {
            $v->turnos_espera_count = Turno::whereDate('fecha_emision', $hoy)
                ->where('estado', 'espera')
                ->when($v->area_id, fn($q) => $q->where('area_id', $v->area_id))
                ->count();
        }

        return view('operador.index', compact('ventanillas', 'ventanillaSeleccionada', 'areas'));
    }

    public function colaActual(Request $request, Ventanilla $ventanilla): JsonResponse
    {
        $hoy = Carbon::today();
        $todasLasAreas = $request->boolean('todas_las_areas', false);

        // Turno en curso en esta ventanilla
        $turnoActual = Turno::with(['area', 'tramite'])
            ->whereDate('fecha_emision', $hoy)
            ->where('ventanilla_id', $ventanilla->id)
            ->whereIn('estado', ['llamado', 'atendiendo'])
            ->latest('updated_at')
            ->first();

        // Cola de espera asignable
        $query = Turno::with(['area', 'tramite'])
            ->whereDate('fecha_emision', $hoy)
            ->where('estado', 'espera');

        if (!$todasLasAreas && $ventanilla->area_id) {
            $query->where('area_id', $ventanilla->area_id);
        }

        $colaEspera = $query->orderByDesc('es_preferencial')
            ->orderBy('id', 'asc')
            ->get();

        // Conteos informativos globales y por área
        $totalGlobalEspera = Turno::whereDate('fecha_emision', $hoy)
            ->where('estado', 'espera')
            ->count();

        $totalMiAreaEspera = $ventanilla->area_id
            ? Turno::whereDate('fecha_emision', $hoy)->where('estado', 'espera')->where('area_id', $ventanilla->area_id)->count()
            : $totalGlobalEspera;

        // Últimos atendidos hoy por esta ventanilla
        $atendidosHoy = Turno::with(['tramite'])
            ->whereDate('fecha_emision', $hoy)
            ->where('ventanilla_id', $ventanilla->id)
            ->whereIn('estado', ['finalizado', 'ausente'])
            ->latest('fecha_fin_atencion')
            ->limit(5)
            ->get();

        return response()->json([
            'ventanilla' => $ventanilla->load('area'),
            'turno_actual' => $turnoActual,
            'cola_espera' => $colaEspera,
            'total_espera' => $colaEspera->count(),
            'total_mi_area' => $totalMiAreaEspera,
            'total_global' => $totalGlobalEspera,
            'atendidos_hoy' => $atendidosHoy,
            'total_atendidos' => Turno::whereDate('fecha_emision', $hoy)->where('ventanilla_id', $ventanilla->id)->where('estado', 'finalizado')->count(),
        ]);
    }

    public function llamarSiguiente(Request $request, Ventanilla $ventanilla): JsonResponse
    {
        $todasLasAreas = $request->boolean('todas_las_areas', false);
        $turno = $this->turnoService->llamarSiguiente($ventanilla, auth()->id(), $todasLasAreas);

        if (!$turno) {
            return response()->json([
                'success' => false,
                'message' => 'No hay ciudadanos en espera en la cola.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => "Turno {$turno->codigo} llamado con éxito.",
            'turno' => $turno,
        ]);
    }

    public function llamarEspecifico(Ventanilla $ventanilla, Turno $turno): JsonResponse
    {
        $turnoActualizado = $this->turnoService->llamarTurnoEspecifico($turno, $ventanilla, auth()->id());

        return response()->json([
            'success' => true,
            'message' => "Turno {$turnoActualizado->codigo} llamado a la {$ventanilla->numero}.",
            'turno' => $turnoActualizado,
        ]);
    }

    public function rellamar(Turno $turno): JsonResponse
    {
        $this->turnoService->rellamarTurno($turno, auth()->id());

        return response()->json([
            'success' => true,
            'message' => "Turno {$turno->codigo} vuelto a llamar.",
            'turno' => $turno,
        ]);
    }

    public function iniciarAtencion(Turno $turno): JsonResponse
    {
        $this->turnoService->iniciarAtencion($turno);

        return response()->json([
            'success' => true,
            'message' => "Atención iniciada para el turno {$turno->codigo}.",
            'turno' => $turno,
        ]);
    }

    public function finalizarAtencion(Request $request, Turno $turno): JsonResponse
    {
        $observaciones = $request->input('observaciones');
        $this->turnoService->finalizarAtencion($turno, $observaciones);

        return response()->json([
            'success' => true,
            'message' => "Atención del turno {$turno->codigo} finalizada.",
            'turno' => $turno,
        ]);
    }

    public function marcarAusente(Turno $turno): JsonResponse
    {
        $this->turnoService->marcarAusente($turno);

        return response()->json([
            'success' => true,
            'message' => "Turno {$turno->codigo} marcado como ausente.",
            'turno' => $turno,
        ]);
    }

    public function transferir(Request $request, Turno $turno): JsonResponse
    {
        $validated = $request->validate([
            'nueva_area_id' => 'required|exists:areas,id',
            'motivo' => 'nullable|string|max:255',
        ]);

        $this->turnoService->transferirTurno(
            $turno,
            (int) $validated['nueva_area_id'],
            null,
            $validated['motivo'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => "Turno {$turno->codigo} derivado a nueva área con prioridad.",
            'turno' => $turno,
        ]);
    }
}

