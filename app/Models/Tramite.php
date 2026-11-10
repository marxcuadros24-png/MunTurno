<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tramite extends Model
{
    use HasFactory;

    protected $table = 'tramites';

    protected $fillable = [
        'area_id',
        'nombre',
        'descripcion',
        'tiempo_estimado_minutos',
        'es_preferencial',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'es_preferencial' => 'boolean',
            'activo' => 'boolean',
            'tiempo_estimado_minutos' => 'integer',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }
}

