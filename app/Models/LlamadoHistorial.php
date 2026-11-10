<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LlamadoHistorial extends Model
{
    use HasFactory;

    protected $table = 'llamados_historial';

    protected $fillable = [
        'turno_id',
        'ventanilla_id',
        'user_id',
        'llamado_at',
    ];

    protected function casts(): array
    {
        return [
            'llamado_at' => 'datetime',
        ];
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    public function ventanilla(): BelongsTo
    {
        return $this->belongsTo(Ventanilla::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

