<script>
    import { page } from '@inertiajs/svelte';
    import AppLayout from '@/Layouts/AppLayout.svelte';
    import Breadcrumb from '@/Components/Breadcrumb.svelte';

    let { supplier } = $props();

    const canWrite = $derived($page.props.auth?.user?.can_write ?? false);

    const products = $derived(supplier.products ?? []);

    const statusConfig = {
        active:   { label: 'Activo',    class: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' },
        inactive: { label: 'Inactivo',  class: 'bg-slate-500/10 text-slate-400 border border-slate-500/20' },
        draft:    { label: 'Borrador',  class: 'bg-amber-500/10 text-amber-400 border border-amber-500/20' },
        archived: { label: 'Archivado', class: 'bg-red-500/10 text-red-400 border border-red-500/20' },
    };

    function fmt(val) {
        if (val == null) return '—';
        return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(val);
    }

    function stockBadge(p) {
        if (p.stock_quantity <= 0) return { label: 'Sin stock', class: 'bg-red-500/10 text-red-400 border border-red-500/20' };
        if (p.min_stock > 0 && p.stock_quantity <= p.min_stock) return { label: 'Bajo', class: 'bg-amber-500/10 text-amber-400 border border-amber-500/20' };
        return null;
    }
</script>

<svelte:head><title>{supplier.name} — Proveedor</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/suppliers" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-semibold text-white">{supplier.name}</h1>
                    <p class="text-sm text-slate-400 mt-0.5">{products.length} producto{products.length !== 1 ? 's' : ''} asociado{products.length !== 1 ? 's' : ''}</p>
                </div>
            </div>
            {#if canWrite}
                <a href="/suppliers/{supplier.uuid}/edit"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
            {/if}
        </div>

        <div class="p-8 space-y-6">
            <Breadcrumb items={[{ label: 'Proveedores', href: '/suppliers' }, { label: supplier.name }]} />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Info del proveedor -->
                <div class="space-y-4">
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-indigo-600/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-white">{supplier.name}</p>
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {supplier.is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border border-slate-500/20'}">
                                    {supplier.is_active ? 'Activo' : 'Inactivo'}
                                </span>
                            </div>
                        </div>

                        <dl class="space-y-3 text-sm">
                            {#if supplier.contact_name}
                                <div>
                                    <dt class="text-slate-500 text-xs uppercase tracking-wide mb-0.5">Contacto</dt>
                                    <dd class="text-slate-300">{supplier.contact_name}</dd>
                                </div>
                            {/if}
                            {#if supplier.email}
                                <div>
                                    <dt class="text-slate-500 text-xs uppercase tracking-wide mb-0.5">Email</dt>
                                    <dd><a href="mailto:{supplier.email}" class="text-indigo-400 hover:text-indigo-300 transition-colors">{supplier.email}</a></dd>
                                </div>
                            {/if}
                            {#if supplier.phone}
                                <div>
                                    <dt class="text-slate-500 text-xs uppercase tracking-wide mb-0.5">Teléfono</dt>
                                    <dd class="text-slate-300">{supplier.phone}</dd>
                                </div>
                            {/if}
                            {#if supplier.address}
                                <div>
                                    <dt class="text-slate-500 text-xs uppercase tracking-wide mb-0.5">Dirección</dt>
                                    <dd class="text-slate-400 text-xs">{supplier.address}</dd>
                                </div>
                            {/if}
                            {#if supplier.notes}
                                <div>
                                    <dt class="text-slate-500 text-xs uppercase tracking-wide mb-0.5">Notas</dt>
                                    <dd class="text-slate-400 text-xs">{supplier.notes}</dd>
                                </div>
                            {/if}
                        </dl>
                    </div>

                    <!-- Stat card -->
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 text-center">
                        <p class="text-3xl font-bold text-white">{products.length}</p>
                        <p class="text-slate-400 text-sm mt-1">Productos registrados</p>
                    </div>
                </div>

                <!-- Productos del proveedor -->
                <div class="lg:col-span-2">
                    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-800">
                            <h3 class="text-sm font-semibold text-white">Productos del proveedor</h3>
                        </div>
                        {#if products.length === 0}
                            <div class="py-12 text-center text-slate-500 text-sm">
                                Este proveedor no tiene productos registrados.
                            </div>
                        {:else}
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-800">
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Producto</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">SKU</th>
                                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Stock</th>
                                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    {#each products as p}
                                        {@const badge = stockBadge(p)}
                                        {@const pst = statusConfig[p.status] ?? statusConfig.inactive}
                                        <tr class="hover:bg-slate-800/40 transition-colors">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-800 flex-shrink-0 border border-slate-700">
                                                        {#if p.cover_image?.url}
                                                            <img src={p.cover_image.url} alt={p.name} class="w-full h-full object-cover"/>
                                                        {:else}
                                                            <div class="w-full h-full flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                                </svg>
                                                            </div>
                                                        {/if}
                                                    </div>
                                                    <span class="font-medium text-white truncate max-w-[160px]">{p.name}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-slate-500 font-mono text-xs">{p.sku ?? '—'}</td>
                                            <td class="px-4 py-3 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    {#if badge}
                                                        <span class="inline-flex px-1.5 py-0.5 rounded-full text-[10px] font-medium {badge.class}">{badge.label}</span>
                                                    {/if}
                                                    <span class="font-semibold {p.stock_quantity <= 0 ? 'text-red-400' : p.min_stock > 0 && p.stock_quantity <= p.min_stock ? 'text-amber-400' : 'text-white'}">{p.stock_quantity}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right text-slate-300">{fmt(p.sale_price)}</td>
                                            <td class="px-4 py-3">
                                                <a href="/products/{p.uuid}" class="p-1.5 text-slate-500 hover:text-indigo-400 hover:bg-slate-700 rounded-md transition-colors inline-flex" title="Ver detalle">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
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
