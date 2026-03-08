<script>
    import { page } from '@inertiajs/svelte';
    import AppLayout from '@/Layouts/AppLayout.svelte';
    import Breadcrumb from '@/Components/Breadcrumb.svelte';

    let { warehouse, stock = [], recentMovements = [] } = $props();

    const canWrite = $derived($page.props.auth?.user?.can_write ?? false);

    const totalUnits  = $derived(stock.reduce((s, i) => s + (i.quantity ?? 0), 0));
    const totalSkus   = $derived(stock.length);
    const zeroStock   = $derived(stock.filter(i => i.quantity <= 0).length);

    const typeStyle = {
        in:         'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
        out:        'bg-red-500/10 text-red-400 border border-red-500/20',
        adjustment: 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
        return:     'bg-amber-500/10 text-amber-400 border border-amber-500/20',
        loss:       'bg-orange-500/10 text-orange-400 border border-orange-500/20',
    };
    const typeLabel = { in: 'Entrada', out: 'Salida', adjustment: 'Ajuste', return: 'Devolución', loss: 'Pérdida' };

    function fmtDate(d) {
        return new Date(d).toLocaleString('es-CO', { dateStyle: 'medium', timeStyle: 'short' });
    }
</script>

<svelte:head><title>{warehouse.name} — Bodega</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/warehouses" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-semibold text-white">{warehouse.name}</h1>
                    <p class="text-sm text-slate-400 mt-0.5">Código: <span class="font-mono">{warehouse.code}</span> · {warehouse.city ?? '—'}</p>
                </div>
            </div>
            {#if canWrite}
                <a href="/warehouses/{warehouse.uuid}/edit"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
            {/if}
        </div>

        <div class="p-8 space-y-6">
            <Breadcrumb items={[{ label: 'Bodegas', href: '/warehouses' }, { label: warehouse.name }]} />

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 text-center">
                    <p class="text-3xl font-bold text-white">{totalUnits}</p>
                    <p class="text-slate-400 text-sm mt-1">Unidades totales</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 text-center">
                    <p class="text-3xl font-bold text-indigo-400">{totalSkus}</p>
                    <p class="text-slate-400 text-sm mt-1">SKUs distintos</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 text-center">
                    <p class="text-3xl font-bold {zeroStock > 0 ? 'text-red-400' : 'text-emerald-400'}">{zeroStock}</p>
                    <p class="text-slate-400 text-sm mt-1">Sin stock</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Info de la bodega -->
                <div class="space-y-4">
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-3">
                        <h3 class="text-sm font-semibold text-white mb-3">Información</h3>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-slate-500 text-xs uppercase tracking-wide mb-0.5">Estado</dt>
                                <dd>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {warehouse.is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border border-slate-500/20'}">
                                        {warehouse.is_active ? 'Activa' : 'Inactiva'}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-slate-500 text-xs uppercase tracking-wide mb-0.5">Tipo</dt>
                                <dd class="text-slate-300">{warehouse.type}</dd>
                            </div>
                            {#if warehouse.address}
                                <div>
                                    <dt class="text-slate-500 text-xs uppercase tracking-wide mb-0.5">Dirección</dt>
                                    <dd class="text-slate-400 text-xs">{warehouse.address}</dd>
                                </div>
                            {/if}
                            {#if warehouse.manager_name}
                                <div>
                                    <dt class="text-slate-500 text-xs uppercase tracking-wide mb-0.5">Responsable</dt>
                                    <dd class="text-slate-300">{warehouse.manager_name}</dd>
                                </div>
                            {/if}
                            {#if warehouse.phone}
                                <div>
                                    <dt class="text-slate-500 text-xs uppercase tracking-wide mb-0.5">Teléfono</dt>
                                    <dd class="text-slate-300">{warehouse.phone}</dd>
                                </div>
                            {/if}
                        </dl>
                    </div>

                    <!-- Movimientos recientes -->
                    {#if recentMovements.length > 0}
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                            <h3 class="text-sm font-semibold text-white mb-3">Movimientos recientes</h3>
                            <div class="space-y-2">
                                {#each recentMovements as mov}
                                    <div class="flex items-center gap-2 py-1.5 border-b border-slate-800 last:border-0">
                                        <span class="inline-flex px-1.5 py-0.5 rounded-full text-[10px] font-medium {typeStyle[mov.type?.value ?? mov.type] ?? typeStyle.adjustment}">
                                            {typeLabel[mov.type?.value ?? mov.type] ?? mov.type}
                                        </span>
                                        <span class="text-xs text-slate-300 flex-1 truncate">{mov.product?.name ?? '—'}</span>
                                        <span class="text-xs font-semibold {mov.quantity > 0 ? 'text-emerald-400' : 'text-red-400'}">
                                            {mov.quantity > 0 ? '+' : ''}{mov.quantity}
                                        </span>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {/if}
                </div>

                <!-- Inventario en bodega -->
                <div class="lg:col-span-2">
                    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-800">
                            <h3 class="text-sm font-semibold text-white">Inventario en esta bodega</h3>
                        </div>
                        {#if stock.length === 0}
                            <div class="py-12 text-center text-slate-500 text-sm">Sin stock registrado en esta bodega.</div>
                        {:else}
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-800">
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Producto</th>
                                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    {#each stock as item}
                                        <tr class="hover:bg-slate-800/40 transition-colors">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-800 flex-shrink-0 border border-slate-700">
                                                        {#if item.product?.cover_image?.url}
                                                            <img src={item.product.cover_image.url} alt={item.product.name} class="w-full h-full object-cover"/>
                                                        {:else}
                                                            <div class="w-full h-full flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                                </svg>
                                                            </div>
                                                        {/if}
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-white">{item.product?.name ?? '—'}</p>
                                                        <p class="text-xs text-slate-500 font-mono">{item.product?.sku ?? ''}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <span class="text-lg font-bold {item.quantity <= 0 ? 'text-red-400' : 'text-white'}">{item.quantity}</span>
                                                {#if item.product?.unit?.abbreviation}
                                                    <span class="text-slate-500 text-xs ml-1">{item.product.unit.abbreviation}</span>
                                                {/if}
                                            </td>
                                            <td class="px-4 py-3">
                                                {#if item.product}
                                                    <a href="/products/{item.product.uuid}" class="p-1.5 text-slate-500 hover:text-indigo-400 hover:bg-slate-700 rounded-md transition-colors inline-flex" title="Ver producto">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                    </a>
                                                {/if}
                                            </td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</AppLayout>
