<?php

namespace App\Events;

use App\Models\Turno;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TurnoActualizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;

    public function __construct(public Turno $turno)
    {
        $this->turno->loadMissing(['area', 'tramite', 'ventanilla']);

        $this->data = [
            'id' => $this->turno->id,
            'codigo' => $this->turno->codigo,
            'estado' => $this->turno->estado,
            'ventanilla_numero' => $this->turno->ventanilla?->numero,
            'ventanilla_id' => $this->turno->ventanilla_id,
            'area_id' => $this->turno->area_id,
            'updated_at' => now()->format('H:i:s'),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('cola.publica'),
            new Channel('turno.' . $this->turno->id),
            new Channel('area.' . $this->turno->area_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'turno.actualizado';
    }
}

