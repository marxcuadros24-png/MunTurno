<?php

namespace App\Events;

use App\Models\Turno;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TurnoCreado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;

    public function __construct(public Turno $turno)
    {
        $this->turno->loadMissing(['area', 'tramite']);

        $this->data = [
            'id' => $this->turno->id,
            'codigo' => $this->turno->codigo,
            'ciudadano_nombre' => $this->turno->ciudadano_nombre,
            'ciudadano_documento' => $this->turno->ciudadano_documento,
            'es_preferencial' => (bool) $this->turno->es_preferencial,
            'estado' => $this->turno->estado,
            'area_id' => $this->turno->area_id,
            'area_nombre' => $this->turno->area?->nombre,
            'tramite_nombre' => $this->turno->tramite?->nombre,
            'fecha_emision' => $this->turno->fecha_emision?->format('H:i:s') ?? now()->format('H:i:s'),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('cola.publica'),
            new Channel('area.' . $this->turno->area_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'turno.creado';
    }
}

