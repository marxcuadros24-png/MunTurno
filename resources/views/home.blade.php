@extends('layouts.app')

@section('title', 'Portal Municipal de Turnos y Colas en Tiempo Real')

@section('content')
<div class="py-12 bg-gradient-to-b from-slate-50 via-white to-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Hero Header -->
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200/60 text-blue-700 text-xs font-semibold mb-4">
                <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
                <span>WebSockets Laravel Reverb Activo</span>
            </div>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight font-display mb-4">
                Sistema Web de Turnos y <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Gestión de Colas</span>
            </h1>
            <p class="text-lg text-slate-600 leading-relaxed">
                Modernización integral de la atención ciudadana con asignación inteligente de turnos, pantalla de sala interactiva con voz y control de tiempos en tiempo real.
            </p>
        </div>

        <!-- Módulos Principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
            <!-- Módulo 1: Kiosco Ciudadano -->
            <a href="{{ route('kiosco.index') }}" class="group relative bg-white rounded-2xl p-6 border border-slate-200 hover:border-blue-400 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/5 rounded-bl-full pointer-events-none transition-all group-hover:scale-125"></div>
                <div>
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-5 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Módulo 01</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-1 mb-2">Kiosco de Turnos</h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-6">
                        Emisión rápida de tickets presenciales o móviles para el ciudadano. Genera correlativo con QR y prioridad inclusiva.
                    </p>
                </div>
                <div class="flex items-center text-sm font-semibold text-blue-600 group-hover:translate-x-1 transition-transform">
                    <span>Acceder al Kiosco</span>
                    <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
            </a>

            <!-- Módulo 2: Pantalla TV Sala -->
            <a href="{{ route('tv.index') }}" target="_blank" class="group relative bg-white rounded-2xl p-6 border border-slate-200 hover:border-indigo-400 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/5 rounded-bl-full pointer-events-none transition-all group-hover:scale-125"></div>
                <div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center mb-5 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Módulo 02</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-1 mb-2">Pantalla TV Sala</h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-6">
                        Monitor de sala de espera a pantalla completa con aviso sonoro (chime) y voz sintetizada automática en tiempo real.
                    </p>
                </div>
                <div class="flex items-center text-sm font-semibold text-indigo-600 group-hover:translate-x-1 transition-transform">
                    <span>Abrir Turnero TV</span>
                    <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </div>
            </a>

            <!-- Módulo 3: Operador de Ventanilla -->
            <a href="{{ route('operador.index') }}" class="group relative bg-white rounded-2xl p-6 border border-slate-200 hover:border-emerald-400 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-bl-full pointer-events-none transition-all group-hover:scale-125"></div>
                <div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-5 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Módulo 03</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-1 mb-2">Panel de Operador</h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-6">
                        Gestión ágil para el cajero/ventanilla: llamar siguiente, re-llamar, cronómetro de atención y registro de ausentes.
                    </p>
                </div>
                <div class="flex items-center text-sm font-semibold text-emerald-600 group-hover:translate-x-1 transition-transform">
                    <span>Entrar a Ventanilla</span>
                    <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
            </a>

            <!-- Módulo 4: Dashboard y Métricas -->
            <a href="{{ route('admin.index') }}" class="group relative bg-white rounded-2xl p-6 border border-slate-200 hover:border-purple-400 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/5 rounded-bl-full pointer-events-none transition-all group-hover:scale-125"></div>
                <div>
                    <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center mb-5 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-600">Módulo 04</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-1 mb-2">Administración</h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-6">
                        Métricas de atención ciudadana: tiempos de espera ($T_e$), tiempos de servicio ($T_a$), productividad y afluencia por área.
                    </p>
                </div>
                <div class="flex items-center text-sm font-semibold text-purple-600 group-hover:translate-x-1 transition-transform">
                    <span>Ver Estadísticas</span>
                    <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
            </a>
        </div>

        <!-- Banner Explicativo de Flujo -->
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-8 sm:p-10 text-white shadow-xl relative overflow-hidden">
            <div class="relative z-10 max-w-3xl">
                <h3 class="text-2xl font-bold font-display mb-3">Flujo de Atención en 3 Pasos</h3>
                <p class="text-slate-300 text-sm mb-6 leading-relaxed">
                    El sistema coordina en tiempo real al ciudadano, la sala de espera y los servidores públicos mediante WebSockets nativos con latencia ultra baja.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-4 border-t border-slate-700/60 text-sm">
                    <div>
                        <span class="w-7 h-7 rounded-full bg-blue-500 text-white font-bold flex items-center justify-center text-xs mb-2">1</span>
                        <h4 class="font-semibold text-white">Ciudadano Solicita</h4>
                        <p class="text-xs text-slate-400 mt-1">Elige trámite y recibe ticket con código y QR en kiosco o móvil.</p>
                    </div>
                    <div>
                        <span class="w-7 h-7 rounded-full bg-indigo-500 text-white font-bold flex items-center justify-center text-xs mb-2">2</span>
                        <h4 class="font-semibold text-white">Operador Llama</h4>
                        <p class="text-xs text-slate-400 mt-1">Presiona "Llamar" y Reverb dispara la alerta inmediata a TV y móvil.</p>
                    </div>
                    <div>
                        <span class="w-7 h-7 rounded-full bg-emerald-500 text-white font-bold flex items-center justify-center text-xs mb-2">3</span>
                        <h4 class="font-semibold text-white">Pantalla Alerta</h4>
                        <p class="text-xs text-slate-400 mt-1">La TV suena y dicta por voz la ventanilla correspondiente.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

