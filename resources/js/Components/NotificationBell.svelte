<script>
    import { page, router } from '@inertiajs/svelte';
    import NotificationDetailModal from './NotificationDetailModal.svelte';

    let open     = $state(false);
    let selected = $state(null);

    const notifs      = $derived($page.props.notifications?.items ?? []);
    const unreadCount = $derived($page.props.notifications?.unread_count ?? 0);

    function toggle() { open = !open; }
    function close()  { open = false; }

    function openDetail(notif) {
        close();
        selected = notif;
    }

    function markAllRead() {
        router.post('/notifications/read-all', {}, { preserveScroll: true });
    }

    function onKeydown(e) {
        if (e.key === 'Escape') close();
    }

    const iconMap = {
        warning: { bg: 'bg-amber-500/15',   text: 'text-amber-400',   path: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' },
        success: { bg: 'bg-emerald-500/15', text: 'text-emerald-400', path: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
        error:   { bg: 'bg-red-500/15',     text: 'text-red-400',     path: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' },
        info:    { bg: 'bg-indigo-500/15',  text: 'text-indigo-400',  path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    };
</script>

<svelte:window onkeydown={onKeydown} />

<!-- Modal de detalle (z-60, por encima del dropdown z-50) -->
<NotificationDetailModal notification={selected} onclose={() => selected = null} />

<div class="relative">
    <!-- Botón campana -->
    <button
        onclick={toggle}
        class="relative flex items-center justify-center w-9 h-9 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
        title="Notificaciones"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        {#if unreadCount > 0}
            <span class="absolute top-1 right-1 flex items-center justify-center min-w-[16px] h-4 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold leading-none">
                {unreadCount > 99 ? '99+' : unreadCount}
            </span>
        {/if}
    </button>

    <!-- Dropdown -->
    {#if open}
        <!-- Backdrop -->
        <div class="fixed inset-0 z-40" onclick={close}></div>

        <div class="absolute right-0 top-full mt-2 w-80 bg-slate-900 border border-slate-700 rounded-xl shadow-2xl z-50 overflow-hidden">

            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-white">Notificaciones</span>
                    {#if unreadCount > 0}
                        <span class="px-1.5 py-0.5 rounded-full text-xs font-bold bg-red-500/20 text-red-400">{unreadCount}</span>
                    {/if}
                </div>
                {#if unreadCount > 0}
                    <button
                        onclick={markAllRead}
                        class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors"
                    >
                        Marcar todas leídas
                    </button>
                {/if}
            </div>

            <!-- Lista -->
            <ul class="max-h-96 overflow-y-auto divide-y divide-slate-800/60">
                {#if notifs.length === 0}
                    <li class="py-10 text-center">
                        <svg class="w-8 h-8 text-slate-700 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <p class="text-sm text-slate-500">Sin notificaciones</p>
                    </li>
                {:else}
                    {#each notifs as notif}
                        {@const icon     = iconMap[notif.data?.icon] ?? iconMap.info}
                        {@const isUnread = !notif.read_at}
                        <li>
                            <button
                                onclick={() => openDetail(notif)}
                                class="w-full flex items-start gap-3 px-4 py-3 text-left transition-colors hover:bg-slate-800/50
                                    {isUnread ? 'bg-indigo-600/5' : ''}"
                            >
                                <!-- Icono -->
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {icon.bg} flex items-center justify-center mt-0.5">
                                    <svg class="w-4 h-4 {icon.text}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d={icon.path}/>
                                    </svg>
                                </div>

                                <!-- Contenido -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-sm font-medium truncate {isUnread ? 'text-white' : 'text-slate-300'}">
                                            {notif.data?.title ?? 'Notificación'}
                                        </p>
                                        {#if isUnread}
                                            <span class="flex-shrink-0 w-2 h-2 rounded-full bg-indigo-500 mt-1.5"></span>
                                        {/if}
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{notif.data?.body ?? ''}</p>
                                    <p class="text-xs text-slate-600 mt-1">{notif.created_at}</p>
                                </div>
                            </button>
                        </li>
                    {/each}
                {/if}
            </ul>

            <!-- Footer — Ver todas -->
            <div class="border-t border-slate-800 px-4 py-2.5">
                <a
                    href="/notifications"
                    onclick={close}
                    class="flex items-center justify-center gap-1.5 w-full py-1.5 text-xs font-medium text-slate-400 hover:text-indigo-400 transition-colors rounded-lg hover:bg-slate-800"
                >
                    Ver todas las notificaciones
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    {/if}
</div>
