<script>
    import AppLayout from '../Layouts/AppLayout.svelte';

    let { stats = {}, recentMovements = [], criticalStock = [] } = $props();

    const statCards = $derived([
        {
            label: 'Total productos',
            value: stats.total_products ?? 0,
            icon: 'package',
            color: 'indigo',
            href: '/products',
        },
        {
            label: 'Valor en inventario',
            value: `$${Number(stats.total_value ?? 0).toLocaleString('es-CO', { minimumFractionDigits: 0 })}`,
            icon: 'currency',
            color: 'emerald',
            href: null,
        },
        {
            label: 'Stock bajo mínimo',
            value: stats.low_stock_count ?? 0,
            icon: 'warning',
            color: 'amber',
            href: '/products?low_stock=1',
        },
        {
            label: 'Sin stock',
            value: stats.out_of_stock_count ?? 0,
            icon: 'empty',
            color: 'red',
            href: '/products',
        },
        {
            label: 'Bodegas activas',
            value: stats.total_warehouses ?? 0,
            icon: 'warehouse',
            color: 'slate',
            href: '/warehouses',
        },
        {
            label: 'Traslados pendientes',
            value: stats.pending_transfers ?? 0,
            icon: 'transfer',
            color: 'violet',
            href: '/transfers',
        },
        {
            label: 'Categorías',
            value: stats.total_categories ?? 0,
            icon: 'tag',
            color: 'sky',
            href: '/categories',
        },
        {
            label: 'Proveedores activos',
            value: stats.total_suppliers ?? 0,
            icon: 'truck',
            color: 'teal',
            href: '/suppliers',
        },
    ]);

    const movementColors = {
        in:         'text-emerald-400 bg-emerald-500/10',
        out:        'text-red-400 bg-red-500/10',
        return:     'text-blue-400 bg-blue-500/10',
        loss:       'text-orange-400 bg-orange-500/10',
        adjustment: 'text-slate-300 bg-slate-700/50',
    };

    function formatDate(str) {
        return new Date(str).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
    }
</script>

