<?php

namespace App\Events;

use App\Models\Turno;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TurnoLlamado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Payload con todos los datos necesarios para la TV y el Ciudadano
     */
    public array $data;

    public function __construct(public Turno $turno)
    {
        $this->turno->loadMissing(['area', 'tramite', 'ventanilla', 'operador']);

        $this->data = [
            'id' => $this->turno->id,
            'codigo' => $this->turno->codigo,
            'ciudadano_nombre' => $this->turno->ciudadano_nombre,
            'ciudadano_documento' => $this->turno->ciudadano_documento,
            'es_preferencial' => (bool) $this->turno->es_preferencial,
            'estado' => $this->turno->estado,
            'llamadas_contador' => $this->turno->llamadas_contador,
            'ventanilla' => [
                'id' => $this->turno->ventanilla?->id,
                'numero' => $this->turno->ventanilla?->numero ?? 'Ventanilla',
                'ubicacion' => $this->turno->ventanilla?->ubicacion ?? 'Sala de Atención',
            ],
            'area' => [
                'id' => $this->turno->area?->id,
                'nombre' => $this->turno->area?->nombre,
                'codigo' => $this->turno->area?->codigo,
                'color' => $this->turno->area?->color,
            ],
            'tramite' => [
                'id' => $this->turno->tramite?->id,
                'nombre' => $this->turno->tramite?->nombre,
            ],
            'hora_llamado' => now()->format('H:i:s'),
            'timestamp' => now()->timestamp,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('cola.publica'),
            new Channel('turno.' . $this->turno->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'turno.llamado';
    }
}

