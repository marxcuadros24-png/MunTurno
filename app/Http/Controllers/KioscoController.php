<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Turno;
use App\Services\TurnoService;
use Illuminate\Http\Request;

class KioscoController extends Controller
{
    public function __construct(protected TurnoService $turnoService)
    {
    }

    public function index()
    {
        $areas = Area::with(['tramites' => function ($query) {
            $query->where('activo', true);
        }])->where('activo', true)->get();

        return view('kiosco.index', compact('areas'));
    }

    public function emitir(Request $request)
    {
        $validated = $request->validate([
            'tramite_id' => 'required|exists:tramites,id',
            'ciudadano_tipo_documento' => 'required|in:DNI,CE,PASAPORTE,OTRO',
            'ciudadano_documento' => 'required|string|min:4|max:20',
            'ciudadano_nombre' => 'required|string|min:3|max:120',
            'ciudadano_telefono' => 'nullable|string|max:20',
            'ciudadano_email' => 'nullable|email|max:150',
            'es_preferencial' => 'nullable|boolean',
        ]);

        $turno = $this->turnoService->emitirTurno($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Turno generado exitosamente',
                'turno' => $turno,
                'personas_adelante' => $this->turnoService->calcularPersonasAdelante($turno),
                'redirect_url' => route('kiosco.ticket', $turno->id),
            ]);
        }

        return redirect()->route('kiosco.ticket', $turno->id);
    }

    public function ticket(Turno $turno)
    {
        $turno->load(['area', 'tramite']);
        $personasAdelante = $this->turnoService->calcularPersonasAdelante($turno);

        return view('kiosco.ticket', compact('turno', 'personasAdelante'));
    }
}

