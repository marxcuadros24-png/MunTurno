<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turno extends Model
{
    use HasFactory;

    protected $table = 'turnos';

    protected $fillable = [
        'codigo',
        'numero_secuencia',
        'area_id',
        'tramite_id',
        'ventanilla_id',
        'user_id',
        'ciudadano_tipo_documento',
        'ciudadano_documento',
        'ciudadano_nombre',
        'ciudadano_telefono',
        'ciudadano_email',
        'es_preferencial',
        'estado',
        'llamadas_contador',
        'fecha_emision',
        'fecha_primer_llamado',
        'fecha_inicio_atencion',
        'fecha_fin_atencion',
        'tiempo_espera_segundos',
        'tiempo_atencion_segundos',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'es_preferencial' => 'boolean',
            'llamadas_contador' => 'integer',
            'numero_secuencia' => 'integer',
            'fecha_emision' => 'datetime',
            'fecha_primer_llamado' => 'datetime',
            'fecha_inicio_atencion' => 'datetime',
            'fecha_fin_atencion' => 'datetime',
            'tiempo_espera_segundos' => 'integer',
            'tiempo_atencion_segundos' => 'integer',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function tramite(): BelongsTo
    {
        return $this->belongsTo(Tramite::class);
    }

    public function ventanilla(): BelongsTo
    {
        return $this->belongsTo(Ventanilla::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function llamados(): HasMany
    {
        return $this->hasMany(LlamadoHistorial::class);
    }
}

