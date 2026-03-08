<script>
    import { page } from '@inertiajs/svelte';
    import AppLayout from '@/Layouts/AppLayout.svelte';
    import Breadcrumb from '@/Components/Breadcrumb.svelte';

    let { category, products } = $props();

    const canWrite = $derived($page.props.auth?.user?.can_write ?? false);

    function formatCurrency(value) {
        if (value == null) return '—';
        return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(value);
    }

    const statusConfig = {
        active:   { label: 'Activo',    class: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' },
        inactive: { label: 'Inactivo',  class: 'bg-slate-500/10 text-slate-400 border border-slate-500/20' },
        draft:    { label: 'Borrador',  class: 'bg-amber-500/10 text-amber-400 border border-amber-500/20' },
        archived: { label: 'Archivado', class: 'bg-red-500/10 text-red-400 border border-red-500/20' },
    };
</script>

<svelte:head><title>{category.name} — Categoría</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/categories" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="flex items-center gap-3">
                    {#if category.color}
                        <div class="w-8 h-8 rounded-lg flex-shrink-0" style="background-color: {category.color}25; border: 1px solid {category.color}50">
                            {#if category.icon}
                                <span class="w-full h-full flex items-center justify-center text-base">{category.icon}</span>
                            {:else}
                                <div class="w-full h-full rounded-lg" style="background-color: {category.color}40"></div>
                            {/if}
                        </div>
                    {/if}
                    <div>
                        <h1 class="text-xl font-semibold text-white">{category.name}</h1>
                        {#if category.parent}
                            <p class="text-sm text-slate-400 mt-0.5">Subcategoría de <span class="text-slate-300">{category.parent.name}</span></p>
                        {:else}
                            <p class="text-sm text-slate-400 mt-0.5">{products.total} producto{products.total !== 1 ? 's' : ''}</p>
                        {/if}
                    </div>
                </div>
            </div>
            {#if canWrite}
                <a href="/categories/{category.uuid}/edit"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
            {/if}
        </div>

        <div class="p-8 space-y-6">
            <Breadcrumb items={[{ label: 'Categorías', href: '/categories' }, { label: category.name }]} />

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                <!-- Sidebar info -->
                <div class="space-y-4">
                    <!-- Info card -->
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-3">
                        <h3 class="text-sm font-semibold text-white">Información</h3>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-slate-500 text-xs uppercase tracking-wide mb-0.5">Estado</dt>
                                <dd>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {category.is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border border-slate-500/20'}">
                                        {category.is_active ? 'Activa' : 'Inactiva'}
                                    </span>
                                </dd>
                            </div>
                            {#if category.description}
                                <div>
                                    <dt class="text-slate-500 text-xs uppercase tracking-wide mb-0.5">Descripción</dt>
                                    <dd class="text-slate-400 text-xs leading-relaxed">{category.description}</dd>
                                </div>
                            {/if}
                            {#if category.parent}
                                <div>
                                    <dt class="text-slate-500 text-xs uppercase tracking-wide mb-0.5">Categoría padre</dt>
                                    <dd>
                                        <a href="/categories/{category.parent.uuid}" class="text-indigo-400 hover:text-indigo-300 transition-colors text-sm">
                                            {category.parent.name}
                                        </a>
                                    </dd>
                                </div>
                            {/if}
                        </dl>
                    </div>

                    <!-- Subcategorías -->
                    {#if category.children && category.children.length > 0}
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                            <h3 class="text-sm font-semibold text-white mb-3">Subcategorías</h3>
                            <div class="space-y-1.5">
                                {#each category.children as child}
                                    <a href="/categories/{child.uuid}"
                                        class="flex items-center gap-2.5 py-1.5 px-2 rounded-lg hover:bg-slate-800 transition-colors">
                                        {#if child.color}
                                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {child.color}"></span>
                                        {/if}
                                        <span class="text-sm text-slate-300">{child.name}</span>
                                        {#if !child.is_active}
                                            <span class="text-xs text-slate-600 ml-auto">Inactiva</span>
                                        {/if}
                                    </a>
                                {/each}
                            </div>
                        </div>
                    {/if}

                    <!-- Stat -->
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 text-center">
                        <p class="text-3xl font-bold text-white">{products.total}</p>
                        <p class="text-slate-400 text-sm mt-1">Productos en esta categoría</p>
                    </div>
                </div>

                <!-- Productos -->
                <div class="lg:col-span-3">
                    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-800">
                            <h3 class="text-sm font-semibold text-white">Productos</h3>
                        </div>

                        {#if products.data.length === 0}
                            <div class="py-16 text-center text-slate-500 text-sm">
                                <svg class="w-10 h-10 mx-auto mb-3 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                No hay productos en esta categoría.
                            </div>
                        {:else}
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-800">
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Producto</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">SKU</th>
                                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Stock</th>
                                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    {#each products.data as product}
                                        {@const st = statusConfig[product.status] ?? statusConfig.inactive}
                                        <tr class="hover:bg-slate-800/40 transition-colors">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-800 flex-shrink-0 border border-slate-700">
                                                        {#if product.cover_image?.url}
                                                            <img src={product.cover_image.url} alt={product.name} class="w-full h-full object-cover"/>
                                                        {:else}
                                                            <div class="w-full h-full flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                                </svg>
                                                            </div>
                                                        {/if}
                                                    </div>
                                                    <a href="/products/{product.uuid}" class="font-medium text-white hover:text-indigo-300 transition-colors truncate">{product.name}</a>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="font-mono text-xs text-slate-500">{product.sku ?? '—'}</span>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <span class="font-medium tabular-nums {product.stock_quantity <= 0 ? 'text-red-400' : 'text-white'}">
                                                    {product.stock_quantity}
                                                </span>
                                                {#if product.unit?.abbreviation}
                                                    <span class="text-slate-500 text-xs ml-1">{product.unit.abbreviation}</span>
                                                {/if}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <span class="text-slate-300 tabular-nums">{formatCurrency(product.sale_price)}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {st.class}">{st.label}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <a href="/products/{product.uuid}"
                                                    class="p-1.5 text-slate-500 hover:text-indigo-400 hover:bg-slate-700 rounded-md transition-colors inline-flex"
                                                    title="Ver producto">
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
                            {#if products.last_page > 1}
                                <div class="px-5 py-4 border-t border-slate-800 flex items-center justify-between text-sm text-slate-400">
                                    <span>Mostrando {products.from}–{products.to} de {products.total}</span>
                                    <div class="flex items-center gap-1">
                                        {#each products.links as link}
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
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</AppLayout>
