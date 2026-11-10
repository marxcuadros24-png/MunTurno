<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CiudadanoController;
use App\Http\Controllers\KioscoController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\PantallaTvController;
use Illuminate\Support\Facades\Route;

// Portal Central / Selector de Módulos MunTurno
Route::get('/', function () {
    return view('home');
})->name('home');

// Módulo 1: Kiosco Ciudadano (Emisión de Turnos)
Route::prefix('kiosco')->name('kiosco.')->group(function () {
    Route::get('/', [KioscoController::class, 'index'])->name('index');
    Route::post('/emitir', [KioscoController::class, 'emitir'])->name('emitir');
    Route::get('/ticket/{turno}', [KioscoController::class, 'ticket'])->name('ticket');
});

// Módulo 2: Pantalla de TV Sala de Espera (Turnero Público)
Route::prefix('tv')->name('tv.')->group(function () {
    Route::get('/', [PantallaTvController::class, 'index'])->name('index');
    Route::get('/estado', [PantallaTvController::class, 'estadoActual'])->name('estado');
});

// Módulo 3: Módulo de Operador / Ventanilla
Route::prefix('operador')->name('operador.')->group(function () {
    Route::get('/', [OperadorController::class, 'index'])->name('index');
    Route::get('/cola/{ventanilla}', [OperadorController::class, 'colaActual'])->name('cola');
    Route::post('/llamar/{ventanilla}', [OperadorController::class, 'llamarSiguiente'])->name('llamar');
    Route::post('/rellamar/{turno}', [OperadorController::class, 'rellamar'])->name('rellamar');
    Route::post('/iniciar/{turno}', [OperadorController::class, 'iniciarAtencion'])->name('iniciar');
    Route::post('/finalizar/{turno}', [OperadorController::class, 'finalizarAtencion'])->name('finalizar');
    Route::post('/ausente/{turno}', [OperadorController::class, 'marcarAusente'])->name('ausente');
    Route::post('/transferir/{turno}', [OperadorController::class, 'transferir'])->name('transferir');
});

// Módulo 4: Seguimiento Móvil del Ciudadano
Route::get('/consultar-turno', [CiudadanoController::class, 'formularioConsulta'])->name('ciudadano.consulta');
Route::post('/consultar-turno', [CiudadanoController::class, 'buscarTurno'])->name('ciudadano.buscar');
Route::get('/mi-turno/{codigo}', [CiudadanoController::class, 'seguimiento'])->name('ciudadano.seguimiento');
Route::get('/api/mi-turno/{codigo}/estado', [CiudadanoController::class, 'estadoTurno'])->name('ciudadano.estado');

// Módulo 5: Dashboard de Administración & Métricas
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/metricas', [AdminController::class, 'metricas'])->name('metricas');
    Route::get('/exportar', [AdminController::class, 'exportarCsv'])->name('exportar');
});
