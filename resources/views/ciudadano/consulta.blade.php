<x-layouts.app :title="'Consultar mi Turno por Documento'">
    <div class="max-w-md mx-auto px-4 py-12">
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xl text-center space-y-6">
            <div class="w-16 h-16 rounded-2xl bg-blue-100 text-blue-600 mx-auto flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <div>
                <h1 class="text-2xl font-black font-display text-slate-900">Buscar mi Turno</h1>
                <p class="text-xs text-slate-500 mt-1">Ingresa tu número de documento de identidad para ver el estado de tu turno en tiempo real.</p>
            </div>

            @if(session('error'))
                <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold text-left">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('ciudadano.buscar') }}" method="POST" class="space-y-4 text-left">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Número de DNI / Documento</label>
                    <input type="text" name="documento" required autofocus placeholder="Ej. 74839201"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 text-base font-semibold focus:ring-2 focus:ring-blue-500">
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <span>Consultar Estado</span>
                </button>
            </form>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                <a href="{{ route('kiosco.index') }}" class="text-blue-600 hover:underline">¿No tienes turno? Solicitar uno</a>
                <a href="{{ route('home') }}" class="text-slate-400 hover:text-slate-600">Volver al inicio</a>
            </div>
        </div>
    </div>
</x-layouts.app>
