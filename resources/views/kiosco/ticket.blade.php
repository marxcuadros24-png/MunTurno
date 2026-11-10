@extends('layouts.app')

@section('title', 'Ticket de Turno ' . $turno->codigo)

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">
    <!-- Ticket Card Estilizado para Impresión y Pantalla -->
    <div id="ticketImprimible" class="bg-white rounded-3xl border-2 border-slate-200 shadow-xl overflow-hidden print:border-none print:shadow-none print:m-0">
        <!-- Cabecera del Ticket -->
        <div class="p-6 text-center text-white relative" style="background-color: {{ $turno->area->color ?? '#2563eb' }}">
            <div class="text-[11px] font-bold uppercase tracking-widest opacity-90">Municipalidad - Atención al Ciudadano</div>
            <h2 class="text-xl font-bold font-display mt-0.5">{{ $turno->area->nombre }}</h2>
            <p class="text-xs opacity-85 mt-1">{{ $turno->tramite->nombre }}</p>

            @if($turno->es_preferencial)
                <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400 text-amber-950 font-extrabold text-xs tracking-wider uppercase shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                    <span>Atención Preferencial</span>
                </div>
            @endif
        </div>

        <!-- Cuerpo del Ticket -->
        <div class="p-8 text-center space-y-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tu Código de Turno</span>
                <div class="text-6xl sm:text-7xl font-extrabold tracking-tight text-slate-900 font-display my-2">
                    {{ $turno->codigo }}
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                    <span>Estado: En Espera</span>
                </div>
            </div>

            <!-- Métricas de Cola -->
            <div class="grid grid-cols-2 gap-4 py-4 border-y border-dashed border-slate-200 text-left">
                <div class="bg-slate-50 p-3 rounded-xl">
                    <span class="text-[11px] font-bold text-slate-500 uppercase">Personas Adelante</span>
                    <div class="text-2xl font-black text-slate-800 mt-0.5">
                        {{ $personasAdelante }}
                    </div>
                    <span class="text-[10px] text-slate-400">en esta misma área</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl">
                    <span class="text-[11px] font-bold text-slate-500 uppercase">Tiempo Estimado</span>
                    <div class="text-2xl font-black text-slate-800 mt-0.5">
                        ~{{ max(3, $personasAdelante * ($turno->tramite->tiempo_estimado_minutos ?? 8)) }} min
                    </div>
                    <span class="text-[10px] text-slate-400">según afluencia actual</span>
                </div>
            </div>

            <!-- Datos del Ciudadano -->
            <div class="text-xs text-slate-600 space-y-1 bg-slate-50 p-4 rounded-xl text-left">
                <div class="flex justify-between">
                    <span class="text-slate-400">Ciudadano:</span>
                    <span class="font-bold text-slate-800">{{ $turno->ciudadano_nombre }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">{{ $turno->ciudadano_tipo_documento }}:</span>
                    <span class="font-mono font-semibold text-slate-800">{{ $turno->ciudadano_documento }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Fecha y Hora:</span>
                    <span class="text-slate-700">{{ $turno->fecha_emision ? $turno->fecha_emision->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}</span>
                </div>
            </div>

            <!-- Código QR para Seguimiento en el Celular -->
            <div class="pt-2 flex flex-col items-center">
                <p class="text-xs font-semibold text-slate-500 mb-3">Escanea para seguir tu turno en tu celular en tiempo real:</p>
                <div class="p-2 bg-white rounded-2xl border border-slate-200 shadow-xs inline-block">
                    <!-- QR Generado con API pública estándar y fallback -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('ciudadano.seguimiento', $turno->codigo)) }}"
                         alt="QR de Seguimiento"
                         class="w-32 h-32 rounded-lg"
                         loading="lazy">
                </div>
                <a href="{{ route('ciudadano.seguimiento', $turno->codigo) }}" class="text-xs font-bold text-blue-600 hover:underline mt-2 inline-flex items-center gap-1">
                    <span>O haz clic aquí para ver tu turno en vivo</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            </div>
        </div>

        <!-- Footer del Ticket -->
        <div class="p-4 bg-slate-50 text-center text-[11px] text-slate-400 border-t border-slate-100">
            Guarda este ticket. Tu turno será anunciado en las pantallas y altavoces de la sala.
        </div>
    </div>

    <!-- Botones de Acción Kiosco -->
    <div class="mt-6 flex flex-col sm:flex-row gap-3 print:hidden">
        <button onclick="window.print()" class="flex-1 py-3 px-4 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm shadow-xs flex items-center justify-center gap-2 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
            <span>Imprimir Ticket</span>
        </button>
        <a href="{{ route('ciudadano.seguimiento', $turno->codigo) }}" class="flex-1 py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md flex items-center justify-center gap-2 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
            <span>Seguir en mi Celular</span>
        </a>
        <a href="{{ route('kiosco.index') }}" class="py-3 px-4 rounded-xl border border-slate-200 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm flex items-center justify-center transition">
            <span>Nuevo Turno</span>
        </a>
    </div>
</div>
@endsection

