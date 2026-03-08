<script>
    import { page, router } from '@inertiajs/svelte';
    import AppLayout from '@/Layouts/AppLayout.svelte';
    import Breadcrumb from '@/Components/Breadcrumb.svelte';

    let { order } = $props();

    const canWrite = $derived($page.props.auth?.user?.can_write ?? false);

    // ── Recepción inline ──────────────────────────────────────
    let receiving   = $state(false);
    let receiveForm = $state([]);
    let submitting  = $state(false);

    function startReceiving() {
        receiveForm = order.items.map(item => ({
            id:                item.id,
            quantity_received: item.pending,
        }));
        receiving = true;
    }

    function cancelReceiving() {
        receiving = false;
        receiveForm = [];
    }

    function submitReceive() {
        submitting = true;
        router.patch(`/purchase-orders/${order.uuid}/receive`, { items: receiveForm }, {
            onError:  () => { submitting = false; },
            onSuccess: () => { submitting = false; receiving = false; },
        });
    }

    function send() {
        router.patch(`/purchase-orders/${order.uuid}/send`);
    }

    function cancel() {
        if (confirm(`¿Cancelar la orden ${order.reference}? Esta acción no se puede deshacer.`)) {
            router.patch(`/purchase-orders/${order.uuid}/cancel`);
        }
    }

    function destroy() {
        if (confirm(`¿Eliminar la orden ${order.reference}?`)) {
            router.delete(`/purchase-orders/${order.uuid}`);
        }
    }

    // ── Helpers ───────────────────────────────────────────────
    function formatCurrency(v) {
        if (v == null || v === 0) return '—';
        return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(v);
    }

    const statusConfig = {
        draft:     { label: 'Borrador',  class: 'bg-slate-500/10 text-slate-400 border border-slate-500/20' },
        sent:      { label: 'Enviada',   class: 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' },
        partial:   { label: 'Parcial',   class: 'bg-amber-500/10 text-amber-400 border border-amber-500/20' },
        received:  { label: 'Recibida',  class: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' },
        cancelled: { label: 'Cancelada', class: 'bg-red-500/10 text-red-400 border border-red-500/20' },
    };
    const st = $derived(statusConfig[order.status] ?? statusConfig.draft);
</script>

<svelte:head><title>{order.reference ?? 'Orden'} — Compra</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-4">
                <a href="/purchase-orders" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl font-semibold text-white font-mono">{order.reference ?? 'Sin referencia'}</h1>
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {st.class}">{st.label}</span>
                    </div>
                    <p class="text-sm text-slate-400 mt-0.5">{order.supplier?.name} → {order.warehouse?.name}</p>
                </div>
            </div>

            <!-- Acciones principales -->
            {#if !receiving}
                <div class="flex items-center gap-2">
                    <!-- PDF siempre disponible -->
                    <a href="/purchase-orders/{order.uuid}/pdf" target="_blank"
                        class="flex items-center gap-1.5 px-3 py-2 text-sm text-slate-400 hover:text-red-300 hover:bg-slate-800 border border-slate-700 rounded-lg transition-colors"
                        title="Descargar PDF de esta orden">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        PDF
                    </a>
            {#if canWrite}
                    {#if order.can_receive}
                        <button
                            onclick={startReceiving}
                            class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Recibir mercancía
                        </button>
                    {/if}
                    {#if order.status === 'draft'}
                        <button
                            onclick={send}
                            class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Enviar al proveedor
                        </button>
                        <a href="/purchase-orders/{order.uuid}/edit"
                            class="px-3 py-2 text-sm text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-700 rounded-lg transition-colors">
                            Editar
                        </a>
                        <button onclick={destroy} class="px-3 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 border border-red-500/20 rounded-lg transition-colors">
                            Eliminar
                        </button>
                    {/if}
                    {#if order.can_cancel}
                        <button onclick={cancel} class="px-3 py-2 text-sm text-slate-400 hover:text-red-300 hover:bg-slate-800 border border-slate-700 rounded-lg transition-colors">
                            Cancelar orden
                        </button>
                    {/if}
                    {/if}
                </div>
            {/if}
        </div>

        <div class="px-8 py-6 space-y-6">
            <Breadcrumb items={[{ label: 'Órdenes de compra', href: '/purchase-orders' }, { label: order.reference ?? 'Detalle' }]} />

            <!-- Info cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Proveedor</p>
                    <p class="text-sm font-semibold text-white">{order.supplier?.name}</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Bodega</p>
                    <p class="text-sm font-semibold text-white">{order.warehouse?.name}</p>
                    <p class="text-xs text-slate-600 font-mono">{order.warehouse?.code}</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Total estimado</p>
                    <p class="text-base font-bold text-white tabular-nums">{formatCurrency(order.total)}</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Entrega esperada</p>
                    <p class="text-sm font-semibold text-white">{order.expected_at ?? '—'}</p>
                </div>
            </div>

            <!-- Timeline de estados -->
            {#if order.sent_at || order.received_at || order.cancelled_at}
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Historial</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-3 text-slate-400">
                            <svg class="w-4 h-4 text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Creada por <span class="text-slate-300">{order.created_by ?? '—'}</span> el <span class="text-slate-300">{order.created_at}</span></span>
                        </div>
                        {#if order.sent_at}
                            <div class="flex items-center gap-3 text-slate-400">
                                <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <span>Enviada al proveedor el <span class="text-slate-300">{order.sent_at}</span></span>
                            </div>
                        {/if}
                        {#if order.received_at}
                            <div class="flex items-center gap-3 text-slate-400">
                                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Recibida completa el <span class="text-slate-300">{order.received_at}</span></span>
                            </div>
                        {/if}
                        {#if order.cancelled_at}
                            <div class="flex items-center gap-3 text-slate-400">
                                <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Cancelada el <span class="text-slate-300">{order.cancelled_at}</span></span>
                            </div>
                        {/if}
                    </div>
                </div>
            {/if}

            <!-- Formulario de recepción -->
            {#if receiving}
                <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-xl overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-emerald-500/20">
                        <h3 class="text-sm font-semibold text-emerald-400 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Registrar recepción de mercancía
                        </h3>
                        <button onclick={cancelReceiving} class="text-slate-500 hover:text-white text-xs transition-colors">Cancelar</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-emerald-500/10">
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Producto</th>
                                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pedido</th>
                                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Ya recibido</th>
                                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pendiente</th>
                                    <th class="text-right px-5 py-3 text-xs font-semibold text-emerald-500 uppercase tracking-wider w-36">Recibir ahora</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                {#each order.items as item, i}
                                    <tr class="{item.pending === 0 ? 'opacity-40' : ''}">
                                        <td class="px-5 py-3">
                                            <a href="/products/{item.product.uuid}" class="text-white hover:text-indigo-300 transition-colors font-medium">
                                                {item.product.name}
                                            </a>
                                            {#if item.product.sku}
                                                <span class="ml-2 text-xs font-mono text-slate-600">{item.product.sku}</span>
                                            {/if}
                                        </td>
                                        <td class="px-5 py-3 text-right text-slate-400 tabular-nums">
                                            {item.quantity_ordered} <span class="text-xs text-slate-600">{item.product.unit ?? ''}</span>
                                        </td>
                                        <td class="px-5 py-3 text-right text-slate-400 tabular-nums">{item.quantity_received}</td>
                                        <td class="px-5 py-3 text-right tabular-nums {item.pending > 0 ? 'text-amber-400 font-semibold' : 'text-slate-600'}">{item.pending}</td>
                                        <td class="px-5 py-3">
                                            <input
                                                type="number"
                                                min="0"
                                                max={item.pending}
                                                bind:value={receiveForm[i].quantity_received}
                                                disabled={item.pending === 0}
                                                class="w-full px-2.5 py-1.5 bg-slate-800 border border-emerald-500/30 rounded-lg text-sm text-slate-200 text-right focus:outline-none focus:border-emerald-500 disabled:opacity-40"
                                            />
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-end gap-3 px-6 py-4 border-t border-emerald-500/10">
                        <button onclick={cancelReceiving} class="px-4 py-2 text-sm text-slate-400 hover:text-white border border-slate-700 rounded-lg transition-colors">
                            Cancelar
                        </button>
                        <button
                            onclick={submitReceive}
                            disabled={submitting}
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white text-sm font-semibold rounded-lg transition-colors"
                        >
                            {submitting ? 'Guardando…' : 'Confirmar recepción'}
                        </button>
                    </div>
                </div>
            {/if}

            <!-- Tabla de ítems -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-800">
                    <h3 class="text-sm font-semibold text-white">Productos de la orden</h3>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-800">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Producto</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pedido</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Recibido</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pendiente</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Costo unit.</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        {#each order.items as item}
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="px-5 py-3">
                                    <a href="/products/{item.product.uuid}" class="text-white hover:text-indigo-300 transition-colors font-medium">
                                        {item.product.name}
                                    </a>
                                    {#if item.product.sku}
                                        <span class="ml-2 text-xs font-mono text-slate-600">{item.product.sku}</span>
                                    {/if}
                                </td>
                                <td class="px-5 py-3 text-right text-slate-300 tabular-nums">
                                    {item.quantity_ordered} <span class="text-xs text-slate-600">{item.product.unit ?? ''}</span>
                                </td>
                                <td class="px-5 py-3 text-right tabular-nums {item.quantity_received > 0 ? 'text-emerald-400' : 'text-slate-600'}">
                                    {item.quantity_received}
                                </td>
                                <td class="px-5 py-3 text-right tabular-nums {item.pending > 0 ? 'text-amber-400' : 'text-slate-600'}">
                                    {item.pending}
                                </td>
                                <td class="px-5 py-3 text-right text-slate-400 tabular-nums">{formatCurrency(item.unit_cost)}</td>
                                <td class="px-5 py-3 text-right text-white font-medium tabular-nums">{formatCurrency(item.subtotal)}</td>
                            </tr>
                        {/each}
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-700">
                            <td colspan="5" class="px-5 py-4 text-right text-sm font-semibold text-slate-400">Total</td>
                            <td class="px-5 py-4 text-right text-base font-bold text-white tabular-nums">{formatCurrency(order.total)}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {#if order.notes}
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Notas</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">{order.notes}</p>
                </div>
            {/if}
        </div>
    </div>
</AppLayout>
