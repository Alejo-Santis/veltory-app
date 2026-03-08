<script>
    import { router } from '@inertiajs/svelte';
    import AppLayout from '@/Layouts/AppLayout.svelte';
    import NotificationDetailModal from '@/Components/NotificationDetailModal.svelte';

    let { notifications } = $props();

    let selected = $state(null);

    const iconMap = {
        warning: {
            bg:   'bg-amber-500/15',
            text: 'text-amber-400',
            path: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        },
        success: {
            bg:   'bg-emerald-500/15',
            text: 'text-emerald-400',
            path: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        },
        error: {
            bg:   'bg-red-500/15',
            text: 'text-red-400',
            path: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        },
        info: {
            bg:   'bg-indigo-500/15',
            text: 'text-indigo-400',
            path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        },
    };

    const hasUnread = $derived(notifications.data.some(n => !n.read_at));

    function markAllRead() {
        router.post('/notifications/read-all', {}, { preserveScroll: true });
    }
</script>

<svelte:head><title>Notificaciones</title></svelte:head>

<NotificationDetailModal notification={selected} onclose={() => selected = null} />

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">Notificaciones</h1>
                <p class="text-sm text-slate-400 mt-0.5">
                    {#if notifications.total > 0}
                        {notifications.total} notificacion{notifications.total !== 1 ? 'es' : ''}
                    {:else}
                        Sin notificaciones
                    {/if}
                </p>
            </div>
            {#if hasUnread}
                <button
                    onclick={markAllRead}
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-indigo-400 hover:text-indigo-300 hover:bg-slate-800 border border-slate-700 hover:border-slate-600 rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Marcar todas como leídas
                </button>
            {/if}
        </div>

        <div class="px-8 py-6">

            {#if notifications.data.length === 0}
                <!-- Empty state -->
                <div class="flex flex-col items-center justify-center py-28 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-800 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <p class="text-slate-400 font-medium">Sin notificaciones</p>
                    <p class="text-slate-600 text-sm mt-1">Aquí aparecerán las alertas del sistema</p>
                </div>

            {:else}
                <!-- Lista -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden divide-y divide-slate-800">
                    {#each notifications.data as notif}
                        {@const icon     = iconMap[notif.data?.icon] ?? iconMap.info}
                        {@const isUnread = !notif.read_at}
                        <button
                            onclick={() => selected = notif}
                            class="w-full flex items-start gap-4 px-6 py-4 text-left transition-colors hover:bg-slate-800/50
                                {isUnread ? 'bg-indigo-600/5' : ''}"
                        >
                            <!-- Icono tipo -->
                            <div class="flex-shrink-0 w-10 h-10 rounded-full {icon.bg} flex items-center justify-center mt-0.5">
                                <svg class="w-5 h-5 {icon.text}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d={icon.path}/>
                                </svg>
                            </div>

                            <!-- Contenido -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <p class="text-sm font-semibold {isUnread ? 'text-white' : 'text-slate-300'} truncate">
                                        {notif.data?.title ?? 'Notificación'}
                                    </p>
                                    <span class="flex-shrink-0 text-xs text-slate-500">{notif.created_at}</span>
                                </div>
                                {#if notif.data?.body}
                                    <p class="text-sm text-slate-500 mt-0.5 line-clamp-1">{notif.data.body}</p>
                                {/if}
                            </div>

                            <!-- Punto no leída / chevron -->
                            <div class="flex-shrink-0 flex items-center self-center gap-2">
                                {#if isUnread}
                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                {/if}
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </button>
                    {/each}
                </div>

                <!-- Paginación -->
                {#if notifications.last_page > 1}
                    <div class="mt-6 flex items-center justify-between text-sm text-slate-400">
                        <span>Mostrando {notifications.from}–{notifications.to} de {notifications.total}</span>
                        <div class="flex items-center gap-1">
                            {#each notifications.links as link}
                                {#if link.url}
                                    <a
                                        href={link.url}
                                        class="px-3 py-1.5 rounded-lg transition-colors
                                            {link.active
                                                ? 'bg-indigo-600 text-white font-semibold'
                                                : 'hover:bg-slate-800 text-slate-400'}"
                                    >
                                        {@html link.label}
                                    </a>
                                {:else}
                                    <span class="px-3 py-1.5 text-slate-700 cursor-default">{@html link.label}</span>
                                {/if}
                            {/each}
                        </div>
                    </div>
                {/if}
            {/if}

        </div>
    </div>
</AppLayout>
