<script>
    import { router } from '@inertiajs/svelte';
    import AppLayout from '@/Layouts/AppLayout.svelte';

    let { period, rotation, sinMovimiento, valorizacion, totals } = $props();

    function formatCurrency(v) {
        if (v == null) return '—';
        return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(v);
    }

    function setPeriod(p) {
        router.get('/reports', { period: p }, { preserveState: true, replace: true });
    }

    const periods = [
        { value: '7',   label: '7 días' },
        { value: '30',  label: '30 días' },
        { value: '90',  label: '90 días' },
        { value: '180', label: '6 meses' },
        { value: '365', label: '1 año' },
    ];
</script>

<svelte:head><title>Reportes</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">Reportes</h1>
                <p class="text-sm text-slate-400 mt-0.5">Rotación de stock y valorización de inventario</p>
            </div>
            <!-- Selector de período -->
            <div class="flex items-center gap-1 bg-slate-800 border border-slate-700 rounded-lg p-1">
                {#each periods as p}
                    <button
                        onclick={() => setPeriod(p.value)}
                        class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors
                            {period === p.value
                                ? 'bg-indigo-600 text-white'
                                : 'text-slate-400 hover:text-white'}"
                    >
                        {p.label}
                    </button>
                {/each}
            </div>
        </div>

        <div class="px-8 py-6 space-y-8">

            <!-- ── Valorización — Resumen ──────────────────────── -->
            <section>
                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">Valorización de inventario</h2>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                        <p class="text-xs text-slate-500 uppercase tracking-wider">Productos con stock</p>
                        <p class="text-2xl font-bold text-white mt-1">{totals.productos}</p>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                        <p class="text-xs text-slate-500 uppercase tracking-wider">Valor a costo</p>
                        <p class="text-2xl font-bold text-white mt-1">{formatCurrency(totals.valor_costo)}</p>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                        <p class="text-xs text-slate-500 uppercase tracking-wider">Valor a precio venta</p>
                        <p class="text-2xl font-bold text-emerald-400 mt-1">{formatCurrency(totals.valor_venta)}</p>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                        <p class="text-xs text-slate-500 uppercase tracking-wider">Margen potencial</p>
                        <p class="text-2xl font-bold mt-1 {totals.margen >= 0 ? 'text-emerald-400' : 'text-red-400'}">
                            {totals.margen}%
                        </p>
                    </div>
                </div>

                <!-- Tabla top 20 por valor -->
                {#if valorizacion.length > 0}
                    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-800">
                            <h3 class="text-sm font-semibold text-slate-300">Top 20 productos por valor de inventario</h3>
                        </div>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-800">
                                    <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Producto</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Stock</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Costo unit.</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">P. Venta unit.</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Valor costo</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Valor venta</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                {#each valorizacion as item}
                                    <tr class="hover:bg-slate-800/40 transition-colors">
                                        <td class="px-4 py-2.5">
                                            <a href="/products/{item.uuid}" class="font-medium text-white hover:text-indigo-300 transition-colors">
                                                {item.name}
                                            </a>
                                            {#if item.sku}
                                                <span class="ml-2 text-xs text-slate-500 font-mono">{item.sku}</span>
                                            {/if}
                                        </td>
                                        <td class="px-4 py-2.5 text-right text-slate-300 tabular-nums">
                                            {item.stock_quantity} <span class="text-slate-600 text-xs">{item.unit_abbr ?? ''}</span>
                                        </td>
                                        <td class="px-4 py-2.5 text-right text-slate-400 tabular-nums">{formatCurrency(item.cost_price)}</td>
                                        <td class="px-4 py-2.5 text-right text-slate-400 tabular-nums">{formatCurrency(item.sale_price)}</td>
                                        <td class="px-4 py-2.5 text-right text-white font-medium tabular-nums">{formatCurrency(item.valor_costo)}</td>
                                        <td class="px-4 py-2.5 text-right text-emerald-400 font-medium tabular-nums">{formatCurrency(item.valor_venta)}</td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                {/if}
            </section>

            <!-- ── Rotación de stock ───────────────────────────── -->
            <section>
                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">
                    Rotación de stock — últimos {periods.find(p => p.value === period)?.label ?? period + ' días'}
                </h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Más vendidos / rotados -->
                    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-800 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <h3 class="text-sm font-semibold text-slate-300">Mayor rotación (más salidas)</h3>
                        </div>

                        {#if rotation.length === 0}
                            <div class="py-10 text-center text-slate-500 text-sm">
                                Sin movimientos de salida en el período
                            </div>
                        {:else}
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-800">
                                        <th class="text-left px-4 py-2.5 text-xs text-slate-500 uppercase tracking-wider">Producto</th>
                                        <th class="text-right px-4 py-2.5 text-xs text-slate-500 uppercase tracking-wider">Salidas</th>
                                        <th class="text-right px-4 py-2.5 text-xs text-slate-500 uppercase tracking-wider">Movimientos</th>
                                        <th class="text-right px-4 py-2.5 text-xs text-slate-500 uppercase tracking-wider">Stock actual</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    {#each rotation as item, i}
                                        <tr class="hover:bg-slate-800/40 transition-colors">
                                            <td class="px-4 py-2.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-mono text-slate-600 w-5">{i + 1}</span>
                                                    <a href="/products/{item.uuid}" class="text-white hover:text-indigo-300 transition-colors truncate max-w-36">
                                                        {item.name}
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 text-right font-semibold text-emerald-400 tabular-nums">
                                                {item.total_salidas} <span class="text-slate-600 text-xs">{item.unit_abbr ?? ''}</span>
                                            </td>
                                            <td class="px-4 py-2.5 text-right text-slate-400 tabular-nums">{item.num_movimientos}</td>
                                            <td class="px-4 py-2.5 text-right tabular-nums">
                                                <span class="{item.stock_quantity <= 0 ? 'text-red-400' : item.stock_quantity <= item.min_stock && item.min_stock > 0 ? 'text-amber-400' : 'text-slate-300'}">
                                                    {item.stock_quantity}
                                                </span>
                                            </td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        {/if}
                    </div>

                    <!-- Stock sin movimiento (inventario muerto) -->
                    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-800 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <h3 class="text-sm font-semibold text-slate-300">Sin movimiento (inventario estancado)</h3>
                        </div>

                        {#if sinMovimiento.length === 0}
                            <div class="py-10 text-center text-slate-500 text-sm">
                                Todos los productos tuvieron movimiento en el período
                            </div>
                        {:else}
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-800">
                                        <th class="text-left px-4 py-2.5 text-xs text-slate-500 uppercase tracking-wider">Producto</th>
                                        <th class="text-right px-4 py-2.5 text-xs text-slate-500 uppercase tracking-wider">Stock detenido</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    {#each sinMovimiento as item}
                                        <tr class="hover:bg-slate-800/40 transition-colors">
                                            <td class="px-4 py-2.5">
                                                <a href="/products/{item.uuid}" class="text-white hover:text-indigo-300 transition-colors">
                                                    {item.name}
                                                </a>
                                                {#if item.sku}
                                                    <span class="ml-2 text-xs text-slate-500 font-mono">{item.sku}</span>
                                                {/if}
                                            </td>
                                            <td class="px-4 py-2.5 text-right font-semibold text-amber-400 tabular-nums">
                                                {item.stock_quantity}
                                            </td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        {/if}
                    </div>
                </div>
            </section>

        </div>
    </div>
</AppLayout>
