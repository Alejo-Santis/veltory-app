<script>
    import { router } from '@inertiajs/svelte';

    let { notification = null, onclose } = $props();

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

    const icon     = $derived(iconMap[notification?.data?.icon] ?? iconMap.info);
    const isUnread = $derived(!notification?.read_at);

    function navigate() {
        const href = notification?.data?.href;
        if (isUnread) {
            router.post(`/notifications/${notification.id}/read`, {}, {
                preserveScroll: true,
                onSuccess: () => {
                    onclose?.();
                    if (href) router.visit(href);
                },
            });
        } else {
            onclose?.();
            if (href) router.visit(href);
        }
    }

    function markRead() {
        router.post(`/notifications/${notification.id}/read`, {}, {
            preserveScroll: true,
            onSuccess: () => onclose?.(),
        });
    }

    function onKeydown(e) {
        if (notification && e.key === 'Escape') onclose?.();
    }
</script>

<svelte:window onkeydown={onKeydown} />

{#if notification}

    <!-- Backdrop -->
    <div
        class="fixed inset-0 z-[60] flex items-center justify-center p-4"
        onclick={onclose}
        role="dialog"
        aria-modal="true"
    >
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"></div>

        <!-- Panel -->
        <div
            class="relative bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-md shadow-2xl"
            onclick={(e) => e.stopPropagation()}
        >
            <!-- Header -->
            <div class="flex items-start gap-4 p-6 pb-4">
                <div class="w-11 h-11 rounded-full {icon.bg} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 {icon.text}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d={icon.path}/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-base font-semibold text-white leading-snug">
                            {notification.data?.title ?? 'Notificación'}
                        </h3>
                        {#if isUnread}
                            <span class="flex-shrink-0 mt-1 w-2 h-2 rounded-full bg-indigo-500"></span>
                        {/if}
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {notification.created_at_full ?? notification.created_at}
                        <span class="text-slate-600 mx-1">·</span>
                        {notification.created_at}
                    </p>
                </div>
                <button
                    onclick={onclose}
                    class="flex-shrink-0 w-7 h-7 flex items-center justify-center rounded-lg text-slate-500 hover:text-white hover:bg-slate-800 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="px-6 pb-6">
                {#if notification.data?.body}
                    <p class="text-sm text-slate-300 leading-relaxed mb-5 bg-slate-800/60 rounded-xl px-4 py-3 border border-slate-700/50">
                        {notification.data.body}
                    </p>
                {/if}

                <!-- Estado -->
                <div class="flex items-center gap-2 mb-5">
                    <span class="text-xs text-slate-500 uppercase tracking-wide">Estado</span>
                    {#if isUnread}
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                            Sin leer
                        </span>
                    {:else}
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700/60 text-slate-400 border border-slate-600/30">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Leída
                        </span>
                    {/if}
                </div>

                <!-- Acciones -->
                <div class="flex items-center gap-3 pt-4 border-t border-slate-800">
                    {#if notification.data?.href}
                        <button
                            onclick={navigate}
                            class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors"
                        >
                            Ver detalle
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    {/if}
                    {#if isUnread}
                        <button
                            onclick={markRead}
                            class="flex items-center gap-1.5 py-2.5 px-4 text-slate-400 hover:text-slate-200 text-sm font-medium rounded-lg hover:bg-slate-800 transition-colors border border-slate-700 hover:border-slate-600"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Marcar leída
                        </button>
                    {/if}
                    {#if !notification.data?.href}
                        <button
                            onclick={onclose}
                            class="flex-1 py-2.5 px-4 text-slate-400 hover:text-slate-200 text-sm font-medium rounded-lg hover:bg-slate-800 transition-colors"
                        >
                            Cerrar
                        </button>
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}
