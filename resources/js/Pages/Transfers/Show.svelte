<script>
    import { useForm, router } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';

    let { transfer, statuses = [] } = $props();

    const statusMap = Object.fromEntries(statuses.map(s => [s.value, s]));

    const statusStyle = {
        draft:      'bg-slate-500/10 text-slate-400 border border-slate-500/20',
        requested:  'bg-amber-500/10 text-amber-400 border border-amber-500/20',
        approved:   'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
        in_transit: 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
        completed:  'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
        cancelled:  'bg-red-500/10 text-red-400 border border-red-500/20',
    };

    // Formulario para despacho (ship) y recepción (complete)
    const shipForm = useForm({
        items: transfer.items.map(item => ({
            id:            item.id,
            quantity_sent: item.quantity_requested,
        })),
    });

    const completeForm = useForm({
        items: transfer.items.map(item => ({
            id:                item.id,
            quantity_received: item.quantity_sent ?? item.quantity_requested,
        })),
    });

    let showShipModal     = $state(false);
    let showCompleteModal = $state(false);

    function submitRequest() {
        router.patch(`/transfers/${transfer.uuid}/request`);
    }

    function submitApprove() {
        router.patch(`/transfers/${transfer.uuid}/approve`);
    }

    function submitShip(e) {
        e.preventDefault();
        $shipForm.patch(`/transfers/${transfer.uuid}/ship`, { onSuccess: () => { showShipModal = false; } });
    }

    function submitComplete(e) {
        e.preventDefault();
        $completeForm.patch(`/transfers/${transfer.uuid}/complete`, { onSuccess: () => { showCompleteModal = false; } });
    }

    function submitCancel() {
        if (confirm('¿Cancelar este traslado?')) {
            router.patch(`/transfers/${transfer.uuid}/cancel`);
        }
    }

    function formatDate(d) {
        if (!d) return '—';
        return new Date(d).toLocaleString('es-CO', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    const st = $derived(transfer.status);
    const canRequest  = $derived(st === 'draft');
    const canApprove  = $derived(st === 'requested');
    const canShip     = $derived(st === 'approved');
    const canComplete = $derived(st === 'in_transit');
    const canCancel   = $derived(['draft','requested','approved','in_transit'].includes(st));
</script>

<svelte:head><title>Traslado {transfer.reference ?? transfer.uuid}</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/transfers" class="text-slate-500 hover:text-slate-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl font-semibold text-white">
                            {transfer.reference ?? 'Traslado sin referencia'}
                        </h1>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {statusStyle[st] ?? ''}">
                            {statusMap[st]?.label ?? st}
                        </span>
                    </div>
                    <p class="text-sm text-slate-400 mt-0.5">
                        {transfer.from_warehouse?.name} → {transfer.to_warehouse?.name}
                    </p>
                </div>
            </div>

            <!-- Acciones según estado -->
            <div class="flex items-center gap-2">
                {#if canRequest}
                    <button onclick={submitRequest}
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-sm font-semibold rounded-lg transition-colors">
                        Enviar para aprobación
                    </button>
                {/if}
                {#if canApprove}
                    <button onclick={submitApprove}
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors">
                        Aprobar traslado
                    </button>
                {/if}
                {#if canShip}
                    <button onclick={() => showShipModal = true}
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-lg transition-colors">
                        Registrar despacho
                    </button>
                {/if}
                {#if canComplete}
                    <button onclick={() => showCompleteModal = true}
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-lg transition-colors">
                        Confirmar recepción
                    </button>
                {/if}
                {#if canCancel}
                    <button onclick={submitCancel}
                        class="px-4 py-2 bg-slate-800 hover:bg-red-900/50 text-red-400 hover:text-red-300 text-sm font-semibold rounded-lg border border-slate-700 hover:border-red-800 transition-colors">
                        Cancelar
                    </button>
                {/if}
            </div>
        </div>

        <div class="px-8 py-6 space-y-6 max-w-4xl">

            <!-- Info general -->
            <div class="grid grid-cols-2 gap-6">
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-3">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Información</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Referencia</dt>
                            <dd class="font-mono text-slate-300">{transfer.reference ?? '—'}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Solicitado por</dt>
                            <dd class="text-slate-300">{transfer.requested_by?.name ?? '—'}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Aprobado por</dt>
                            <dd class="text-slate-300">{transfer.approved_by?.name ?? '—'}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-3">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Fechas</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Creado</dt>
                            <dd class="text-slate-300">{formatDate(transfer.created_at)}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Solicitado</dt>
                            <dd class="text-slate-300">{formatDate(transfer.requested_at)}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Despachado</dt>
                            <dd class="text-slate-300">{formatDate(transfer.shipped_at)}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Completado</dt>
                            <dd class="text-slate-300">{formatDate(transfer.completed_at)}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {#if transfer.notes}
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Notas</h3>
                    <p class="text-sm text-slate-300">{transfer.notes}</p>
                </div>
            {/if}

            <!-- Ítems -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-800">
                    <h3 class="text-sm font-semibold text-white">Productos del traslado</h3>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-800">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Producto</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Solicitado</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Despachado</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Recibido</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Notas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        {#each transfer.items as item}
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-white">{item.product?.name ?? '—'}</div>
                                    {#if item.product?.sku}
                                        <div class="text-xs text-slate-500 font-mono">{item.product.sku}</div>
                                    {/if}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-slate-300">{item.quantity_requested}</td>
                                <td class="px-4 py-3 text-right font-mono {item.quantity_sent != null ? 'text-blue-400' : 'text-slate-600'}">
                                    {item.quantity_sent ?? '—'}
                                </td>
                                <td class="px-4 py-3 text-right font-mono {item.quantity_received != null ? 'text-emerald-400' : 'text-slate-600'}">
                                    {item.quantity_received ?? '—'}
                                    {#if item.quantity_received != null && item.quantity_sent != null && item.quantity_received < item.quantity_sent}
                                        <span class="text-xs text-amber-400 ml-1" title="Diferencia en recepción">!</span>
                                    {/if}
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{item.notes ?? '—'}</td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</AppLayout>

<!-- Modal despacho -->
{#if showShipModal}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick={() => showShipModal = false}></div>
        <div class="relative bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-lg shadow-2xl">
            <h3 class="text-base font-semibold text-white mb-1">Registrar despacho</h3>
            <p class="text-sm text-slate-400 mb-5">Indica la cantidad despachada por producto. El stock de la bodega origen se descontará.</p>

            <form onsubmit={submitShip} class="space-y-3">
                {#each $shipForm.items as item, i}
                    {@const original = transfer.items[i]}
                    <div class="flex items-center gap-4 p-3 bg-slate-800 rounded-lg">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">{original.product?.name}</p>
                            <p class="text-xs text-slate-500">Solicitado: {original.quantity_requested}</p>
                        </div>
                        <div class="w-28">
                            <input type="number" min="0" max={original.quantity_requested} bind:value={item.quantity_sent}
                                class="w-full px-3 py-2 bg-slate-700 border border-slate-600 focus:border-indigo-500 rounded-lg text-sm text-white text-center outline-none"/>
                        </div>
                    </div>
                {/each}

                <div class="flex gap-3 pt-2">
                    <button type="submit" disabled={$shipForm.processing}
                        class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-500 disabled:opacity-60 text-white text-sm font-semibold rounded-lg transition-colors">
                        {$shipForm.processing ? 'Registrando...' : 'Confirmar despacho'}
                    </button>
                    <button type="button" onclick={() => showShipModal = false}
                        class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-lg transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}

<!-- Modal recepción -->
{#if showCompleteModal}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick={() => showCompleteModal = false}></div>
        <div class="relative bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-lg shadow-2xl">
            <h3 class="text-base font-semibold text-white mb-1">Confirmar recepción</h3>
            <p class="text-sm text-slate-400 mb-5">Indica la cantidad recibida. El stock de la bodega destino se incrementará.</p>

            <form onsubmit={submitComplete} class="space-y-3">
                {#each $completeForm.items as item, i}
                    {@const original = transfer.items[i]}
                    <div class="flex items-center gap-4 p-3 bg-slate-800 rounded-lg">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">{original.product?.name}</p>
                            <p class="text-xs text-slate-500">Despachado: {original.quantity_sent ?? '—'}</p>
                        </div>
                        <div class="w-28">
                            <input type="number" min="0" bind:value={item.quantity_received}
                                class="w-full px-3 py-2 bg-slate-700 border border-slate-600 focus:border-indigo-500 rounded-lg text-sm text-white text-center outline-none"/>
                        </div>
                    </div>
                {/each}

                <div class="flex gap-3 pt-2">
                    <button type="submit" disabled={$completeForm.processing}
                        class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-60 text-white text-sm font-semibold rounded-lg transition-colors">
                        {$completeForm.processing ? 'Registrando...' : 'Confirmar recepción'}
                    </button>
                    <button type="button" onclick={() => showCompleteModal = false}
                        class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-lg transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}
