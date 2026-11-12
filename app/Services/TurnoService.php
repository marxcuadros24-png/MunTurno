<?php

namespace App\Services;

use App\Events\TurnoActualizado;
use App\Events\TurnoCreado;
use App\Events\TurnoLlamado;
use App\Models\Area;
use App\Models\LlamadoHistorial;
use App\Models\Tramite;
use App\Models\Turno;
use App\Models\Ventanilla;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TurnoService
{
    /**
     * Emite un evento de broadcasting sin interrumpir el flujo ni revertir transacciones
     * si el servidor WebSocket (Reverb) no se encuentra activo temporalmente.
     */
    protected function safeBroadcast(object $event): void
    {
        try {
            broadcast($event);
        } catch (Throwable $e) {
            Log::warning('WebSocket broadcast omitido: ' . $e->getMessage());
        }
    }

    /**
     * Genera un nuevo turno para el ciudadano con código atómico correlativo por día y área.
     */
    public function emitirTurno(array $datos): Turno
    {
        $turno = DB::transaction(function () use ($datos) {
            $tramite = Tramite::with('area')->findOrFail($datos['tramite_id']);
            $area = $tramite->area;
            $esPreferencial = !empty($datos['es_preferencial']) || $tramite->es_preferencial;

            // Obtener el correlativo secuencial del día para el área/preferencial
            $hoy = Carbon::today();
            $prefijo = $esPreferencial ? 'P' : $area->codigo;

            // Bloqueamos el último correlativo para evitar condiciones de carrera (Race Conditions)
            $ultimoTurno = Turno::whereDate('fecha_emision', $hoy)
                ->where('area_id', $area->id)
                ->where('es_preferencial', $esPreferencial)
                ->lockForUpdate()
                ->orderByDesc('numero_secuencia')
                ->first();

            $siguienteSecuencia = $ultimoTurno ? ($ultimoTurno->numero_secuencia + 1) : 1;
            $codigo = sprintf('%s-%03d', $prefijo, $siguienteSecuencia);

            $turno = Turno::create([
                'codigo' => $codigo,
                'numero_secuencia' => $siguienteSecuencia,
                'area_id' => $area->id,
                'tramite_id' => $tramite->id,
                'ciudadano_tipo_documento' => $datos['ciudadano_tipo_documento'] ?? 'DNI',
                'ciudadano_documento' => trim($datos['ciudadano_documento']),
                'ciudadano_nombre' => trim($datos['ciudadano_nombre']),
                'ciudadano_telefono' => $datos['ciudadano_telefono'] ?? null,
                'ciudadano_email' => $datos['ciudadano_email'] ?? null,
                'es_preferencial' => $esPreferencial,
                'estado' => 'espera',
                'fecha_emision' => now(),
            ]);

            return $turno->load(['area', 'tramite']);
        });

        // Disparamos evento en tiempo real vía WebSockets una vez confirmada la transacción
        $this->safeBroadcast(new TurnoCreado($turno));

        return $turno;
    }

    /**
     * Llama al siguiente turno en cola para la ventanilla.
     * Prioriza turnos preferenciales según ley de atención inclusiva.
     */
    public function llamarSiguiente(Ventanilla $ventanilla, ?int $operadorId = null, bool $todasLasAreas = false): ?Turno
    {
        $turno = DB::transaction(function () use ($ventanilla, $operadorId, $todasLasAreas) {
            $hoy = Carbon::today();

            // Buscar en la cola de turnos en espera para el área de la ventanilla (o cualquier área si es universal)
            $query = Turno::whereDate('fecha_emision', $hoy)
                ->where('estado', 'espera');

            if (!$todasLasAreas && $ventanilla->area_id) {
                $query->where('area_id', $ventanilla->area_id);
            }

            // Prioridad: 1° Preferenciales más antiguos, 2° Regulares más antiguos
            $turno = $query->orderByDesc('es_preferencial')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$turno) {
                return null;
            }

            $ahora = now();
            $tiempoEspera = $turno->fecha_emision ? max(0, (int) abs($ahora->diffInSeconds($turno->fecha_emision))) : 0;

            $turno->update([
                'ventanilla_id' => $ventanilla->id,
                'user_id' => $operadorId ?? $ventanilla->user_id,
                'estado' => 'llamado',
                'llamadas_contador' => 1,
                'fecha_primer_llamado' => $ahora,
                'tiempo_espera_segundos' => $tiempoEspera,
            ]);

            $ventanilla->update(['estado' => 'atendiendo']);

            // Registrar en historial de llamados
            LlamadoHistorial::create([
                'turno_id' => $turno->id,
                'ventanilla_id' => $ventanilla->id,
                'user_id' => $operadorId ?? $ventanilla->user_id,
                'llamado_at' => $ahora,
            ]);

            return $turno->load(['area', 'tramite', 'ventanilla', 'operador']);
        });

        if ($turno) {
            // Disparar WebSocket a pantalla de TV y cliente ya con el commit persistido en MySQL
            $this->safeBroadcast(new TurnoLlamado($turno));
        }

        return $turno;
    }

    /**
     * Llama a un turno específico seleccionado manualmente por el operador.
     */
    public function llamarTurnoEspecifico(Turno $turno, Ventanilla $ventanilla, ?int $operadorId = null): Turno
    {
        $turno = DB::transaction(function () use ($turno, $ventanilla, $operadorId) {
            $ahora = now();
            $tiempoEspera = $turno->fecha_emision ? max(0, (int) abs($ahora->diffInSeconds($turno->fecha_emision))) : 0;

            $turno->update([
                'ventanilla_id' => $ventanilla->id,
                'user_id' => $operadorId ?? $ventanilla->user_id,
                'estado' => 'llamado',
                'llamadas_contador' => ($turno->llamadas_contador ?? 0) + 1,
                'fecha_primer_llamado' => $turno->fecha_primer_llamado ?? $ahora,
                'tiempo_espera_segundos' => $tiempoEspera,
            ]);

            $ventanilla->update(['estado' => 'atendiendo']);

            LlamadoHistorial::create([
                'turno_id' => $turno->id,
                'ventanilla_id' => $ventanilla->id,
                'user_id' => $operadorId ?? $ventanilla->user_id,
                'llamado_at' => $ahora,
            ]);

            return $turno->load(['area', 'tramite', 'ventanilla', 'operador']);
        });

        $this->safeBroadcast(new TurnoLlamado($turno));

        return $turno;
    }

    /**
     * Vuelve a llamar al turno actual en la pantalla y móvil.
     */
    public function rellamarTurno(Turno $turno, ?int $operadorId = null): Turno
    {
        $turno->increment('llamadas_contador');
        $turno->refresh();

        if ($turno->ventanilla_id) {
            LlamadoHistorial::create([
                'turno_id' => $turno->id,
                'ventanilla_id' => $turno->ventanilla_id,
                'user_id' => $operadorId ?? $turno->user_id,
                'llamado_at' => now(),
            ]);
        }

        $turno->load(['area', 'tramite', 'ventanilla', 'operador']);

        $this->safeBroadcast(new TurnoLlamado($turno));

        return $turno;
    }

    /**
     * Inicia formalmente la atención en ventanilla (cuando el ciudadano toma asiento).
     */
    public function iniciarAtencion(Turno $turno): Turno
    {
        $turno->update([
            'estado' => 'atendiendo',
            'fecha_inicio_atencion' => now(),
        ]);

        if ($turno->ventanilla) {
            $turno->ventanilla->update(['estado' => 'atendiendo']);
        }

        $this->safeBroadcast(new TurnoActualizado($turno));

        return $turno;
    }

    /**
     * Finaliza la atención del turno y registra las métricas de tiempo.
     */
    public function finalizarAtencion(Turno $turno, ?string $observaciones = null): Turno
    {
        $ahora = now();
        $inicio = $turno->fecha_inicio_atencion ?? $turno->fecha_primer_llamado ?? $ahora;
        $tiempoAtencion = max(0, (int) abs($ahora->diffInSeconds($inicio)));

        $turno->update([
            'estado' => 'finalizado',
            'fecha_fin_atencion' => $ahora,
            'tiempo_atencion_segundos' => $tiempoAtencion,
            'observaciones' => $observaciones,
        ]);

        if ($turno->ventanilla) {
            $turno->ventanilla->update(['estado' => 'disponible']);
        }

        $this->safeBroadcast(new TurnoActualizado($turno));

        return $turno;
    }

    /**
     * Marca al ciudadano como ausente tras reiterados llamados sin presentarse.
     */
    public function marcarAusente(Turno $turno): Turno
    {
        $turno->update([
            'estado' => 'ausente',
            'fecha_fin_atencion' => now(),
        ]);

        if ($turno->ventanilla) {
            $turno->ventanilla->update(['estado' => 'disponible']);
        }

        $this->safeBroadcast(new TurnoActualizado($turno));

        return $turno;
    }

    /**
     * Deriva / transfiere un turno a otra área (ej. de Trámites a Caja/Rentas).
     * Se coloca con prioridad preferencial para que el ciudadano no espere desde cero.
     */
    public function transferirTurno(Turno $turno, int $nuevaAreaId, ?int $nuevoTramiteId = null, ?string $motivo = null): Turno
    {
        $nuevaArea = Area::findOrFail($nuevaAreaId);
        $tramiteId = $nuevoTramiteId ?? $nuevaArea->tramites()->first()?->id ?? $turno->tramite_id;

        $nota = "[Derivado a {$nuevaArea->nombre}]" . ($motivo ? ": $motivo" : '');
        $observaciones = trim(($turno->observaciones ? $turno->observaciones . ' | ' : '') . $nota);

        if ($turno->ventanilla) {
            $turno->ventanilla->update(['estado' => 'disponible']);
        }

        $turno->update([
            'area_id' => $nuevaArea->id,
            'tramite_id' => $tramiteId,
            'ventanilla_id' => null,
            'user_id' => null,
            'estado' => 'espera',
            'es_preferencial' => true,
            'llamadas_contador' => 0,
            'fecha_primer_llamado' => null,
            'fecha_inicio_atencion' => null,
            'observaciones' => $observaciones,
        ]);

        $turno->load(['area', 'tramite']);

        $this->safeBroadcast(new TurnoActualizado($turno));
        $this->safeBroadcast(new TurnoCreado($turno));

        return $turno;
    }

    /**
     * Calcula la posición y turnos por delante para un turno en espera.
     */
    public function calcularPersonasAdelante(Turno $turno): int
    {
        if ($turno->estado !== 'espera') {
            return 0;
        }

        $hoy = Carbon::today();

        return Turno::whereDate('fecha_emision', $hoy)
            ->where('area_id', $turno->area_id)
            ->where('estado', 'espera')
            ->where('id', '<', $turno->id)
            ->count();
    }
}
