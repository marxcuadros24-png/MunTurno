<x-layouts.app :title="'Kiosco de Emisión de Turnos'">
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="kioscoApp()">
    <!-- Cabecera del Kiosco -->
    <div class="text-center mb-8">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider mb-2">
            Kiosco de Autoservicio Municipal
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-display">Solicitud de Turno de Atención</h1>
        <p class="text-slate-600 text-sm mt-1 max-w-xl mx-auto">Selecciona el área municipal de tu trámite e ingresa tus datos para emitir tu ticket digital.</p>
    </div>

    <!-- Progreso Visual de Pasos -->
    <div class="flex items-center justify-center gap-2 sm:gap-4 mb-8 text-xs font-semibold">
        <button type="button" @click="paso = 1" :class="paso >= 1 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500'" class="px-4 py-2 rounded-xl flex items-center gap-2 transition">
            <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px] font-bold">1</span>
            <span>Área & Trámite</span>
        </button>
        <span class="text-slate-300">──</span>
        <button type="button" @click="if(tramiteSeleccionado) paso = 2" :disabled="!tramiteSeleccionado" :class="paso >= 2 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500'" class="px-4 py-2 rounded-xl flex items-center gap-2 transition disabled:opacity-50">
            <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px] font-bold">2</span>
            <span>Datos del Ciudadano</span>
        </button>
    </div>

    <form action="{{ route('kiosco.emitir') }}" method="POST" id="formTurno">
        @csrf
        <input type="hidden" name="tramite_id" :value="tramiteSeleccionado ? tramiteSeleccionado.id : ''">

        <!-- PASO 1: Selección de Área y Trámite -->
        <div x-show="paso === 1" class="space-y-6">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                1. Selecciona el Área y Trámite Requerido
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($areas as $area)
                    <div class="bg-white rounded-2xl border-2 transition-all duration-200 overflow-hidden shadow-xs hover:shadow-md"
                         :class="areaSeleccionada && areaSeleccionada.id === {{ $area->id }} ? 'border-blue-600 ring-2 ring-blue-600/20' : 'border-slate-200 hover:border-slate-300'">
                        <!-- Header del Área -->
                        <div class="p-5 flex items-start justify-between cursor-pointer"
                             @click="seleccionarArea({{ $area->toJson() }})">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-sm" style="background-color: {{ $area->color }}">
                                    {{ $area->codigo }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 text-base leading-tight">{{ $area->nombre }}</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $area->tramites->count() }} trámites disponibles</p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold px-2 py-1 rounded-md"
                                  :class="areaSeleccionada && areaSeleccionada.id === {{ $area->id }} ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'">
                                <span x-text="areaSeleccionada && areaSeleccionada.id === {{ $area->id }} ? 'Seleccionado' : 'Elegir'"></span>
                            </span>
                        </div>

                        <!-- Trámites del Área (se expande al seleccionar el área) -->
                        <div x-show="areaSeleccionada && areaSeleccionada.id === {{ $area->id }}" class="border-t border-slate-100 bg-slate-50/50 p-4 space-y-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Selecciona tu trámite específico:</p>
                            @foreach($area->tramites as $tr)
                                <div @click="seleccionarTramite({{ $tr->toJson() }})"
                                     class="p-3 rounded-xl border bg-white flex items-center justify-between cursor-pointer transition hover:border-blue-400"
                                     :class="tramiteSeleccionado && tramiteSeleccionado.id === {{ $tr->id }} ? 'border-blue-600 bg-blue-50/50 font-semibold text-blue-900' : 'border-slate-200 text-slate-700'">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full" :class="tramiteSeleccionado && tramiteSeleccionado.id === {{ $tr->id }} ? 'bg-blue-600' : 'bg-slate-300'"></div>
                                        <span class="text-sm">{{ $tr->nombre }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($tr->es_preferencial)
                                            <span class="text-[10px] font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">Preferencial</span>
                                        @endif
                                        <span class="text-xs text-slate-400">~{{ $tr->tiempo_estimado_minutos }} min</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end pt-4">
                <button type="button" @click="avanzarAPaso2()" :disabled="!tramiteSeleccionado"
                        class="px-8 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md hover:shadow-lg disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-2">
                    <span>Continuar a Datos del Ciudadano</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </button>
            </div>
        </div>

        <!-- PASO 2: Datos del Ciudadano y Tipo de Atención -->
        <div x-show="paso === 2" class="space-y-6" style="display: none;">
            <!-- Resumen de Trámite Elegido -->
            <div class="bg-blue-50/70 border border-blue-200/80 rounded-2xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xs" x-text="areaSeleccionada ? areaSeleccionada.codigo : ''"></div>
                    <div>
                        <span class="text-xs text-blue-600 font-bold uppercase" x-text="areaSeleccionada ? areaSeleccionada.nombre : ''"></span>
                        <h4 class="text-base font-bold text-slate-900" x-text="tramiteSeleccionado ? tramiteSeleccionado.nombre : ''"></h4>
                    </div>
                </div>
                <button type="button" @click="paso = 1" class="text-xs font-semibold text-blue-700 hover:underline">Cambiar</button>
            </div>

            <!-- Opción de Atención Preferencial (Ley de Atención Inclusiva) -->
            <div class="bg-amber-50/60 border border-amber-200 rounded-2xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-900">¿Requiere Atención Preferencial?</h4>
                        <p class="text-xs text-amber-700">Para adultos mayores, mujeres embarazadas o personas con discapacidad.</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="es_preferencial" value="1" x-model="esPreferencial" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                </label>
            </div>

            <!-- Formulario de Identificación -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <h3 class="text-base font-bold text-slate-900">2. Identificación del Ciudadano</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Documento</label>
                        <select name="ciudadano_tipo_documento" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="DNI">DNI - Documento Nacional</option>
                            <option value="CE">CE - Carnet de Extranjería</option>
                            <option value="PASAPORTE">Pasaporte</option>
                            <option value="OTRO">Otro Documento</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Número de Documento <span class="text-red-500">*</span></label>
                        <input type="text" name="ciudadano_documento" required placeholder="Ej. 74839201"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nombres y Apellidos Completos <span class="text-red-500">*</span></label>
                    <input type="text" name="ciudadano_nombre" required placeholder="Ej. Rosa María Quispe Gómez"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Teléfono Móvil (Opcional - Notificación)</label>
                        <input type="tel" name="ciudadano_telefono" placeholder="Ej. 987654321"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Correo Electrónico (Opcional)</label>
                        <input type="email" name="ciudadano_email" placeholder="ciudadano@correo.com"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-between pt-4">
                <button type="button" @click="paso = 1" class="px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-100 transition">
                    Volver Atrás
                </button>
                <button type="submit" class="px-8 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-base shadow-lg hover:shadow-xl transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>EMITIR MI TICKET DE TURNO</span>
                </button>
            </div>
        </div>
    </form>
</div>
</x-layouts.app>

@push('scripts')
<script>
    function kioscoApp() {
        return {
            paso: 1,
            areaSeleccionada: null,
            tramiteSeleccionado: null,
            esPreferencial: false,

            seleccionarArea(area) {
                this.areaSeleccionada = area;
                this.tramiteSeleccionado = null;
            },

            seleccionarTramite(tramite) {
                this.tramiteSeleccionado = tramite;
                if (tramite.es_preferencial) {
                    this.esPreferencial = true;
                }
            },

            avanzarAPaso2() {
                if (this.tramiteSeleccionado) {
                    this.paso = 2;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        };
    }
</script>
<!-- Alpine.js CDN para reactividad instantánea del formulario -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

