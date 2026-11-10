<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Services\TurnoService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CiudadanoController extends Controller
{
    public function __construct(protected TurnoService $turnoService)
    {
    }

    public function seguimiento(string $codigo)
    {
        $turno = Turno::with(['area', 'tramite', 'ventanilla'])
            ->where('codigo', $codigo)
            ->whereDate('fecha_emision', Carbon::today())
            ->firstOrFail();

        $personasAdelante = $this->turnoService->calcularPersonasAdelante($turno);

        return view('ciudadano.seguimiento', compact('turno', 'personasAdelante'));
    }

    public function estadoTurno(string $codigo): JsonResponse
    {
        $turno = Turno::with(['area', 'tramite', 'ventanilla'])
            ->where('codigo', $codigo)
            ->whereDate('fecha_emision', Carbon::today())
            ->firstOrFail();

        $personasAdelante = $this->turnoService->calcularPersonasAdelante($turno);

        return response()->json([
            'id' => $turno->id,
            'codigo' => $turno->codigo,
            'estado' => $turno->estado,
            'es_preferencial' => $turno->es_preferencial,
            'ciudadano_nombre' => $turno->ciudadano_nombre,
            'area_nombre' => $turno->area?->nombre,
            'area_color' => $turno->area?->color,
            'tramite_nombre' => $turno->tramite?->nombre,
            'ventanilla_numero' => $turno->ventanilla?->numero,
            'ventanilla_ubicacion' => $turno->ventanilla?->ubicacion,
            'personas_adelante' => $personasAdelante,
            'llamadas_contador' => $turno->llamadas_contador,
        ]);
    }

    public function formularioConsulta()
    {
        return view('ciudadano.consulta');
    }

    public function buscarTurno(Request $request)
    {
        $validated = $request->validate([
            'documento' => 'required|string|min:4|max:20',
        ]);

        $doc = trim($validated['documento']);

        $turno = Turno::whereDate('fecha_emision', Carbon::today())
            ->where('ciudadano_documento', $doc)
            ->whereIn('estado', ['espera', 'llamado', 'atendiendo'])
            ->latest('id')
            ->first();

        if (!$turno) {
            $turnoFinalizado = Turno::whereDate('fecha_emision', Carbon::today())
                ->where('ciudadano_documento', $doc)
                ->latest('id')
                ->first();

            if ($turnoFinalizado) {
                return redirect()->route('ciudadano.seguimiento', $turnoFinalizado->codigo);
            }

            return back()->with('error', 'No se encontró ningún turno registrado hoy para el documento ingresado.');
        }

        return redirect()->route('ciudadano.seguimiento', $turno->codigo);
    }
}

