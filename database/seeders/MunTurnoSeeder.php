<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Tramite;
use App\Models\User;
use App\Models\Ventanilla;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MunTurnoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Usuarios Operadores y Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@munturno.gob'],
            [
                'name' => 'Administrador MunTurno',
                'password' => Hash::make('password'),
            ]
        );

        $op1 = User::firstOrCreate(
            ['email' => 'operador1@munturno.gob'],
            [
                'name' => 'Carlos Mendoza (Rentas)',
                'password' => Hash::make('password'),
            ]
        );

        $op2 = User::firstOrCreate(
            ['email' => 'operador2@munturno.gob'],
            [
                'name' => 'María Fernández (Rentas)',
                'password' => Hash::make('password'),
            ]
        );

        $op3 = User::firstOrCreate(
            ['email' => 'operador3@munturno.gob'],
            [
                'name' => 'Jorge Ramos (Registro Civil)',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Áreas y Trámites
        $areasData = [
            [
                'nombre' => 'Rentas y Administración Tributaria',
                'codigo' => 'REN',
                'descripcion' => 'Pagos de tributos municipales, impuesto predial, arbitrios y constancias.',
                'color' => '#2563eb', // Blue
                'icono' => 'currency-dollar',
                'tramites' => [
                    ['nombre' => 'Pago de Impuesto Predial y Arbitrios', 'tiempo_estimado_minutos' => 8, 'es_preferencial' => false],
                    ['nombre' => 'Convenios de Fraccionamiento de Deuda', 'tiempo_estimado_minutos' => 15, 'es_preferencial' => false],
                    ['nombre' => 'Constancia de No Adeudo Tributario', 'tiempo_estimado_minutos' => 5, 'es_preferencial' => false],
                ],
            ],
            [
                'nombre' => 'Registro Civil e Identidad',
                'codigo' => 'REG',
                'descripcion' => 'Actas de nacimiento, matrimonio, defunción y certificados.',
                'color' => '#059669', // Emerald
                'icono' => 'identification',
                'tramites' => [
                    ['nombre' => 'Copia Certificada de Partida / Acta', 'tiempo_estimado_minutos' => 6, 'es_preferencial' => false],
                    ['nombre' => 'Inscripción de Nacimiento o Matrimonio', 'tiempo_estimado_minutos' => 15, 'es_preferencial' => false],
                    ['nombre' => 'Rectificación Administrativa de Actas', 'tiempo_estimado_minutos' => 20, 'es_preferencial' => false],
                ],
            ],
            [
                'nombre' => 'Licencias y Desarrollo Económico',
                'codigo' => 'LIC',
                'descripcion' => 'Licencias comerciales de funcionamiento, autorizaciones y vallas.',
                'color' => '#7c3aed', // Purple
                'icono' => 'storefront',
                'tramites' => [
                    ['nombre' => 'Licencia de Funcionamiento Comercial', 'tiempo_estimado_minutos' => 15, 'es_preferencial' => false],
                    ['nombre' => 'Inspección Técnica de Seguridad (ITSE)', 'tiempo_estimado_minutos' => 12, 'es_preferencial' => false],
                    ['nombre' => 'Autorización para Anuncios Publicitarios', 'tiempo_estimado_minutos' => 10, 'es_preferencial' => false],
                ],
            ],
            [
                'nombre' => 'Desarrollo Urbano, Catastro y Obras',
                'codigo' => 'OBR',
                'descripcion' => 'Licencias de edificación, certificados de zonificación y catastro.',
                'color' => '#d97706', // Amber
                'icono' => 'building-office',
                'tramites' => [
                    ['nombre' => 'Licencia de Edificación y Demolición', 'tiempo_estimado_minutos' => 18, 'es_preferencial' => false],
                    ['nombre' => 'Certificado de Parámetros y Zonificación', 'tiempo_estimado_minutos' => 10, 'es_preferencial' => false],
                    ['nombre' => 'Visación de Planos y Catastro', 'tiempo_estimado_minutos' => 15, 'es_preferencial' => false],
                ],
            ],
            [
                'nombre' => 'Atención Social y Programas Inclusivos',
                'codigo' => 'SOC',
                'descripcion' => 'Orientación a adultos mayores, OMAPED, Vaso de Leche y programas sociales.',
                'color' => '#db2777', // Pink
                'icono' => 'heart',
                'tramites' => [
                    ['nombre' => 'Atención y Empadronamiento SISFOH', 'tiempo_estimado_minutos' => 10, 'es_preferencial' => true],
                    ['nombre' => 'Inscripción a Programas de Adulto Mayor / OMAPED', 'tiempo_estimado_minutos' => 12, 'es_preferencial' => true],
                    ['nombre' => 'Orientación e Información General al Ciudadano', 'tiempo_estimado_minutos' => 5, 'es_preferencial' => false],
                ],
            ],
        ];

        $createdAreas = [];

        foreach ($areasData as $item) {
            $tramites = $item['tramites'];
            unset($item['tramites']);

            $area = Area::firstOrCreate(['codigo' => $item['codigo']], $item);
            $createdAreas[$area->codigo] = $area;

            foreach ($tramites as $tr) {
                Tramite::firstOrCreate(
                    ['area_id' => $area->id, 'nombre' => $tr['nombre']],
                    $tr
                );
            }
        }

        // 3. Ventanillas
        $ventanillasData = [
            ['numero' => 'Ventanilla 01', 'area_id' => $createdAreas['REN']->id, 'ubicacion' => 'Piso 1 - Módulo Rentas', 'user_id' => $op1->id, 'estado' => 'disponible'],
            ['numero' => 'Ventanilla 02', 'area_id' => $createdAreas['REN']->id, 'ubicacion' => 'Piso 1 - Módulo Rentas', 'user_id' => $op2->id, 'estado' => 'disponible'],
            ['numero' => 'Ventanilla 03', 'area_id' => $createdAreas['REG']->id, 'ubicacion' => 'Piso 1 - Registro Civil', 'user_id' => $op3->id, 'estado' => 'disponible'],
            ['numero' => 'Ventanilla 04', 'area_id' => $createdAreas['LIC']->id, 'ubicacion' => 'Piso 2 - Licencias', 'user_id' => null, 'estado' => 'inactiva'],
            ['numero' => 'Ventanilla 05', 'area_id' => $createdAreas['OBR']->id, 'ubicacion' => 'Piso 2 - Obras Privadas', 'user_id' => null, 'estado' => 'inactiva'],
            ['numero' => 'Módulo Preferencial 01', 'area_id' => $createdAreas['SOC']->id, 'ubicacion' => 'Hall Central - Acceso Rampa', 'user_id' => null, 'estado' => 'disponible'],
        ];

        foreach ($ventanillasData as $v) {
            Ventanilla::firstOrCreate(
                ['numero' => $v['numero']],
                $v
            );
        }
    }
}

