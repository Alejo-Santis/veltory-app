<script>
    import VeltoryLogo from '../Components/VeltoryLogo.svelte';

    let { status = 404 } = $props();

    const messages = {
        403: { title: 'Acceso denegado',       description: 'No tienes permiso para ver esta página.' },
        404: { title: 'Página no encontrada',  description: 'El recurso que buscas no existe o fue movido.' },
        500: { title: 'Error del servidor',    description: 'Algo salió mal en el servidor. Intenta de nuevo más tarde.' },
        503: { title: 'Servicio no disponible', description: 'El servicio está temporalmente fuera de línea. Vuelve pronto.' },
    };

    const msg = $derived(messages[status] ?? { title: 'Error inesperado', description: 'Ocurrió un error desconocido.' });

    const statusColors = {
        403: 'text-amber-400',
        404: 'text-indigo-400',
        500: 'text-red-400',
        503: 'text-slate-400',
    };
    const statusColor = $derived(statusColors[status] ?? 'text-slate-400');
</script>

<svelte:head><title>{status} — {msg.title}</title></svelte:head>

<div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col items-center justify-center px-4">

    <!-- Logo -->
    <a href="/" class="mb-10 opacity-80 hover:opacity-100 transition-opacity">
        <VeltoryLogo size={32} showWordmark={true} />
    </a>

    <!-- Error code -->
    <p class="text-8xl font-black tabular-nums {statusColor} mb-4 select-none">{status}</p>

    <!-- Message -->
    <h1 class="text-2xl font-semibold text-white mb-2">{msg.title}</h1>
    <p class="text-slate-400 text-sm text-center max-w-sm mb-10">{msg.description}</p>

    <!-- Actions -->
    <div class="flex items-center gap-3">
        <a
            href="/"
            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors"
        >
            Ir al inicio
        </a>
        <button
            onclick={() => history.back()}
            class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-lg transition-colors"
        >
            Volver
        </button>
    </div>

    <!-- Subtle decoration -->
    <div class="mt-16 text-xs text-slate-700 text-center">
        Veltory · Sistema de Inventario
    </div>
</div>
