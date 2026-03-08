<script>
    import { page } from '@inertiajs/svelte';
    import AppLayout from '@/Layouts/AppLayout.svelte';
    import Breadcrumb from '@/Components/Breadcrumb.svelte';

    let { product, recentMovements = [] } = $props();

    const canWrite = $derived($page.props.auth?.user?.can_write ?? false);

    const images    = $derived(product.images ?? []);
    const coverImg  = $derived(images.find(i => i.is_cover) ?? images[0] ?? null);
    let activeImg   = $state(null);
    const displayed = $derived(activeImg ?? coverImg);

    const typeStyle = {
        in:         'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
        out:        'bg-red-500/10 text-red-400 border border-red-500/20',
        adjustment: 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
        return:     'bg-amber-500/10 text-amber-400 border border-amber-500/20',
        loss:       'bg-orange-500/10 text-orange-400 border border-orange-500/20',
    };
    const typeLabel = { in: 'Entrada', out: 'Salida', adjustment: 'Ajuste', return: 'Devolución', loss: 'Pérdida' };

    const statusConfig = {
        active:   { label: 'Activo',    class: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' },
        inactive: { label: 'Inactivo',  class: 'bg-slate-500/10 text-slate-400 border border-slate-500/20' },
        draft:    { label: 'Borrador',  class: 'bg-amber-500/10 text-amber-400 border border-amber-500/20' },
        archived: { label: 'Archivado', class: 'bg-red-500/10 text-red-400 border border-red-500/20' },
    };
    const st = $derived(statusConfig[product.status_value ?? product.status] ?? statusConfig.inactive);

    function stockClass(qty) {
        if (qty <= 0) return 'text-red-400';
        if (product.min_stock > 0 && qty <= product.min_stock) return 'text-amber-400';
        return 'text-emerald-400';
    }

    function fmt(val) {
        if (val == null) return '—';
        return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(val);
    }

    function fmtDate(d) {
        return new Date(d).toLocaleString('es-CO', { dateStyle: 'medium', timeStyle: 'short' });
    }
</script>

<svelte:head><title>{product.name} — Producto</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/products" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-semibold text-white">{product.name}</h1>
                    <p class="text-sm text-slate-400 mt-0.5">SKU: <span class="font-mono">{product.sku ?? '—'}</span></p>
                </div>
            </div>
            {#if canWrite}
                <a href="/products/{product.uuid}/edit"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
            {/if}
        </div>

        <div class="p-8">
            <Breadcrumb items={[{ label: 'Productos', href: '/products' }, { label: product.name }]} />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Galería -->
                <div class="lg:col-span-1 space-y-3">
                    <!-- Imagen principal -->
                    <div class="aspect-square rounded-xl overflow-hidden bg-slate-900 border border-slate-800">
                        {#if displayed}
                            <img src={displayed.url} alt={displayed.alt_text ?? product.name}
                                class="w-full h-full object-cover" />
                        {:else}
                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-700 gap-3">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <span class="text-sm">Sin imágenes</span>
                            </div>
                        {/if}
                    </div>

                    <!-- Thumbnails -->
                    {#if images.length > 1}
                        <div class="grid grid-cols-4 gap-2">
                            {#each images as img}
                                <button
                                    onclick={() => activeImg = img}
                                    class="aspect-square rounded-lg overflow-hidden border-2 transition-colors
                                        {displayed?.id === img.id ? 'border-indigo-500' : 'border-slate-700 hover:border-slate-500'}"
                                >
                                    <img src={img.url} alt={img.alt_text} class="w-full h-full object-cover" />
                                </button>
                            {/each}
                        </div>
                    {/if}

                    <!-- Badges de estado -->
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {st.class}">{st.label}</span>
                        {#if product.featured}
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                Destacado
                            </span>
                        {/if}
                        {#if product.stock_quantity <= 0}
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">Sin stock</span>
                        {:else if product.min_stock > 0 && product.stock_quantity <= product.min_stock}
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">Stock bajo</span>
                        {/if}
                    </div>
                </div>

                <!-- Info principal -->
                <div class="lg:col-span-2 space-y-5">

                    <!-- Descripción -->
                    {#if product.short_description || product.description}
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-2">
                            {#if product.short_description}
                                <p class="text-slate-300 text-sm">{product.short_description}</p>
                            {/if}
                            {#if product.description}
                                <p class="text-slate-500 text-sm">{product.description}</p>
                            {/if}
                        </div>
                    {/if}

                    <!-- Precios y stock -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                            <p class="text-xs text-slate-500 mb-1">Precio venta</p>
                            <p class="text-lg font-semibold text-white">{fmt(product.sale_price)}</p>
                        </div>
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                            <p class="text-xs text-slate-500 mb-1">Precio costo</p>
                            <p class="text-lg font-semibold text-slate-300">{fmt(product.cost_price)}</p>
                        </div>
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                            <p class="text-xs text-slate-500 mb-1">Stock actual</p>
                            <p class="text-2xl font-bold {stockClass(product.stock_quantity)}">{product.stock_quantity}</p>
                        </div>
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                            <p class="text-xs text-slate-500 mb-1">Stock mínimo</p>
                            <p class="text-2xl font-bold text-slate-400">{product.min_stock ?? 0}</p>
                        </div>
                    </div>

                    <!-- Detalles -->
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                        <h3 class="text-sm font-semibold text-white mb-4">Detalles</h3>
                        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div>
                                <dt class="text-slate-500">Código de barras</dt>
                                <dd class="text-slate-300 font-mono mt-0.5">{product.barcode ?? '—'}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Unidad</dt>
                                <dd class="text-slate-300 mt-0.5">{product.unit?.name ?? '—'}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Proveedor</dt>
                                <dd class="mt-0.5">
                                    {#if product.supplier}
                                        <a href="/suppliers/{product.supplier.uuid}"
                                            class="text-indigo-400 hover:text-indigo-300 transition-colors">
                                            {product.supplier.name}
                                        </a>
                                    {:else}
                                        <span class="text-slate-500">—</span>
                                    {/if}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Categorías</dt>
                                <dd class="mt-0.5 flex flex-wrap gap-1">
                                    {#if product.categories?.length}
                                        {#each product.categories as cat}
                                            <span class="text-xs px-2 py-0.5 bg-slate-800 text-slate-300 rounded-full">{cat.name}</span>
                                        {/each}
                                    {:else}
                                        <span class="text-slate-500">—</span>
                                    {/if}
                                </dd>
                            </div>
                            {#if product.tax_rate}
                                <div>
                                    <dt class="text-slate-500">IVA</dt>
                                    <dd class="text-slate-300 mt-0.5">{product.tax_rate}%</dd>
                                </div>
                            {/if}
                            {#if product.weight}
                                <div>
                                    <dt class="text-slate-500">Peso</dt>
                                    <dd class="text-slate-300 mt-0.5">{product.weight} kg</dd>
                                </div>
                            {/if}
                            <div>
                                <dt class="text-slate-500">Creado por</dt>
                                <dd class="text-slate-300 mt-0.5">{product.created_by_user?.name ?? '—'}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Actualizado</dt>
                                <dd class="text-slate-400 mt-0.5 text-xs">{fmtDate(product.updated_at)}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Movimientos recientes -->
                    {#if recentMovements.length > 0}
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold text-white">Movimientos recientes</h3>
                                <a href="/stock-movements?search={product.sku}" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">Ver todos →</a>
                            </div>
                            <div class="space-y-2">
                                {#each recentMovements as mov}
                                    <div class="flex items-center gap-3 py-2 border-b border-slate-800 last:border-0">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {typeStyle[mov.type?.value ?? mov.type] ?? typeStyle.adjustment}">
                                            {typeLabel[mov.type?.value ?? mov.type] ?? mov.type}
                                        </span>
                                        <span class="text-sm font-semibold {mov.quantity > 0 ? 'text-emerald-400' : 'text-red-400'}">
                                            {mov.quantity > 0 ? '+' : ''}{mov.quantity}
                                        </span>
                                        <span class="text-slate-500 text-xs flex-1">
                                            {mov.quantity_before} → {mov.quantity_after}
                                            {#if mov.warehouse?.name}
                                                · {mov.warehouse.name}
                                            {/if}
                                        </span>
                                        <span class="text-slate-600 text-xs">{fmtDate(mov.created_at)}</span>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</AppLayout>
