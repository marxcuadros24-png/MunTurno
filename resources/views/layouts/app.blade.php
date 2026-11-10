<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MunTurno') }} - @yield('title', 'Gestión de Turnos Virtuales')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .font-display {
            font-family: 'Space Grotesk', sans-serif;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 20px rgba(37, 99, 235, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }
        .pulse-call {
            animation: pulse-ring 2s infinite;
        }
    </style>

    @stack('styles')
</head>
<body class="h-full flex flex-col text-slate-800 antialiased selection:bg-blue-600 selection:text-white">
    <!-- Barra Superior de Navegación -->
    @unless(View::hasSection('hide_nav'))
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logotipo / Identidad -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-700 via-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-display font-bold text-xl tracking-tight text-slate-900">Mun<span class="text-blue-600">Turno</span></span>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">En Vivo</span>
                        </div>
                        <p class="text-xs text-slate-500 hidden sm:block">Atención al Ciudadano en Tiempo Real</p>
                    </div>
                </a>

                <!-- Enlaces Rápidos a Módulos -->
                <nav class="flex items-center gap-1 sm:gap-2 text-sm font-medium">
                    <a href="{{ route('kiosco.index') }}" class="px-3 py-2 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-slate-100 transition {{ request()->routeIs('kiosco.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            <span>Kiosco</span>
                        </span>
                    </a>
                    <a href="{{ route('tv.index') }}" target="_blank" class="px-3 py-2 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-slate-100 transition {{ request()->routeIs('tv.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            <span>Pantalla TV</span>
                        </span>
                    </a>
                    <a href="{{ route('operador.index') }}" class="px-3 py-2 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-slate-100 transition {{ request()->routeIs('operador.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            <span>Operador</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.index') }}" class="px-3 py-2 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-slate-100 transition {{ request()->routeIs('admin.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            <span>Dashboard</span>
                        </span>
                    </a>
                </nav>
            </div>
        </div>
    </header>
    @endunless

    <!-- Contenido Principal -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer Municipal -->
    @unless(View::hasSection('hide_footer'))
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                <span>Servidor WebSockets Reverb conectado a <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-700">munturno_db</code></span>
            </div>
            <p>© {{ date('Y') }} Sistema Municipal de Atención Ciudadana - MunTurno. Todos los derechos reservados.</p>
        </div>
    </footer>
    @endunless

    <!-- Sintetizador de Audio y Voz para Llamados -->
    <script>
        window.MunAudio = {
            ctx: null,
            init() {
                if (!this.ctx) {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (AudioContext) {
                        this.ctx = new AudioContext();
                    }
                }
                if (this.ctx && this.ctx.state === 'suspended') {
                    this.ctx.resume();
                }
            },
            // Emite un sonido de timbre / chime armónico (dos notas de campana)
            playChime() {
                try {
                    this.init();
                    if (!this.ctx) return;

                    const now = this.ctx.currentTime;
                    
                    // Nota 1: Sol 5 (784Hz)
                    const osc1 = this.ctx.createOscillator();
                    const gain1 = this.ctx.createGain();
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(783.99, now);
                    gain1.gain.setValueAtTime(0.3, now);
                    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.8);
                    osc1.connect(gain1);
                    gain1.connect(this.ctx.destination);
                    osc1.start(now);
                    osc1.stop(now + 0.8);

                    // Nota 2: Do 6 (1046Hz) a los 0.25s
                    const osc2 = this.ctx.createOscillator();
                    const gain2 = this.ctx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(1046.50, now + 0.25);
                    gain2.gain.setValueAtTime(0.35, now + 0.25);
                    gain2.gain.exponentialRampToValueAtTime(0.001, now + 1.2);
                    osc2.connect(gain2);
                    gain2.connect(this.ctx.destination);
                    osc2.start(now + 0.25);
                    osc2.stop(now + 1.2);
                } catch(e) {
                    console.warn('Audio chime falló:', e);
                }
            },
            // Voz sintetizada en español con Web Speech API
            speak(text) {
                if (!('speechSynthesis' in window)) return;
                
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'es-ES';
                utterance.rate = 0.95;
                utterance.pitch = 1.05;

                // Buscar voz en español disponible
                const voices = window.speechSynthesis.getVoices();
                const esVoice = voices.find(v => v.lang.startsWith('es'));
                if (esVoice) utterance.voice = esVoice;

                window.speechSynthesis.speak(utterance);
            },
            // Campanilla + voz
            llamar(codigo, ventanilla) {
                this.playChime();
                setTimeout(() => {
                    this.speak(`Turno ${codigo}, acercarse a ${ventanilla}`);
                }, 800);
            }
        };

        // Inicializar audio al primer toque/click en la pantalla
        window.addEventListener('click', () => window.MunAudio.init(), { once: true });
    </script>

    @stack('scripts')
</body>
</html>