<AppLayout>
    <div class="flex-1 overflow-y-auto">
        <!-- Page header -->
        <div class="border-b border-slate-800 px-8 py-5">
            <h1 class="text-xl font-semibold text-white">Dashboard</h1>
            <p class="text-sm text-slate-400 mt-0.5">Resumen general del inventario</p>
        </div>

        <div class="p-8 space-y-8">
            <!-- Stat cards -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                {#each statCards as card}
                    <svelte:element
                        this={card.href ? 'a' : 'div'}
                        href={card.href ?? undefined}
                        class="bg-slate-900 border border-slate-800 rounded-xl p-5 {card.href ? 'hover:border-slate-700 transition-colors cursor-pointer' : ''}"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-xs text-slate-400 truncate">{card.label}</p>
                                <p class="text-2xl font-bold text-white mt-1 truncate">{card.value}</p>
                            </div>
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0
                                {card.color === 'indigo'  ? 'bg-indigo-500/15 text-indigo-400'  : ''}
                                {card.color === 'emerald' ? 'bg-emerald-500/15 text-emerald-400': ''}
                                {card.color === 'amber'   ? 'bg-amber-500/15 text-amber-400'   : ''}
                                {card.color === 'red'     ? 'bg-red-500/15 text-red-400'       : ''}
                                {card.color === 'slate'   ? 'bg-slate-700/50 text-slate-300'   : ''}
                                {card.color === 'violet'  ? 'bg-violet-500/15 text-violet-400' : ''}
                                {card.color === 'sky'     ? 'bg-sky-500/15 text-sky-400'       : ''}
                                {card.color === 'teal'    ? 'bg-teal-500/15 text-teal-400'     : ''}
                            ">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {#if card.icon === 'package'}
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    {:else if card.icon === 'currency'}
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    {:else if card.icon === 'warning'}
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    {:else if card.icon === 'empty'}
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    {:else if card.icon === 'warehouse'}
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    {:else if card.icon === 'transfer'}
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    {:else if card.icon === 'tag'}
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    {:else if card.icon === 'truck'}
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                    {/if}
                                </svg>
                            </div>
                        </div>
                    </svelte:element>
                {/each}
            </div>

            <!-- Alertas de stock crítico -->
            {#if criticalStock.length > 0}
                <div class="bg-slate-900 border border-slate-800 rounded-xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-red-400 animate-pulse"></div>
                            <h2 class="text-sm font-semibold text-white">Alertas de stock</h2>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">
                                {criticalStock.length} producto{criticalStock.length !== 1 ? 's' : ''}
                            </span>
                        </div>
                        <a href="/products?low_stock=1" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">Ver todos →</a>
                    </div>
                    <div class="divide-y divide-slate-800">
                        {#each criticalStock as item}
                            <a href="/products/{item.uuid}" class="flex items-center gap-3 px-6 py-3 hover:bg-slate-800/40 transition-colors">
                                <!-- Thumbnail -->
                                <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-800 flex-shrink-0 border border-slate-700">
                                    {#if item.cover_url}
                                        <img src={item.cover_url} alt={item.name} class="w-full h-full object-cover"/>
                                    {:else}
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                    {/if}
                                </div>
                                <!-- Nombre -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-slate-200 truncate">{item.name}</p>
                                    {#if item.sku}
                                        <p class="text-xs text-slate-500 font-mono">{item.sku}</p>
                                    {/if}
                                </div>
                                <!-- Badge estado -->
                                {#if item.status === 'out'}
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20 flex-shrink-0">Sin stock</span>
                                {:else}
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20 flex-shrink-0">Stock bajo</span>
                                {/if}
                                <!-- Cantidad -->
                                <span class="text-sm font-bold tabular-nums {item.status === 'out' ? 'text-red-400' : 'text-amber-400'} flex-shrink-0 w-12 text-right">
                                    {item.stock_quantity}{item.unit_abbr ? ` ${item.unit_abbr}` : ''}
                                </span>
                            </a>
                        {/each}
                    </div>
                </div>
            {/if}

            <!-- Últimos movimientos -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800">
                    <h2 class="text-sm font-semibold text-white">Últimos movimientos de stock</h2>
                    <a href="/stock-movements" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">Ver todos →</a>
                </div>

                {#if recentMovements.length === 0}
                    <div class="flex items-center justify-center py-14 text-slate-500">
                        <div class="text-center">
                            <svg class="w-10 h-10 mx-auto mb-3 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <p class="text-sm">Sin movimientos registrados</p>
                        </div>
                    </div>
                {:else}
                    <div class="divide-y divide-slate-800">
                        {#each recentMovements as m}
                            <div class="flex items-center gap-4 px-6 py-3 hover:bg-slate-800/40 transition-colors">
                                <!-- Tipo badge -->
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold min-w-[80px] justify-center {movementColors[m.type] ?? 'text-slate-400 bg-slate-700/50'}">
                                    {m.type_label}
                                </span>
                                <!-- Producto -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-slate-200 truncate">{m.product_name ?? '—'}</p>
                                    {#if m.product_sku}
                                        <p class="text-xs text-slate-500">{m.product_sku}</p>
                                    {/if}
                                </div>
                                <!-- Cantidad -->
                                <span class="text-sm font-semibold tabular-nums {m.quantity >= 0 ? 'text-emerald-400' : 'text-red-400'}">
                                    {m.quantity >= 0 ? '+' : ''}{m.quantity}
                                </span>
                                <!-- Stock antes → después -->
                                <span class="text-xs text-slate-500 tabular-nums hidden sm:block">
                                    {m.quantity_before} → {m.quantity_after}
                                </span>
                                <!-- Usuario + fecha -->
                                <div class="text-right hidden md:block">
                                    <p class="text-xs text-slate-400">{m.user_name ?? '—'}</p>
                                    <p class="text-xs text-slate-600">{formatDate(m.created_at)}</p>
                                </div>
                            </div>
                        {/each}
                    </div>
                {/if}
            </div>
        </div>
    </div>
</AppLayout>
