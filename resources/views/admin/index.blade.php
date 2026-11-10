@extends('layouts.app')

@section('title', 'Dashboard de Supervisión y Métricas')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="adminDashboard()" x-init="initApp()">
    <!-- Header del Dashboard -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold font-display text-slate-900">Panel de Control & Métricas</h1>
                <span class="text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>En Vivo</span>
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Supervisión en tiempo real de la atención ciudadana, tiempos de espera y productividad municipal.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-400 font-mono">Última actualización: <span x-text="ultimaActualizacion">--:--:--</span></span>
            <button @click="cargarMetricas()" class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                <span>Actualizar</span>
            </button>
        </div>
    </div>

    <!-- TARJETAS DE INDICADORES CLAVE (KPIs) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <!-- 1. Total Emitidos -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Hoy</span>
            <div class="text-3xl font-black font-display text-slate-900 mt-1" x-text="metricas.total_emitidos">{{ $totalEmitidos }}</div>
            <span class="text-[10px] text-slate-500">turnos generados</span>
        </div>

        <!-- 2. En Espera -->
        <div class="bg-white rounded-2xl border border-blue-200 bg-blue-50/30 p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600">En Espera</span>
            <div class="text-3xl font-black font-display text-blue-700 mt-1" x-text="metricas.total_espera">{{ $totalEspera }}</div>
            <span class="text-[10px] text-blue-500">pendientes en cola</span>
        </div>

        <!-- 3. Atendidos -->
        <div class="bg-white rounded-2xl border border-emerald-200 bg-emerald-50/30 p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Atendidos</span>
            <div class="text-3xl font-black font-display text-emerald-700 mt-1" x-text="metricas.total_atendidos">{{ $totalAtendidos }}</div>
            <span class="text-[10px] text-emerald-600 font-semibold">
                {{ $totalEmitidos > 0 ? round(($totalAtendidos / $totalEmitidos) * 100) : 0 }}% efectividad
            </span>
        </div>

        <!-- 4. Ausentes -->
        <div class="bg-white rounded-2xl border border-red-200 bg-red-50/30 p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-red-600">Ausentes</span>
            <div class="text-3xl font-black font-display text-red-700 mt-1" x-text="metricas.total_ausentes">{{ $totalAusentes }}</div>
            <span class="text-[10px] text-red-500">no se presentaron</span>
        </div>

        <!-- 5. T. Espera Promedio -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">T. Espera Prom.</span>
            <div class="text-3xl font-black font-display text-slate-900 mt-1">
                <span x-text="metricas.promedio_espera_min">{{ $promedioEsperaMinutos }}</span><span class="text-sm font-normal text-slate-400"> min</span>
            </div>
            <span class="text-[10px] text-slate-500">tiempo hasta llamado</span>
        </div>

        <!-- 6. T. Atención Promedio -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">T. Atención Prom.</span>
            <div class="text-3xl font-black font-display text-slate-900 mt-1">
                <span x-text="metricas.promedio_atencion_min">{{ $promedioAtencionMinutos }}</span><span class="text-sm font-normal text-slate-400"> min</span>
            </div>
            <span class="text-[10px] text-slate-500">en ventanilla</span>
        </div>
    </div>

    <!-- SECCIÓN INTERMEDIA: Estado de Ventanillas + Desglose por Áreas -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        <!-- Ventanillas Activas (7 Columnas) -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
            <h3 class="text-base font-bold font-display text-slate-900 mb-4 flex items-center justify-between">
                <span>Estado de Ventanillas y Módulos</span>
                <span class="text-xs font-semibold text-slate-500">{{ $ventanillas->count() }} configuradas</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($ventanillas as $v)
                    <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/50 flex flex-col justify-between">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="font-bold text-slate-900 text-sm">{{ $v->numero }}</span>
                                <p class="text-xs text-slate-500">{{ $v->area->nombre ?? 'Atención General' }}</p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase
                                {{ $v->estado === 'atendiendo' ? 'bg-amber-100 text-amber-800' : ($v->estado === 'disponible' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600') }}">
                                {{ $v->estado }}
                            </span>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs text-slate-500">
                            <span>Operador: <strong class="text-slate-700">{{ $v->operador ? explode(' ', $v->operador->name)[0] : 'Sin asignar' }}</strong></span>
                            <span class="font-bold text-slate-800 font-mono">{{ $v->atendidos_count }} atendidos</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Estadísticas por Área (5 Columnas) -->
        <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
            <h3 class="text-base font-bold font-display text-slate-900 mb-4">Afluencia por Área Municipal</h3>
            <div class="space-y-4">
                @foreach($areas as $area)
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $area->color }}"></span>
                                <span class="text-slate-800">{{ $area->nombre }}</span>
                            </span>
                            <span class="text-slate-600">{{ $area->turnos_hoy_count }} turnos</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full transition-all duration-500"
                                 style="background-color: {{ $area->color }}; width: {{ $totalEmitidos > 0 ? ($area->turnos_hoy_count / $totalEmitidos) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- TABLA DE ACTIVIDAD RECIENTE -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
        <h3 class="text-base font-bold font-display text-slate-900 mb-4">Últimos Turnos Registrados</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">
                        <th class="py-2.5 font-bold">Código</th>
                        <th class="py-2.5 font-bold">Ciudadano</th>
                        <th class="py-2.5 font-bold">Área / Trámite</th>
                        <th class="py-2.5 font-bold">Ventanilla</th>
                        <th class="py-2.5 font-bold">Estado</th>
                        <th class="py-2.5 font-bold">T. Espera</th>
                        <th class="py-2.5 font-bold">T. Atención</th>
                        <th class="py-2.5 font-bold">Hora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ultimosTurnos as $t)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 font-mono font-bold text-slate-900">
                                {{ $t->codigo }}
                                @if($t->es_preferencial)
                                    <span class="text-[9px] bg-amber-300 text-amber-950 font-black px-1 rounded ml-1">P</span>
                                @endif
                            </td>
                            <td class="py-3 text-slate-700 font-semibold">{{ $t->ciudadano_nombre }}</td>
                            <td class="py-3 text-slate-600">
                                <span class="font-semibold" style="color: {{ $t->area->color }}">{{ $t->area->nombre }}</span>
                                <span class="text-slate-400 block text-[11px]">{{ $t->tramite->nombre }}</span>
                            </td>
                            <td class="py-3 font-semibold text-slate-800">{{ $t->ventanilla->numero ?? '-' }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full font-bold uppercase text-[10px]
                                    {{ $t->estado === 'finalizado' ? 'bg-emerald-100 text-emerald-800' : ($t->estado === 'atendiendo' ? 'bg-indigo-100 text-indigo-800' : ($t->estado === 'llamado' ? 'bg-amber-100 text-amber-800' : ($t->estado === 'espera' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'))) }}">
                                    {{ $t->estado }}
                                </span>
                            </td>
                            <td class="py-3 text-slate-500 font-mono">{{ $t->tiempo_espera_segundos ? round($t->tiempo_espera_segundos / 60) . ' min' : '-' }}</td>
                            <td class="py-3 text-slate-500 font-mono">{{ $t->tiempo_atencion_segundos ? round($t->tiempo_atencion_segundos / 60) . ' min' : '-' }}</td>
                            <td class="py-3 text-slate-400 font-mono">{{ $t->created_at->format('H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 text-slate-400">Sin registros de turnos en el sistema.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function adminDashboard() {
        return {
            metricas: {
                total_emitidos: {{ $totalEmitidos }},
                total_atendidos: {{ $totalAtendidos }},
                total_espera: {{ $totalEspera }},
                total_ausentes: {{ $totalAusentes }},
                promedio_espera_min: {{ $promedioEsperaMinutos }},
                promedio_atencion_min: {{ $promedioAtencionMinutos }}
            },
            ultimaActualizacion: new Date().toLocaleTimeString('es-ES'),

            initApp() {
                this.iniciarWebSockets();
                // Polling cada 30 segundos
                setInterval(() => this.cargarMetricas(), 30000);
            },

            iniciarWebSockets() {
                if (window.Echo) {
                    window.Echo.channel('cola.publica')
                        .listen('.turno.creado', () => this.cargarMetricas())
                        .listen('.turno.llamado', () => this.cargarMetricas())
                        .listen('.turno.actualizado', () => this.cargarMetricas());
                } else {
                    setTimeout(() => this.iniciarWebSockets(), 1000);
                }
            },

            async cargarMetricas() {
                try {
                    const res = await fetch('{{ route('admin.metricas') }}');
                    if (res.ok) {
                        const data = await res.json();
                        this.metricas = data;
                        this.ultimaActualizacion = data.timestamp || new Date().toLocaleTimeString('es-ES');
                    }
                } catch(e) {
                    console.error('Error cargando métricas:', e);
                }
            }
        };
    }
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

