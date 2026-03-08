<script>
    import { page, router } from '@inertiajs/svelte';
    import AppLayout from '@/Layouts/AppLayout.svelte';
    import Breadcrumb from '@/Components/Breadcrumb.svelte';

    let { orders, filters } = $props();

    let search = $state(filters.search ?? '');
    let status = $state(filters.status ?? '');

    const canWrite = $derived($page.props.auth?.user?.can_write ?? false);

    let searchTimer;
    function onSearch() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => applyFilters(), 350);
    }

    function setStatus(s) {
        status = s;
        applyFilters();
    }

    function applyFilters() {
        router.get('/purchase-orders', { search: search || undefined, status: status || undefined }, {
            preserveState: true, replace: true,
        });
    }

    const statusConfig = {
        draft:     { label: 'Borrador',   class: 'bg-slate-500/10 text-slate-400 border border-slate-500/20' },
        sent:      { label: 'Enviada',    class: 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' },
        partial:   { label: 'Parcial',    class: 'bg-amber-500/10 text-amber-400 border border-amber-500/20' },
        received:  { label: 'Recibida',   class: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' },
        cancelled: { label: 'Cancelada',  class: 'bg-red-500/10 text-red-400 border border-red-500/20' },
    };

    const tabs = [
        { value: '',         label: 'Todas' },
        { value: 'draft',    label: 'Borrador' },
        { value: 'sent',     label: 'Enviadas' },
        { value: 'partial',  label: 'Parciales' },
        { value: 'received', label: 'Recibidas' },
        { value: 'cancelled',label: 'Canceladas' },
    ];

    function formatCurrency(v) {
        if (v == null) return '—';
        return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(v);
    }
</script>

<svelte:head><title>Órdenes de compra</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">Órdenes de compra</h1>
                <p class="text-sm text-slate-400 mt-0.5">{orders.total} orden{orders.total !== 1 ? 'es' : ''}</p>
            </div>
            <div class="flex items-center gap-2">
                {#if canWrite}
                    <a
                        href={`/purchase-orders/export/pdf${status ? '?status=' + status : ''}${search ? (status ? '&' : '?') + 'search=' + encodeURIComponent(search) : ''}`}
                        target="_blank"
                        class="flex items-center gap-1.5 px-3 py-2 text-sm text-slate-400 hover:text-red-300 hover:bg-slate-800 border border-slate-700 rounded-lg transition-colors"
                        title="Exportar listado a PDF"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        PDF
                    </a>
                    <a href="/purchase-orders/create"
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nueva orden
                    </a>
                {/if}
            </div>
        </div>

        <div class="px-8 py-6 space-y-4">
            <Breadcrumb items={[{ label: 'Órdenes de compra' }]} />

            <!-- Filtros -->
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Tabs de estado -->
                <div class="flex items-center gap-1 bg-slate-800 border border-slate-700 rounded-lg p-1">
                    {#each tabs as tab}
                        <button
                            onclick={() => setStatus(tab.value)}
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors
                                {status === tab.value
                                    ? 'bg-indigo-600 text-white'
                                    : 'text-slate-400 hover:text-white'}"
                        >
                            {tab.label}
                        </button>
                    {/each}
                </div>

                <!-- Búsqueda -->
                <div class="relative flex-1 min-w-48 max-w-72">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input
                        type="text"
                        placeholder="Referencia o proveedor..."
                        bind:value={search}
                        oninput={onSearch}
                        class="w-full pl-9 pr-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500"
                    />
                </div>
            </div>

            <!-- Tabla -->
            {#if orders.data.length === 0}
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-800 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-slate-400 font-medium">Sin órdenes de compra</p>
                    {#if canWrite}
                        <a href="/purchase-orders/create" class="mt-3 text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
                            Crear primera orden →
                        </a>
                    {/if}
                </div>
            {:else}
                <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Referencia</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Proveedor</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Bodega destino</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Ítems</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            {#each orders.data as order}
                                {@const st = statusConfig[order.status] ?? statusConfig.draft}
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <td class="px-5 py-3">
                                        <a href="/purchase-orders/{order.uuid}" class="font-mono text-sm font-semibold text-white hover:text-indigo-300 transition-colors">
                                            {order.reference ?? '—'}
                                        </a>
                                    </td>
                                    <td class="px-5 py-3 text-slate-300">{order.supplier?.name ?? '—'}</td>
                                    <td class="px-5 py-3 text-slate-400 text-xs">
                                        <span class="font-mono">{order.warehouse?.code}</span>
                                        <span class="ml-1 text-slate-600">{order.warehouse?.name}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right text-slate-400 tabular-nums">{order.items_count}</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {st.class}">
                                            {st.label}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-500 text-xs">{order.created_at}</td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="/purchase-orders/{order.uuid}"
                                            class="p-1.5 text-slate-500 hover:text-indigo-400 hover:bg-slate-700 rounded-md transition-colors inline-flex"
                                            title="Ver detalle">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>

                    <!-- Paginación -->
                    {#if orders.last_page > 1}
                        <div class="px-5 py-4 border-t border-slate-800 flex items-center justify-between text-sm text-slate-400">
                            <span>Mostrando {orders.from}–{orders.to} de {orders.total}</span>
                            <div class="flex items-center gap-1">
                                {#each orders.links as link}
                                    {#if link.url}
                                        <a href={link.url}
                                            class="px-3 py-1.5 rounded-lg transition-colors {link.active ? 'bg-indigo-600 text-white font-semibold' : 'hover:bg-slate-800 text-slate-400'}">
                                            {@html link.label}
                                        </a>
                                    {:else}
                                        <span class="px-3 py-1.5 text-slate-700 cursor-default">{@html link.label}</span>
                                    {/if}
                                {/each}
                            </div>
                        </div>
                    {/if}
                </div>
            {/if}
        </div>
    </div>
</AppLayout>
