<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simulation Tutoria - Demo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased" x-data="{ view: 'alumno' }">

    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="bg-indigo-600 text-white p-1.5 rounded-lg shadow-indigo-200 shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.253.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.253.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <span class="font-bold text-xl tracking-tight text-indigo-950">Simulation Tutoria</span>
            </div>

            <nav class="flex items-center gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">Panel de Control</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition">Entrar</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-5 py-2 rounded-full text-sm font-bold hover:bg-indigo-700 transition shadow-md shadow-indigo-100">Registrarse</a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-12">
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 mb-6">Gestiona tu futuro académico</h1>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">Explora cómo nuestra plataforma ayuda a estudiantes y profesores a mantener el éxito educativo a través del seguimiento en tiempo real.</p>
        </div>

        <div class="flex justify-center mb-8">
            <div class="bg-slate-200 p-1 rounded-xl inline-flex shadow-inner">
                <button @click="view = 'alumno'" :class="view === 'alumno' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-600 hover:text-slate-900'" class="px-6 py-2 rounded-lg text-sm font-bold transition-all duration-200">Vista del Alumno</button>
                <button @click="view = 'tutor'" :class="view === 'tutor' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-600 hover:text-slate-900'" class="px-6 py-2 rounded-lg text-sm font-bold transition-all duration-200">Vista del Tutor</button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden min-h-[500px]">
            
            <template x-if="view === 'alumno'">
                <div class="animate-in fade-in duration-500">
                    <div class="bg-indigo-50 border-b border-indigo-100 p-6 flex justify-between items-center">
                        <h2 class="text-xl font-bold text-indigo-900 font-medium">Panel de Alumno</h2>
                        <div class="flex gap-2">
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">Promedio: 9.2</span>
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">5to Semestre</span>
                        </div>
                    </div>
                    <div class="p-6 grid md:grid-cols-3 gap-6">
                        <div class="md:col-span-2 space-y-4">
                            <h3 class="font-bold text-slate-700">Mis Materias del Periodo</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="border border-slate-100 p-4 rounded-xl bg-white shadow-sm">
                                    <p class="text-xs text-slate-500 font-bold uppercase">Software II</p>
                                    <p class="text-2xl font-bold text-indigo-600">95</p>
                                </div>
                                <div class="border border-slate-100 p-4 rounded-xl bg-white shadow-sm">
                                    <p class="text-xs text-slate-500 font-bold uppercase">Bases de Datos</p>
                                    <p class="text-2xl font-bold text-indigo-600">88</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 p-6 rounded-xl border border-slate-100">
                            <h3 class="font-bold text-slate-700 mb-4">Avisos</h3>
                            <div class="flex items-start gap-3 p-3 bg-amber-50 border-l-4 border-amber-400 rounded">
                                <p class="text-xs text-amber-800 italic">"Tu tutor Brando ha enviado un nuevo mensaje sobre tus prácticas profesionales."</p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="view === 'tutor'">
                <div class="animate-in fade-in duration-500">
                    <div class="bg-slate-900 text-white p-6 flex justify-between items-center">
                        <h2 class="text-xl font-bold font-medium">Panel de Tutor</h2>
                        <span class="bg-indigo-500/20 text-indigo-200 px-3 py-1 rounded-full text-xs font-bold">Tutor: Brando Paredes</span>
                    </div>
                    <div class="p-6">
                        <h3 class="font-bold text-slate-700 mb-6">Lista de Alumnos Asignados</h3>
                        <div class="overflow-hidden border border-slate-100 rounded-xl">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-widest font-bold">
                                    <tr>
                                        <th class="p-4">Nombre del Estudiante</th>
                                        <th class="p-4">Estado</th>
                                        <th class="p-4">Alertas Pendientes</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr>
                                        <td class="p-4 font-semibold text-slate-700 italic">Axel Paredes</td>
                                        <td class="p-4"><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-[11px] font-bold">Regular</span></td>
                                        <td class="p-4 text-slate-400 italic">Ninguna</td>
                                    </tr>
                                    <tr class="bg-red-50/30">
                                        <td class="p-4 font-semibold text-slate-700 italic">Zoe Paredes</td>
                                        <td class="p-4"><span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-[11px] font-bold">En Riesgo</span></td>
                                        <td class="p-4 text-red-600 font-bold">2 - Baja Asistencia</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </template>

        </div>
    </main>

    <footer class="text-center py-8 text-slate-400 text-xs font-medium">
        &copy; {{ date('Y') }} Simulation Tutoria - Panel Académico Público
    </footer>

</body>
</html>