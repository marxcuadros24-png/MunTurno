<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ventanilla extends Model
{
    use HasFactory;

    protected $table = 'ventanillas';

    protected $fillable = [
        'numero',
        'area_id',
        'ubicacion',
        'user_id',
        'estado',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }

    public function llamados(): HasMany
    {
        return $this->hasMany(LlamadoHistorial::class);
    }

    public function turnoActual()
    {
        return $this->hasOne(Turno::class)->whereIn('estado', ['llamado', 'atendiendo'])->latest('updated_at');
    }
}

