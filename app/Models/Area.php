<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use HasFactory;

    protected $table = 'areas';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'color',
        'icono',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function tramites(): HasMany
    {
        return $this->hasMany(Tramite::class);
    }

    public function ventanillas(): HasMany
    {
        return $this->hasMany(Ventanilla::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }
}

