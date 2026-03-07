<script>
    import { useForm } from '@inertiajs/svelte';
    import VeltoryLogo from '../../Components/VeltoryLogo.svelte';

    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    function submit(e) {
        e.preventDefault();
        $form.post('/register', {
            onFinish: () => $form.reset('password', 'password_confirmation'),
        });
    }
</script>

<svelte:head>
    <title>Crear cuenta</title>
</svelte:head>

<div class="min-h-screen bg-slate-950 flex">
    <!-- Panel izquierdo — branding -->
    <div class="hidden lg:flex lg:w-1/2 xl:w-2/5 flex-col justify-between p-12 bg-slate-900 border-r border-slate-800 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-[-10%] right-[-10%] w-96 h-96 rounded-full bg-indigo-600/10 blur-3xl"></div>
            <div class="absolute bottom-[-10%] left-[-5%] w-80 h-80 rounded-full bg-indigo-500/8 blur-3xl"></div>
        </div>

        <VeltoryLogo size={40} showWordmark={true} />

        <div class="relative z-10">
            <h1 class="text-4xl font-bold text-white leading-tight tracking-tight max-w-sm">
                Empieza a gestionar tu inventario hoy.
            </h1>
            <p class="mt-4 text-slate-400 text-lg max-w-sm leading-relaxed">
                Crea tu cuenta gratis y toma el control de tus productos y stock.
            </p>
        </div>

        <div class="relative z-10 p-5 bg-slate-800/60 border border-slate-700 rounded-xl">
            <p class="text-slate-300 text-sm italic leading-relaxed">
                "Veltory nos ayudó a reducir pérdidas por falta de stock en un 40% durante el primer mes."
            </p>
            <div class="mt-3 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-bold text-white">A</div>
                <div>
                    <p class="text-sm font-medium text-white">Alejandro M.</p>
                    <p class="text-xs text-slate-500">Gerente de Operaciones</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel derecho — formulario -->
    <div class="flex-1 flex flex-col justify-center items-center p-8">
        <div class="w-full max-w-sm">
            <div class="lg:hidden mb-10">
                <VeltoryLogo size={36} showWordmark={true} />
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-bold text-white tracking-tight">Crear cuenta</h2>
                <p class="text-slate-400 mt-1.5 text-sm">Completa el formulario para empezar</p>
            </div>

            <form onsubmit={submit} class="space-y-4">
                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Nombre completo
                    </label>
                    <input
                        id="name"
                        type="text"
                        bind:value={$form.name}
                        placeholder="Juan García"
                        autocomplete="name"
                        class="w-full px-3.5 py-2.5 bg-slate-900 border rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors
                            {$form.errors.name
                                ? 'border-red-500 focus:border-red-400'
                                : 'border-slate-700 focus:border-indigo-500'}"
                    />
                    {#if $form.errors.name}
                        <p class="mt-1.5 text-xs text-red-400">{$form.errors.name}</p>
                    {/if}
                </div>

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
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Contraseña
                    </label>
                    <input
                        id="password"
                        type="password"
                        bind:value={$form.password}
                        placeholder="Mínimo 8 caracteres"
                        autocomplete="new-password"
                        class="w-full px-3.5 py-2.5 bg-slate-900 border rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors
                            {$form.errors.password
                                ? 'border-red-500 focus:border-red-400'
                                : 'border-slate-700 focus:border-indigo-500'}"
                    />
                    {#if $form.errors.password}
                        <p class="mt-1.5 text-xs text-red-400">{$form.errors.password}</p>
                    {/if}
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Confirmar contraseña
                    </label>
                    <input
                        id="password_confirmation"
                        type="password"
                        bind:value={$form.password_confirmation}
                        placeholder="Repite tu contraseña"
                        autocomplete="new-password"
                        class="w-full px-3.5 py-2.5 bg-slate-900 border rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors border-slate-700 focus:border-indigo-500"
                    />
                </div>

                <button
                    type="submit"
                    disabled={$form.processing}
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors mt-2"
                >
                    {#if $form.processing}
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Creando cuenta...
                    {:else}
                        Crear cuenta
                    {/if}
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                ¿Ya tienes cuenta?
                <a href="/login" class="text-indigo-400 hover:text-indigo-300 font-medium transition-colors ml-1">
                    Iniciar sesión
                </a>
            </p>
        </div>
    </div>
</div>
