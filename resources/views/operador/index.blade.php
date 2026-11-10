<x-layouts.app :title="'Módulo de Operador - Ventanilla de Atención'">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="operadorApp()" x-init="initApp()">
    <!-- Barra Superior del Operador: Selección de Ventanilla y Estado -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 mb-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg shadow-sm">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold font-display text-slate-900" x-text="ventanilla ? ventanilla.numero : 'Ventanilla'">---</h1>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full"
                          :class="turnoActual ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                          x-text="turnoActual ? (turnoActual.estado === 'atendiendo' ? 'En Atención' : 'Llamado') : 'Disponible'">
                    </span>
                </div>
                <p class="text-xs text-slate-500" x-text="ventanilla && ventanilla.area ? ventanilla.area.nombre : 'Área General'"></p>
            </div>
        </div>

        <!-- Selector de Ventanilla -->
        <div class="flex items-center gap-3">
            <label class="text-xs font-bold text-slate-500 uppercase">Cambiar Módulo:</label>
            <select class="px-3 py-2 rounded-xl border border-slate-300 text-sm font-semibold bg-white focus:ring-2 focus:ring-emerald-500"
                    @change="cambiarVentanilla($event.target.value)">
                @foreach($ventanillas as $v)
                    <option value="{{ $v->id }}" {{ $ventanillaSeleccionada && $ventanillaSeleccionada->id === $v->id ? 'selected' : '' }}>
                        {{ $v->numero }} ({{ $v->area->nombre ?? 'General' }})
                    </option>
                @endforeach
            </select>
            <button @click="cargarCola()" class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition" title="Refrescar">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
            </button>
        </div>
    </div>

    <!-- Mensaje Flash / Notificación -->
    <template x-if="mensaje">
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-xs transition">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span x-text="mensaje"></span>
            </div>
            <button @click="mensaje = ''" class="text-emerald-700 hover:text-emerald-900 text-xs">✕</button>
        </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- COLUMNA PRINCIPAL (7 Columnas): Control de Turno en Atención -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Tarjeta del Turno Activo -->
            <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm p-6 sm:p-8">
                <!-- Cuando NO HAY turno en atención -->
                <template x-if="!turnoActual">
                    <div class="text-center py-10 space-y-5">
                        <div class="w-20 h-20 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold font-display text-slate-900">Ventanilla Disponible</h3>
                            <p class="text-sm text-slate-500 max-w-md mx-auto mt-1">
                                Hay <span class="font-bold text-blue-600" x-text="colaEspera.length">0</span> ciudadanos en espera para tu módulo. Presiona el botón para convocar al siguiente.
                            </p>
                        </div>
                        <button type="button" @click="llamarSiguiente()"
                                :disabled="colaEspera.length === 0 || cargando"
                                class="w-full sm:w-auto px-10 py-5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-lg shadow-lg hover:shadow-xl transition flex items-center justify-center gap-3 mx-auto disabled:opacity-40 disabled:cursor-not-allowed">
                            <svg class="w-6 h-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                            <span>📢 LLAMAR SIGUIENTE TURNO</span>
                        </button>
                    </div>
                </template>

                <!-- Cuando HAY un turno llamado / en atención -->
                <template x-if="turnoActual">
                    <div class="space-y-6">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">TURNO EN ATENCIÓN</span>
                            </div>
                            <!-- Cronómetro en Vivo -->
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 text-white font-mono text-sm font-bold shadow-inner">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span x-text="cronometroFormateado">00:00</span>
                            </div>
                        </div>

                        <!-- Código y Nombre del Ciudadano -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-blue-50/60 border border-blue-200">
                            <div>
                                <span class="text-xs font-bold uppercase text-blue-600">Código Asignado</span>
                                <div class="text-5xl font-black font-display text-blue-900" x-text="turnoActual.codigo">---</div>
                                <template x-if="turnoActual.es_preferencial">
                                    <span class="inline-block mt-1 text-[11px] font-bold bg-amber-400 text-amber-950 px-2 py-0.5 rounded-full uppercase">Preferencial</span>
                                </template>
                            </div>
                            <div class="text-left sm:text-right">
                                <span class="text-xs text-slate-400">Ciudadano</span>
                                <h3 class="text-lg font-bold text-slate-900" x-text="turnoActual.ciudadano_nombre">---</h3>
                                <p class="text-xs font-mono text-slate-500" x-text="`${turnoActual.ciudadano_tipo_documento}: ${turnoActual.ciudadano_documento}`"></p>
                                <p class="text-xs text-slate-600 font-semibold mt-1" x-text="turnoActual.tramite ? turnoActual.tramite.nombre : ''"></p>
                            </div>
                        </div>

                        <!-- Botonera de Acciones de Atención -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <!-- Re-Llamar -->
                            <button type="button" @click="rellamarTurno()" :disabled="cargando"
                                    class="p-3 rounded-xl border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs flex flex-col items-center justify-center gap-1.5 transition">
                                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" /></svg>
                                <span>Re-Llamar (<span x-text="turnoActual.llamadas_contador || 1"></span>)</span>
                            </button>

                            <!-- Iniciar Atención -->
                            <button type="button" @click="iniciarAtencion()" :disabled="turnoActual.estado === 'atendiendo' || cargando"
                                    class="p-3 rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs flex flex-col items-center justify-center gap-1.5 transition disabled:opacity-50">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span x-text="turnoActual.estado === 'atendiendo' ? 'Atendiendo' : 'Tomar Asiento'"></span>
                            </button>

                            <!-- Finalizar Atención -->
                            <button type="button" @click="finalizarAtencion()" :disabled="cargando"
                                    class="p-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex flex-col items-center justify-center gap-1.5 shadow-sm transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Finalizar</span>
                            </button>

                            <!-- Marcar Ausente / No Asistió: Se bloquea y se oculta automáticamente cuando se inicia la atención -->
                            <button type="button" @click="marcarAusente()"
                                    x-show="turnoActual.estado !== 'atendiendo'"
                                    :disabled="turnoActual.estado === 'atendiendo' || cargando"
                                    class="p-3 rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 font-bold text-xs flex flex-col items-center justify-center gap-1.5 transition disabled:opacity-30 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                <span>No Asistió</span>
                            </button>

                            <!-- Derivar Trámite: Disponible cuando ya está en atención presencial -->
                            <button type="button" @click="abrirModalDerivar()"
                                    x-show="turnoActual.estado === 'atendiendo'"
                                    :disabled="cargando"
                                    class="p-3 rounded-xl border border-purple-200 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs flex flex-col items-center justify-center gap-1.5 transition">
                                <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                <span>Derivar</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Últimos Atendidos Hoy -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 mb-4 flex items-center justify-between">
                    <span>Atendidos Hoy en esta Ventanilla</span>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full" x-text="totalAtendidos + ' completados'"></span>
                </h3>
                <div class="divide-y divide-slate-100 text-xs">
                    <template x-for="item in atendidosHoy" :key="item.id">
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="font-mono font-bold text-slate-800 text-sm" x-text="item.codigo"></span>
                                <span class="text-slate-600" x-text="item.ciudadano_nombre"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400" x-text="item.tiempo_atencion_segundos ? `${Math.round(item.tiempo_atencion_segundos/60)} min` : '-'"></span>
                                <span class="px-2 py-0.5 rounded-full font-bold uppercase text-[10px]"
                                      :class="item.estado === 'finalizado' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'"
                                      x-text="item.estado"></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="atendidosHoy.length === 0">
                        <p class="text-slate-400 text-center py-4">Aún no se han registrado atenciones hoy.</p>
                    </template>
                </div>
            </div>
        </div>

        <!-- COLUMNA LATERAL (5 Columnas): Cola de Espera en Vivo -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col h-full">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div>
                        <h3 class="text-base font-bold font-display text-slate-900">Cola de Espera</h3>
                        <p class="text-xs text-slate-500">Ordenada por prioridad preferencial y llegada</p>
                    </div>
                    <span class="text-sm font-black font-display px-3 py-1 rounded-full bg-blue-100 text-blue-700" x-text="colaEspera.length + ' en fila'"></span>
                </div>

                <div class="space-y-2.5 overflow-y-auto max-h-[550px] pr-1">
                    <template x-for="(item, index) in colaEspera" :key="item.id">
                        <div class="p-3.5 rounded-2xl border transition flex items-center justify-between"
                             :class="item.es_preferencial ? 'border-amber-200 bg-amber-50/40' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100/70'">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 text-xs font-bold flex items-center justify-center font-mono" x-text="index + 1"></span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-base font-black font-mono text-slate-900" x-text="item.codigo"></span>
                                        <template x-if="item.es_preferencial">
                                            <span class="text-[9px] font-black uppercase bg-amber-400 text-amber-950 px-1.5 py-0.5 rounded-sm">Pref</span>
                                        </template>
                                    </div>
                                    <p class="text-xs text-slate-600 font-medium truncate max-w-[160px]" x-text="item.ciudadano_nombre"></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-[11px] text-slate-400 block" x-text="item.tramite ? item.tramite.nombre : ''"></span>
                                <span class="text-[10px] text-slate-400 font-mono" x-text="new Date(item.fecha_emision).toLocaleTimeString('es-ES', {hour:'2-digit', minute:'2-digit'})"></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="colaEspera.length === 0">
                        <div class="text-center py-12 text-slate-400">
                            <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <p class="text-sm font-semibold">No hay ciudadanos en espera</p>
                            <p class="text-xs text-slate-400">Los nuevos tickets aparecerán en tiempo real aquí.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Derivación a otra Área -->
    <div x-show="modalDerivarAbierto" style="display: none;" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="modalDerivarAbierto = false" class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Derivar Turno <span x-text="turnoActual ? turnoActual.codigo : ''" class="text-purple-600 font-mono"></span></h3>
                <button @click="modalDerivarAbierto = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>
            <p class="text-xs text-slate-500">El turno será reubicado con <strong>prioridad preferencial</strong> en la cola del área seleccionada.</p>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Área Destino</label>
                <select x-model="areaDestinoId" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    @foreach($areas as $ar)
                        <option value="{{ $ar->id }}">{{ $ar->nombre }} ({{ $ar->codigo }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Motivo / Indicaciones (Opcional)</label>
                <textarea x-model="motivoDerivacion" rows="2" placeholder="Ej. Derivado para firma o pago en caja" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="modalDerivarAbierto = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs">Cancelar</button>
                <button type="button" @click="confirmarDerivacion()" :disabled="cargando" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm">
                    <span>Confirmar Derivación</span>
                </button>
            </div>
        </div>
    </div>
</div>
</x-layouts.app>

@push('scripts')
<script>
    function operadorApp() {
        return {
            ventanillaId: {{ $ventanillaSeleccionada ? $ventanillaSeleccionada->id : 'null' }},
            ventanilla: null,
            turnoActual: null,
            colaEspera: [],
            atendidosHoy: [],
            totalAtendidos: 0,
            cargando: false,
            mensaje: '',
            cronometroSegundos: 0,
            cronometroIntervalo: null,
            modalDerivarAbierto: false,
            areaDestinoId: {{ $areas->first()?->id ?? 'null' }},
            motivoDerivacion: '',

            initApp() {
                if (this.ventanillaId) {
                    this.cargarCola();
                    this.iniciarWebSockets();
                }
            },

            get cronometroFormateado() {
                const mins = String(Math.floor(this.cronometroSegundos / 60)).padStart(2, '0');
                const secs = String(this.cronometroSegundos % 60).padStart(2, '0');
                return `${mins}:${secs}`;
            },

            iniciarCronometro(segundosIniciales = 0) {
                this.detenerCronometro();
                this.cronometroSegundos = segundosIniciales;
                this.cronometroIntervalo = setInterval(() => {
                    this.cronometroSegundos++;
                }, 1000);
            },

            detenerCronometro() {
                if (this.cronometroIntervalo) {
                    clearInterval(this.cronometroIntervalo);
                    this.cronometroIntervalo = null;
                }
            },

            cambiarVentanilla(id) {
                this.ventanillaId = id;
                window.location.href = `{{ route('operador.index') }}?ventanilla_id=${id}`;
            },

            async cargarCola() {
                if (!this.ventanillaId) return;
                try {
                    const res = await fetch(`{{ url('/operador/cola') }}/${this.ventanillaId}`);
                    if (res.ok) {
                        const data = await res.json();
                        this.ventanilla = data.ventanilla;
                        this.turnoActual = data.turno_actual;
                        this.colaEspera = data.cola_espera || [];
                        this.atendidosHoy = data.atendidos_hoy || [];
                        this.totalAtendidos = data.total_atendidos || 0;

                        if (this.turnoActual) {
                            const inicio = this.turnoActual.fecha_inicio_atencion || this.turnoActual.fecha_primer_llamado;
                            const diff = inicio ? Math.floor((new Date() - new Date(inicio)) / 1000) : 0;
                            this.iniciarCronometro(Math.max(0, diff));
                        } else {
                            this.detenerCronometro();
                        }
                    }
                } catch(e) {
                    console.error('Error cargando cola del operador:', e);
                }
            },

            iniciarWebSockets() {
                if (window.Echo) {
                    window.Echo.channel('cola.publica')
                        .listen('.turno.creado', () => this.cargarCola())
                        .listen('.turno.actualizado', () => this.cargarCola())
                        .listen('.turno.llamado', () => this.cargarCola());
                } else {
                    setTimeout(() => this.iniciarWebSockets(), 1000);
                }
            },

            async llamarSiguiente() {
                this.cargando = true;
                try {
                    const res = await fetch(`{{ url('/operador/llamar') }}/${this.ventanillaId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.mensaje = data.message;
                        this.cargarCola();
                    } else {
                        alert(data.message || 'No se pudo llamar al siguiente.');
                    }
                } catch(e) {
                    console.error('Error llamando siguiente:', e);
                } finally {
                    this.cargando = false;
                }
            },

            async rellamarTurno() {
                if (!this.turnoActual) return;
                this.cargando = true;
                try {
                    const res = await fetch(`{{ url('/operador/rellamar') }}/${this.turnoActual.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.mensaje = data.message;
                        this.cargarCola();
                    }
                } catch(e) {
                    console.error('Error re-llamando turno:', e);
                } finally {
                    this.cargando = false;
                }
            },

            async iniciarAtencion() {
                if (!this.turnoActual) return;
                this.cargando = true;
                try {
                    const res = await fetch(`{{ url('/operador/iniciar') }}/${this.turnoActual.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.mensaje = data.message;
                        this.cargarCola();
                    }
                } catch(e) {
                    console.error('Error iniciando atención:', e);
                } finally {
                    this.cargando = false;
                }
            },

            async finalizarAtencion() {
                if (!this.turnoActual) return;
                if (!confirm(`¿Confirmas finalizar la atención del turno ${this.turnoActual.codigo}?`)) return;

                this.cargando = true;
                try {
                    const res = await fetch(`{{ url('/operador/finalizar') }}/${this.turnoActual.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.mensaje = data.message;
                        this.detenerCronometro();
                        this.cargarCola();
                    }
                } catch(e) {
                    console.error('Error finalizando atención:', e);
                } finally {
                    this.cargando = false;
                }
            },

            async marcarAusente() {
                if (!this.turnoActual) return;
                if (!confirm(`¿Marcar al ciudadano del turno ${this.turnoActual.codigo} como AUSENTE?`)) return;

                this.cargando = true;
                try {
                    const res = await fetch(`{{ url('/operador/ausente') }}/${this.turnoActual.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.mensaje = data.message;
                        this.detenerCronometro();
                        this.cargarCola();
                    }
                } catch(e) {
                    console.error('Error marcando ausente:', e);
                } finally {
                    this.cargando = false;
                }
            },

            abrirModalDerivar() {
                if (!this.turnoActual) return;
                this.modalDerivarAbierto = true;
            },

            async confirmarDerivacion() {
                if (!this.turnoActual || !this.areaDestinoId) return;

                this.cargando = true;
                try {
                    const res = await fetch(`{{ url('/operador/transferir') }}/${this.turnoActual.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            nueva_area_id: this.areaDestinoId,
                            motivo: this.motivoDerivacion
                        })
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.mensaje = data.message;
                        this.modalDerivarAbierto = false;
                        this.motivoDerivacion = '';
                        this.detenerCronometro();
                        this.cargarCola();
                    } else {
                        alert(data.message || 'No se pudo derivar el turno.');
                    }
                } catch(e) {
                    console.error('Error derivando turno:', e);
                } finally {
                    this.cargando = false;
                }
            }
        };
    }
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

