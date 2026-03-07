<script>
    import { router } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';

    let { products, filters = {}, statuses = [] } = $props();

    let search     = $state(filters.search     ?? '');
    let status     = $state(filters.status     ?? '');
    let low_stock  = $state(filters.low_stock  ?? false);
    let deleting   = $state(null);

    let searchTimer;
    function onSearchInput() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilters, 350);
    }

    function applyFilters() {
        router.get('/products', {
            search:    search    || undefined,
            status:    status    || undefined,
            low_stock: low_stock || undefined,
        }, { preserveState: true, replace: true });
    }

    function confirmDelete(product) {
        deleting = product;
    }

    function cancelDelete() {
        deleting = null;
    }

    function doDelete() {
        if (!deleting) return;
        router.delete(`/products/${deleting.uuid}`, {
            onFinish: () => { deleting = null; },
        });
    }

    const statusConfig = {
        active:   { label: 'Activo',     class: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' },
        inactive: { label: 'Inactivo',   class: 'bg-slate-500/10 text-slate-400 border border-slate-500/20' },
        draft:    { label: 'Borrador',   class: 'bg-amber-500/10 text-amber-400 border border-amber-500/20' },
        archived: { label: 'Archivado',  class: 'bg-red-500/10 text-red-400 border border-red-500/20' },
    };

    function stockBadge(product) {
        if (product.stock_quantity <= 0)
            return { label: 'Sin stock', class: 'bg-red-500/10 text-red-400 border border-red-500/20' };
        if (product.min_stock > 0 && product.stock_quantity <= product.min_stock)
            return { label: 'Stock bajo', class: 'bg-amber-500/10 text-amber-400 border border-amber-500/20' };
        return null;
    }

    function formatCurrency(value) {
        if (value == null) return '—';
        return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
    }
</script>

<svelte:head><title>Productos</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">Productos</h1>
                <p class="text-sm text-slate-400 mt-0.5">{products.total} producto{products.total !== 1 ? 's' : ''} registrado{products.total !== 1 ? 's' : ''}</p>
            </div>
            <a
                href="/products/create"
                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo producto
            </a>
        </div>

        <!-- Filters -->
        <div class="px-8 py-4 border-b border-slate-800 flex items-center gap-3 flex-wrap">
            <!-- Search -->
            <div class="relative flex-1 min-w-[220px] max-w-xs">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input
                    type="text"
                    bind:value={search}
                    oninput={onSearchInput}
                    placeholder="Buscar por nombre, SKU..."
                    class="w-full pl-9 pr-3.5 py-2 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"
                />
            </div>

            <!-- Status filter -->
            <select
                bind:value={status}
                onchange={applyFilters}
                class="px-3.5 py-2 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"
            >
                <option value="">Todos los estados</option>
                {#each statuses as s}
                    <option value={s.value}>{s.label}</option>
                {/each}
            </select>

            <!-- Low stock toggle -->
            <label class="flex items-center gap-2 cursor-pointer px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-lg text-sm text-slate-300 select-none">
                <input
                    type="checkbox"
                    bind:checked={low_stock}
                    onchange={applyFilters}
                    class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900"
                />
                Solo stock bajo
            </label>
        </div>

        <!-- Table -->
        <div class="px-8 py-6">
            {#if products.data.length === 0}
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <svg class="w-12 h-12 text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="text-slate-400 font-medium">No se encontraron productos</p>
                    <p class="text-slate-600 text-sm mt-1">Prueba ajustando los filtros o crea uno nuevo</p>
                </div>
            {:else}
                <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800">
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Producto</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">SKU</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Stock</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio venta</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Categorías</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            {#each products.data as product}
                                {@const badge = stockBadge(product)}
                                {@const st = statusConfig[product.status] ?? statusConfig.inactive}
                                <tr class="hover:bg-slate-800/50 transition-colors">
                                    <!-- Name -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            {#if product.featured}
                                                <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                </svg>
                                            {/if}
                                            <div>
                                                <p class="font-medium text-white">{product.name}</p>
                                                {#if product.supplier}
                                                    <p class="text-xs text-slate-500">{product.supplier.name}</p>
                                                {/if}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- SKU -->
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs text-slate-400">{product.sku ?? '—'}</span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {st.class}">{st.label}</span>
                                    </td>

                                    <!-- Stock -->
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            {#if badge}
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {badge.class}">{badge.label}</span>
                                            {/if}
                                            <span class="text-white font-medium tabular-nums">
                                                {product.stock_quantity}
                                                {#if product.unit}<span class="text-slate-500 text-xs">{product.unit.abbreviation}</span>{/if}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Price -->
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-white font-medium tabular-nums">{formatCurrency(product.sale_price)}</span>
                                    </td>

                                    <!-- Categories -->
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            {#each (product.categories ?? []).slice(0, 2) as cat}
                                                <span
                                                    class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium text-white"
                                                    style="background-color: {cat.color ?? '#6366f1'}30; border: 1px solid {cat.color ?? '#6366f1'}50; color: {cat.color ?? '#818cf8'}"
                                                >{cat.name}</span>
                                            {/each}
                                            {#if (product.categories ?? []).length > 2}
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs text-slate-500">+{product.categories.length - 2}</span>
                                            {/if}
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <a
                                                href="/products/{product.uuid}/edit"
                                                class="p-1.5 text-slate-500 hover:text-indigo-400 hover:bg-slate-700 rounded-md transition-colors"
                                                title="Editar"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <button
                                                onclick={() => confirmDelete(product)}
                                                class="p-1.5 text-slate-500 hover:text-red-400 hover:bg-slate-700 rounded-md transition-colors"
                                                title="Eliminar"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                {#if products.last_page > 1}
                    <div class="mt-4 flex items-center justify-between text-sm text-slate-400">
                        <span>Mostrando {products.from}–{products.to} de {products.total}</span>
                        <div class="flex items-center gap-1">
                            {#each products.links as link}
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

<!-- Delete modal -->
{#if deleting}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick={cancelDelete}></div>
        <div class="relative bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-sm shadow-2xl">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-500/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-white">Eliminar producto</h3>
                    <p class="text-sm text-slate-400 mt-0.5">Esta acción no se puede deshacer</p>
                </div>
            </div>
            <p class="text-sm text-slate-300 mb-6">
                ¿Estás seguro de que deseas eliminar <span class="font-semibold text-white">"{deleting.name}"</span>?
            </p>
            <div class="flex gap-3">
                <button
                    onclick={doDelete}
                    class="flex-1 py-2.5 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-lg transition-colors"
                >
                    Eliminar
                </button>
                <button
                    onclick={cancelDelete}
                    class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-lg transition-colors"
                >
                    Cancelar
                </button>
            </div>
        </div>
    </div>
{/if}
