<x-layouts.app :title="'Mi Turno ' . $turno->codigo . ' en Vivo'">
    <x-slot:styles>
        <style>
            @keyframes alert-vibrate {
                0%, 100% { transform: translateY(0); }
                25% { transform: translateY(-4px); }
                75% { transform: translateY(4px); }
            }
            .called-animation {
                animation: alert-vibrate 0.6s infinite ease-in-out;
            }
        </style>
    </x-slot:styles>

    <div class="max-w-md mx-auto px-4 py-8" x-data="ciudadanoApp()" x-init="initApp()">
    <!-- Tarjeta Principal de Seguimiento Móvil -->
    <div class="rounded-3xl border-2 transition-all duration-300 shadow-xl overflow-hidden"
         :class="{
            'bg-white border-blue-200': turno.estado === 'espera',
            'bg-emerald-600 border-emerald-400 text-white called-animation shadow-emerald-500/30': turno.estado === 'llamado',
            'bg-indigo-900 border-indigo-700 text-white': turno.estado === 'atendiendo',
            'bg-slate-100 border-slate-300': turno.estado === 'finalizado' || turno.estado === 'ausente'
         }">

        <!-- Banner de Estado Superior -->
        <div class="p-4 text-center text-xs font-bold uppercase tracking-wider border-b"
             :class="{
                'bg-blue-50 text-blue-700 border-blue-100': turno.estado === 'espera',
                'bg-emerald-700 text-white border-emerald-500': turno.estado === 'llamado',
                'bg-indigo-950 text-indigo-300 border-indigo-800': turno.estado === 'atendiendo',
                'bg-slate-200 text-slate-600 border-slate-300': turno.estado === 'finalizado' || turno.estado === 'ausente'
             }">
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full"
                      :class="turno.estado === 'llamado' ? 'bg-amber-300 animate-ping' : (turno.estado === 'espera' ? 'bg-blue-500 animate-pulse' : 'bg-slate-400')"></span>
                <span x-text="estadoTexto"></span>
            </span>
        </div>

        <!-- Contenido Central -->
        <div class="p-6 sm:p-8 text-center space-y-6">
            <!-- Área y Trámite -->
            <div>
                <span class="text-xs font-bold uppercase tracking-wider opacity-75" x-text="turno.area ? turno.area.nombre : ''"></span>
                <h2 class="text-sm font-semibold opacity-90 mt-0.5" x-text="turno.tramite ? turno.tramite.nombre : ''"></h2>
            </div>

            <!-- Código Grande -->
            <div class="py-2">
                <span class="text-xs uppercase tracking-widest font-bold opacity-60">Tu Código de Turno</span>
                <div class="text-6xl sm:text-7xl font-black font-display tracking-tight my-1" x-text="turno.codigo">
                    {{ $turno->codigo }}
                </div>
                <p class="text-xs opacity-75" x-text="turno.ciudadano_nombre">{{ $turno->ciudadano_nombre }}</p>
            </div>

            <!-- CASO 1: EN ESPERA -->
            <template x-if="turno.estado === 'espera'">
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-5 text-slate-800 space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block">Posición en la Fila</span>
                    <div class="text-5xl font-black font-display text-blue-600" x-text="personasAdelante">
                        {{ $personasAdelante }}
                    </div>
                    <p class="text-xs text-slate-500 font-medium">
                        <span x-show="personasAdelante === 0" class="text-emerald-600 font-bold">¡Eres el siguiente en ser llamado!</span>
                        <span x-show="personasAdelante > 0">personas por delante de ti en esta área.</span>
                    </p>
                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-center gap-2 text-xs text-slate-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Actualizándose en tiempo real</span>
                    </div>
                </div>
            </template>

            <!-- CASO 2: LLAMADO A VENTANILLA (¡ATENCIÓN INMEDIATA!) -->
            <template x-if="turno.estado === 'llamado'">
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 text-white space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400 text-amber-950 font-black text-xs uppercase tracking-wider animate-bounce">
                        🔔 ¡ACÉRCATE DE INMEDIATO!
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-wider opacity-85 block">Diríjase a:</span>
                        <div class="text-4xl sm:text-5xl font-black font-display text-white mt-1" x-text="turno.ventanilla ? turno.ventanilla.numero : 'Ventanilla'"></div>
                        <p class="text-sm font-semibold opacity-90 mt-1" x-text="turno.ventanilla ? turno.ventanilla.ubicacion : 'Hall Principal'"></p>
                    </div>
                </div>
            </template>

            <!-- CASO 3: EN ATENCIÓN -->
            <template x-if="turno.estado === 'atendiendo'">
                <div class="bg-indigo-950/60 rounded-2xl p-5 border border-indigo-700/60 text-white space-y-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-300">En Atención en</span>
                    <div class="text-2xl font-bold font-display" x-text="turno.ventanilla ? turno.ventanilla.numero : 'Ventanilla'"></div>
                    <p class="text-xs text-indigo-200">Tu trámite está siendo procesado por el servidor público asignado.</p>
                </div>
            </template>

            <!-- CASO 4: FINALIZADO / AUSENTE -->
            <template x-if="turno.estado === 'finalizado'">
                <div class="bg-white rounded-2xl p-5 border border-slate-200 text-slate-800 space-y-3">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h4 class="font-bold text-base text-slate-900">¡Atención Finalizada!</h4>
                    <p class="text-xs text-slate-500">Gracias por acudir a la Municipalidad. ¿Cómo calificarías la atención recibida?</p>
                    <div class="flex justify-center gap-2 pt-2 text-2xl text-amber-400 cursor-pointer">
                        <span>⭐</span><span>⭐</span><span>⭐</span><span>⭐</span><span>⭐</span>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer Informativo -->
        <div class="p-4 text-center text-xs opacity-75 border-t border-black/10">
            Mantén esta pantalla abierta. Te avisaremos con sonido y vibración cuando te toque.
        </div>
    </div>
