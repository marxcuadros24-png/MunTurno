<x-layouts.app :title="'Pantalla de Sala de Espera - Turnero TV'" :hide-nav="true" :hide-footer="true">
    <x-slot:styles>
        <style>
            @keyframes pulse-call-tv {
                0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7); }
                50% { transform: scale(1.02); box-shadow: 0 0 50px 10px rgba(37, 99, 235, 0.4); }
                100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
            }
            .tv-flash {
                animation: pulse-call-tv 1.5s 4 ease-in-out;
            }
        </style>
    </x-slot:styles>

    <div class="h-screen w-screen bg-slate-950 text-white flex flex-col overflow-hidden select-none" id="tvContainer" x-data="tvApp()" x-init="initApp()">
        <!-- Header TV: Identidad Municipal + Reloj en Vivo -->
        <header class="bg-slate-900/90 border-b border-slate-800 px-6 py-4 flex items-center justify-between shrink-0 shadow-lg">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-black font-display tracking-tight text-white">MUNICIPALIDAD</h1>
                        <span class="text-xs font-bold uppercase tracking-widest bg-blue-500/20 text-blue-400 px-2.5 py-0.5 rounded-full border border-blue-500/30">Sala de Atención</span>
                    </div>
                    <p class="text-xs text-slate-400">Atención al Ciudadano y Gestión de Turnos en Tiempo Real</p>
                </div>
            </div>

            <!-- Reloj Digital y Estado de Conexión -->
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-800 border border-slate-700/60 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full" :class="conectado ? 'bg-emerald-500 animate-pulse' : 'bg-amber-400 animate-pulse'"></span>
                    <span class="text-slate-300 font-mono" x-text="conectado ? 'WebSockets En Vivo' : 'Sondeo En Vivo (2s)'"></span>
                </div>

                <!-- Botón de Audio y Fullscreen -->
                <div class="flex items-center gap-2">
                    <button @click="testAudio()" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition" title="Probar audio">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" /></svg>
                    </button>
                    <button @click="toggleFullscreen()" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition" title="Pantalla Completa">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
                    </button>
                </div>

                <!-- Reloj -->
                <div class="text-right">
                    <div class="text-3xl font-black font-display tracking-tight text-white" x-text="relojHora">--:--:--</div>
                    <div class="text-xs text-slate-400 capitalize" x-text="relojFecha">--</div>
                </div>
            </div>
        </header>

        <!-- Contenido Principal Dividido: Turno Destacado a la Izquierda + Historial a la Derecha -->
        <div class="flex-1 grid grid-cols-12 gap-6 p-6 min-h-0">
            <!-- COLUMNA IZQUIERDA (7 Columnas): Turno Protagonista en Llamada O Turnos en Atención Activa -->
            <div class="col-span-7 flex flex-col justify-between min-h-0">
                <!-- CASO A: HAY UN TURNO EN LLAMADO RECIENTE -->
                <template x-if="turnoActual && turnoActual.estado === 'llamado'">
                    <div class="h-full flex flex-col justify-between bg-gradient-to-b from-slate-900 to-slate-900/90 rounded-3xl border-2 border-blue-500/40 p-8 shadow-2xl relative overflow-hidden"
                         :class="animarLlamado ? 'tv-flash border-blue-400' : ''">
                        <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full opacity-10 blur-3xl"
                             :style="`background-color: ${turnoActual.area ? turnoActual.area.color : '#3b82f6'}`"></div>

                        <!-- Encabezado de la Tarjeta del Turno -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="w-3.5 h-3.5 rounded-full bg-blue-500 animate-ping"></span>
                                <span class="text-lg font-black uppercase tracking-widest text-blue-400 font-display">TURNO LLAMADO</span>
                            </div>
                            <template x-if="turnoActual.es_preferencial">
                                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-amber-400 text-amber-950 font-black text-sm uppercase tracking-wider shadow-md">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                                    <span>Atención Preferencial</span>
                                </span>
                            </template>
                        </div>

                        <!-- Código Gigante del Turno -->
                        <div class="text-center my-auto py-6">
                            <div class="text-8xl lg:text-9xl font-black font-display tracking-tight text-white drop-shadow-md"
                                 x-text="turnoActual.codigo">
                                ---
                            </div>
                            <div class="text-2xl font-bold text-slate-300 mt-2" x-text="turnoActual.ciudadano_nombre">
                                ---
                            </div>
                        </div>

                        <!-- Ventanilla y Destino en Grande -->
                        <div class="p-6 rounded-2xl bg-slate-800/80 border border-slate-700/80 flex items-center justify-between shadow-inner">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-lg">
                                    <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Dirigirse a:</span>
                                    <h2 class="text-4xl font-black font-display text-emerald-400" x-text="turnoActual.ventanilla ? turnoActual.ventanilla.numero : 'Ventanilla'">---</h2>
                                    <p class="text-xs text-slate-400" x-text="turnoActual.ventanilla ? turnoActual.ventanilla.ubicacion : 'Hall Principal'"></p>
                                </div>
                            </div>

                            <!-- Área y Trámite -->
                            <div class="text-right">
                                <span class="px-3 py-1 rounded-md text-xs font-bold uppercase"
                                      :style="`background-color: ${turnoActual.area ? turnoActual.area.color + '33' : '#2563eb33'}; color: ${turnoActual.area ? turnoActual.area.color : '#60a5fa'}`"
                                      x-text="turnoActual.area ? turnoActual.area.nombre : ''">
                                </span>
                                <p class="text-sm text-slate-300 font-semibold mt-1" x-text="turnoActual.tramite ? turnoActual.tramite.nombre : ''"></p>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- CASO B: NO HAY LLAMADOS POR ATENDER, PERO SÍ HAY TURNOS EN ATENCIÓN EN VENTANILLAS -->
                <template x-if="(!turnoActual || turnoActual.estado !== 'llamado') && turnosEnAtencion.length > 0">
                    <div class="h-full flex flex-col justify-between bg-gradient-to-b from-slate-900 to-slate-900/90 rounded-3xl border-2 border-emerald-500/40 p-8 shadow-2xl overflow-hidden">
                        <!-- Cabecera de Módulos en Atención -->
                        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                            <div class="flex items-center gap-3">
                                <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-lg font-black uppercase tracking-widest text-emerald-400 font-display">MÓDULOS EN ATENCIÓN</span>
                            </div>
                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30"
                                  x-text="turnosEnAtencion.length + ' ventanillas atendiendo'"></span>
                        </div>

                        <!-- Cuadrícula de Turnos Atendiéndose Actualmente -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-auto py-4 overflow-y-auto max-h-[460px]">
                            <template x-for="item in turnosEnAtencion" :key="item.id">
                                <div class="p-5 rounded-2xl bg-slate-800/80 border border-emerald-500/30 flex items-center justify-between shadow-lg">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 font-display" x-text="item.ventanilla ? item.ventanilla.numero : 'Ventanilla'"></span>
                                        <div class="text-4xl font-black font-display text-white mt-1" x-text="item.codigo"></div>
                                        <p class="text-xs text-slate-300 truncate max-w-[180px] mt-0.5" x-text="item.ciudadano_nombre"></p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full bg-emerald-950 text-emerald-300 border border-emerald-500/40 flex items-center gap-1.5 justify-end">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                            <span>En Atención</span>
                                        </span>
                                        <p class="text-xs text-slate-400 mt-2 truncate max-w-[140px]" x-text="item.area ? item.area.nombre : ''"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Footer Informativo -->
                        <div class="p-4 rounded-xl bg-slate-800/60 border border-slate-700/60 flex items-center justify-between text-xs text-slate-400">
                            <span>Próximo llamado se anunciará por altavoz automáticamente.</span>
                            <span class="text-slate-300 font-semibold" x-text="totalEspera + ' ciudadanos en espera'"></span>
                        </div>
                    </div>
                </template>

                <!-- CASO C: SALA EN ESPERA TOTALMENTE DESPEJADA (Ni llamados ni atenciones) -->
                <template x-if="(!turnoActual || turnoActual.estado !== 'llamado') && turnosEnAtencion.length === 0">
                    <div class="h-full flex flex-col items-center justify-center bg-slate-900 rounded-3xl border border-slate-800 p-12 text-center">
                        <div class="w-20 h-20 rounded-full bg-slate-800 flex items-center justify-center text-slate-500 mb-4 animate-pulse">
                            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold font-display text-slate-300">Sala en Espera</h3>
                        <p class="text-sm text-slate-500 max-w-sm mt-2">Los turnos llamados por las ventanillas aparecerán automáticamente aquí con aviso sonoro y voz.</p>
                    </div>
                </template>
            </div>

            <!-- COLUMNA DERECHA (5 Columnas): Últimos Turnos Llamados + En Espera -->
            <div class="col-span-5 flex flex-col gap-6 min-h-0">
                <!-- Últimos Llamados -->
                <div class="flex-1 min-h-0 bg-slate-900/90 rounded-3xl border border-slate-800 p-6 flex flex-col shadow-lg overflow-hidden">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-4">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Últimos Turnos Llamados</span>
                        </h3>
                        <span class="text-xs text-slate-500 font-mono" x-text="historial.length + ' registrados'"></span>
                    </div>

                    <div class="space-y-3 overflow-y-auto flex-1 pr-1">
                        <template x-for="(item, index) in historial" :key="item.id">
                            <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/60 flex items-center justify-between transition hover:bg-slate-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm text-white"
                                         :style="`background-color: ${item.area ? item.area.color : '#3b82f6'}`"
                                         x-text="item.area ? item.area.codigo : 'T'">
                                    </div>
                                    <div>
                                        <span class="text-2xl font-black font-display text-white" x-text="item.codigo"></span>
                                        <p class="text-xs text-slate-400 truncate max-w-[140px]" x-text="item.ciudadano_nombre"></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-lg font-bold font-display text-emerald-400" x-text="item.ventanilla ? item.ventanilla.numero : 'Ventanilla'"></span>
                                    <p class="text-[10px] text-slate-400" x-text="item.area ? item.area.nombre : ''"></p>
                                </div>
                            </div>
                        </template>
                        <template x-if="historial.length === 0">
                            <p class="text-center text-xs text-slate-500 py-6">Sin historial reciente de llamados</p>
                        </template>
                    </div>
                </div>

                <!-- Próximos en Espera -->
                <div class="h-44 bg-slate-900/90 rounded-3xl border border-slate-800 p-5 flex flex-col shadow-lg overflow-hidden">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800 mb-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Próximos en Fila de Espera</h4>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-400" x-text="totalEspera + ' en cola'"></span>
                    </div>
                    <div class="flex flex-wrap gap-2 overflow-y-auto">
                        <template x-for="item in enEspera" :key="item.id">
                            <div class="px-3 py-1.5 rounded-xl bg-slate-800 border border-slate-700 text-xs font-bold flex items-center gap-2">
                                <span class="text-white font-mono" x-text="item.codigo"></span>
                                <template x-if="item.es_preferencial">
                                    <span class="text-[9px] bg-amber-400 text-amber-950 px-1 rounded font-black">P</span>
                                </template>
                            </div>
                        </template>
                        <template x-if="enEspera.length === 0">
                            <p class="text-xs text-slate-500 py-2">No hay ciudadanos en espera actualmente.</p>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticker / Cinta Informativa Inferior -->
        <footer class="bg-blue-950/80 border-t border-blue-900/60 px-6 py-2.5 flex items-center gap-4 shrink-0 text-xs text-blue-200">
            <span class="font-bold uppercase tracking-wider text-white bg-blue-600 px-2.5 py-0.5 rounded-md shrink-0">AVISOS MUNICIPALES</span>
            <marquee behavior="scroll" direction="left" scrollamount="6" class="font-medium text-slate-300">
                🏛️ Municipalidad al servicio del ciudadano • Recuerde tener a la mano su DNI o documento de identidad original para ser atendido • Horario de atención continuo de Lunes a Viernes de 8:00 AM a 5:00 PM • Pagos de arbitrios con hasta 20% de descuento en Rentas •
            </marquee>
        </footer>
    </div>

    <x-slot:scripts>
        <script>
            function tvApp() {
                return {
                    turnoActual: null,
                    turnosEnAtencion: [],
                    historial: [],
                    enEspera: [],
                    totalEspera: 0,
                    conectado: false,
                    animarLlamado: false,
                    relojHora: '',
                    relojFecha: '',
                    primeraCarga: true,
                    ultimoAnuncioFirma: null,
                    echoInicializado: false,

                    initApp() {
                        this.actualizarReloj();
                        setInterval(() => this.actualizarReloj(), 1000);

                        this.cargarEstado();
                        // Sondeo continuo cada 2 segundos sin caché como respaldo en tiempo real
                        setInterval(() => this.cargarEstado(), 2000);

                        this.iniciarWebSockets();
                    },

                    actualizarReloj() {
                        const ahora = new Date();
                        this.relojHora = ahora.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        this.relojFecha = ahora.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                    },

                    async cargarEstado() {
                        try {
                            const res = await fetch(`{{ route('tv.estado') }}?_=${Date.now()}`, {
                                cache: 'no-store',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (res.ok) {
                                const data = await res.json();
                                const nuevoLlamado = data.ultimo_llamado;

                                this.turnoActual = nuevoLlamado;
                                this.turnosEnAtencion = data.turnos_en_atencion || [];
                                this.historial = data.historial || [];
                                this.enEspera = data.en_espera || [];
                                this.totalEspera = data.total_espera || 0;

                                // Detectar nuevos llamados o re-llamados también durante el sondeo HTTP
                                if (nuevoLlamado && nuevoLlamado.estado === 'llamado') {
                                    const firma = `${nuevoLlamado.id}:${nuevoLlamado.llamadas_contador || 1}`;
                                    if (!this.primeraCarga && this.ultimoAnuncioFirma !== firma) {
                                        this.ultimoAnuncioFirma = firma;
                                        this.dispararAlertaVisualYSonora(nuevoLlamado);
                                    } else if (this.primeraCarga) {
                                        this.ultimoAnuncioFirma = firma;
                                    }
                                }

                                this.primeraCarga = false;
                            }
                        } catch(e) {
                            console.error('Error cargando estado de TV:', e);
                        }
                    },

                    iniciarWebSockets() {
                        if (this.echoInicializado) return;

                        if (window.Echo) {
                            this.echoInicializado = true;
                            console.log('Suscribiendo TV al canal cola.publica...');

                            // Monitorear estado real de conexión WebSocket (Pusher/Reverb)
                            const pusherConn = window.Echo.connector?.pusher?.connection;
                            if (pusherConn) {
                                this.conectado = pusherConn.state === 'connected';
                                pusherConn.bind('state_change', (states) => {
                                    this.conectado = states.current === 'connected';
                                });
                            }

                            window.Echo.channel('cola.publica')
                                .listen('.turno.llamado', (e) => {
                                    console.log('📢 WebSockets Evento TurnoLlamado recibido:', e);
                                    this.procesarLlamado(e.data);
                                })
                                .listen('.turno.creado', (e) => {
                                    console.log('✨ WebSockets Evento TurnoCreado recibido:', e);
                                    this.cargarEstado();
                                })
                                .listen('.turno.actualizado', (e) => {
                                    console.log('🔄 WebSockets Evento TurnoActualizado recibido:', e);
                                    this.cargarEstado();
                                });
                        } else {
                            setTimeout(() => this.iniciarWebSockets(), 1000);
                        }
                    },

                    dispararAlertaVisualYSonora(data) {
                        this.animarLlamado = true;
                        const ventanillaNombre = data.ventanilla ? data.ventanilla.numero : 'Ventanilla';
                        if (window.MunAudio) {
                            window.MunAudio.llamar(data.codigo, ventanillaNombre);
                        }
                        setTimeout(() => {
                            this.animarLlamado = false;
                        }, 6000);
                    },

                    procesarLlamado(data) {
                        this.turnoActual = data;
                        this.primeraCarga = false;

                        const firma = `${data.id}:${data.llamadas_contador || 1}`;
                        if (this.ultimoAnuncioFirma !== firma) {
                            this.ultimoAnuncioFirma = firma;
                            this.dispararAlertaVisualYSonora(data);
                        }

                        this.cargarEstado();
                    },

                    testAudio() {
                        if (window.MunAudio) {
                            window.MunAudio.llamar('A-001', 'Ventanilla 01');
                        }
                    },

                    toggleFullscreen() {
                        const elem = document.getElementById('tvContainer');
                        if (!document.fullscreenElement) {
                            elem.requestFullscreen().catch(err => alert(`Error al activar pantalla completa: ${err.message}`));
                        } else {
                            document.exitFullscreen();
                        }
                    }
                };
            }
        </script>
    </x-slot:scripts>
</x-layouts.app>
