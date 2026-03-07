<script>
    import { useForm } from '@inertiajs/svelte';
    import VeltoryLogo from '../../Components/VeltoryLogo.svelte';

    let { errors = {} } = $props();

    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit(e) {
        e.preventDefault();
        $form.post('/login');
    }
</script>

<svelte:head>
    <title>Iniciar sesión</title>
</svelte:head>

<div class="min-h-screen bg-slate-950 flex">
    <!-- Panel izquierdo — branding -->
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 flex-col justify-between p-12 bg-slate-900 border-r border-slate-800 relative overflow-hidden">
        <!-- Fondo decorativo -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-[-10%] right-[-10%] w-96 h-96 rounded-full bg-indigo-600/10 blur-3xl"></div>
            <div class="absolute bottom-[-10%] left-[-5%] w-80 h-80 rounded-full bg-indigo-500/8 blur-3xl"></div>
        </div>

        <!-- Logo + nombre -->
        <VeltoryLogo size={40} showWordmark={true} />

        <!-- Tagline central -->
        <div class="relative z-10">
            <h1 class="text-4xl font-bold text-white leading-tight tracking-tight max-w-sm">
                Controla tu inventario con precisión.
            </h1>
            <p class="mt-4 text-slate-400 text-lg max-w-sm leading-relaxed">
                Gestión de productos, stock, categorías y proveedores en un solo lugar.
            </p>
        </div>

        <!-- Feature list -->
        <ul class="relative z-10 space-y-3">
            {#each [
                'Seguimiento de stock en tiempo real',
                'Alertas automáticas de inventario bajo',
                'Historial completo de movimientos',
            ] as feature}
                <li class="flex items-center gap-3 text-slate-400 text-sm">
                    <div class="w-5 h-5 rounded-full bg-indigo-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    {feature}
                </li>
            {/each}
        </ul>
    </div>

    <!-- Panel derecho — formulario -->
    <div class="flex-1 flex flex-col justify-center items-center p-8">
        <div class="w-full max-w-sm">
            <!-- Logo mobile -->
            <div class="lg:hidden mb-10">
                <VeltoryLogo size={36} showWordmark={true} />
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-bold text-white tracking-tight">Bienvenido de vuelta</h2>
                <p class="text-slate-400 mt-1.5 text-sm">Ingresa tus credenciales para continuar</p>
            </div>

            <form onsubmit={submit} class="space-y-5">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Correo electrónico
                    </label>
                    <input
                        id="email"
                        type="email"
                        bind:value={$form.email}
                        placeholder="tu@email.com"
                        autocomplete="email"
                        class="w-full px-3.5 py-2.5 bg-slate-900 border rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors
                            {$form.errors.email
                                ? 'border-red-500 focus:border-red-400'
                                : 'border-slate-700 focus:border-indigo-500'}"
                    />
                    {#if $form.errors.email}
                        <p class="mt-1.5 text-xs text-red-400">{$form.errors.email}</p>
                    {/if}
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-slate-300">
                            Contraseña
                        </label>
                    </div>
                    <input
                        id="password"
                        type="password"
                        bind:value={$form.password}
                        placeholder="••••••••"
                        autocomplete="current-password"
                        class="w-full px-3.5 py-2.5 bg-slate-900 border rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors
                            {$form.errors.password
                                ? 'border-red-500 focus:border-red-400'
                                : 'border-slate-700 focus:border-indigo-500'}"
                    />
                    {#if $form.errors.password}
                        <p class="mt-1.5 text-xs text-red-400">{$form.errors.password}</p>
                    {/if}
                </div>

                <!-- Remember me -->
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        bind:checked={$form.remember}
                        class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-950"
                    />
                    <span class="text-sm text-slate-400">Recordarme en este dispositivo</span>
                </label>

                <!-- Submit -->
                <button
                    type="submit"
                    disabled={$form.processing}
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors"
                >
                    {#if $form.processing}
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Ingresando...
                    {:else}
                        Iniciar sesión
                    {/if}
                </button>
            </form>

            <!-- Link registro -->
            <p class="mt-6 text-center text-sm text-slate-500">
                ¿No tienes cuenta?
                <a href="/register" class="text-indigo-400 hover:text-indigo-300 font-medium transition-colors ml-1">
                    Crear cuenta
                </a>
            </p>
        </div>
    </div>
</div>