</div>

<x-slot:scripts>
<script>
    function ciudadanoApp() {
        return {
            turno: {
                id: {{ $turno->id }},
                codigo: @js($turno->codigo),
                estado: @js($turno->estado),
                ciudadano_nombre: @js($turno->ciudadano_nombre),
                area: {
                    nombre: @js($turno->area->nombre),
                    color: @js($turno->area->color)
                },
                tramite: {
                    nombre: @js($turno->tramite->nombre)
                },
                ventanilla: @json($turno->ventanilla)
            },
            personasAdelante: {{ $personasAdelante }},

            get estadoTexto() {
                switch(this.turno.estado) {
                    case 'espera': return 'En Fila de Espera';
                    case 'llamado': return '¡ES TU TURNO! ACÉRCATE AHORA';
                    case 'atendiendo': return 'En Atención Presencial';
                    case 'finalizado': return 'Atención Concluida';
                    case 'ausente': return 'Marcado como Ausente';
                    default: return this.turno.estado;
                }
            },

            initApp() {
                this.iniciarWebSockets();
                // Sondeo continuo cada 3s sin caché como respaldo
                setInterval(() => this.verificarEstado(), 3000);
            },

            iniciarWebSockets() {
                if (window.Echo) {
                    // Escucha en el canal personal del turno
                    window.Echo.channel(`turno.${this.turno.id}`)
                        .listen('.turno.llamado', (e) => {
                            console.log('📢 Mi Turno llamado:', e);
                            this.turno.estado = 'llamado';
                            this.turno.ventanilla = e.data.ventanilla;
                            this.alertarCiudadano();
                        })
                        .listen('.turno.actualizado', (e) => {
                            console.log('🔄 Mi Turno actualizado:', e);
                            this.turno.estado = e.data.estado;
                        });

                    // Escucha en la cola general para actualizar la posición en fila
                    window.Echo.channel('cola.publica')
                        .listen('.turno.llamado', () => this.verificarEstado())
                        .listen('.turno.actualizado', () => this.verificarEstado());
                } else {
                    setTimeout(() => this.iniciarWebSockets(), 1000);
                }
            },

            async verificarEstado() {
                try {
                    const res = await fetch(`{{ route('ciudadano.estado', $turno->codigo) }}?_=${Date.now()}`, {
                        cache: 'no-store'
                    });
                    if (res.ok) {
                        const data = await res.json();
                        const estadoAnterior = this.turno.estado;
                        this.turno.estado = data.estado;
                        this.personasAdelante = data.personas_adelante;
                        if (data.ventanilla_numero) {
                            this.turno.ventanilla = {
                                numero: data.ventanilla_numero,
                                ubicacion: data.ventanilla_ubicacion
                            };
                        }
                        if (estadoAnterior !== 'llamado' && data.estado === 'llamado') {
                            this.alertarCiudadano();
                        }
                    }
                } catch(e) {
                    console.error('Error verificando estado del turno:', e);
                }
            },

            alertarCiudadano() {
                // Vibración móvil si está soportada por el navegador
                if ('vibrate' in navigator) {
                    navigator.vibrate([400, 200, 400, 200, 600]);
                }
                // Timbre y voz
                const vent = this.turno.ventanilla ? this.turno.ventanilla.numero : 'Ventanilla';
                if (window.MunAudio) {
                    window.MunAudio.llamar(this.turno.codigo, vent);
                }
            }
        };
    }
</script>
</x-slot:scripts>
</x-layouts.app>

