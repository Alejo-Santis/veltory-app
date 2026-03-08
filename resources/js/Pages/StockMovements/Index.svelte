<script>
    import { page, useForm, router } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';

    let { movements, filters = {}, types = [], products = [], warehouses = [] } = $props();

    const canWrite = $derived($page.props.auth?.user?.can_write ?? false);

    // ── Filtros ───────────────────────────────────────────────
    let search = $state(filters.search ?? '');
    let type   = $state(filters.type   ?? '');
    let from   = $state(filters.from   ?? '');
    let to     = $state(filters.to     ?? '');

    let searchTimer;
    function onSearchInput() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilters, 350);
    }

    function applyFilters() {
        router.get('/stock-movements', {
            search: search || undefined,
            type:   type   || undefined,
            from:   from   || undefined,
            to:     to     || undefined,
        }, { preserveState: true, replace: true });
    }

    // ── Modal nuevo movimiento ────────────────────────────────
    let showModal      = $state(false);
    let selectedProduct = $state(null);

    const form = useForm({
        type:         'in',
        quantity:     1,
        warehouse_id: null,
        unit_cost:    '',
        reference:    '',
        notes:        '',
    });

    function openModal(product = null) {
        selectedProduct = product;
        $form.reset();
        $form.clearErrors();
        $form.type     = 'in';
        $form.quantity = 1;
        showModal = true;
    }

    function closeModal() {
        showModal       = false;
        selectedProduct = null;
        $form.reset();
        $form.clearErrors();
    }

    function submitMovement(e) {
        e.preventDefault();
        if (!selectedProduct) return;
        $form.post(`/products/${selectedProduct.uuid}/stock`, {
            onSuccess: closeModal,
        });
    }

    // ── Helpers ───────────────────────────────────────────────
    const typeMap = Object.fromEntries(types.map(t => [t.value, t]));

    const typeStyle = {
        in:         'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
        out:        'bg-red-500/10 text-red-400 border border-red-500/20',
        adjustment: 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
        return:     'bg-amber-500/10 text-amber-400 border border-amber-500/20',
        loss:       'bg-orange-500/10 text-orange-400 border border-orange-500/20',
    };

    function qtyClass(qty) {
        if (qty > 0) return 'text-emerald-400';
        if (qty < 0) return 'text-red-400';
        return 'text-slate-400';
    }

    function formatDate(dateStr) {
        return new Date(dateStr).toLocaleString('es-CO', {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit',
        });
    }
</script>

<svelte:head><title>Movimientos de stock</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">Movimientos de stock</h1>
                <p class="text-sm text-slate-400 mt-0.5">{movements.total} movimiento{movements.total !== 1 ? 's' : ''} registrado{movements.total !== 1 ? 's' : ''}</p>
            </div>
            <div class="flex items-center gap-2">
                {#if canWrite}
                    <!-- Export dropdown -->
                    <div class="relative group">
                        <button class="flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-lg border border-slate-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Exportar
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="absolute right-0 top-full mt-1 w-44 bg-slate-800 border border-slate-700 rounded-lg shadow-xl z-10 overflow-hidden opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                            <a href="/exports/movements/excel?search={search}&type={type}&from={from}&to={to}" target="_blank"
                                class="flex items-center gap-2 px-3 py-2.5 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Excel (.xlsx)
                            </a>
                            <a href="/exports/movements/pdf?search={search}&type={type}&from={from}&to={to}" target="_blank"
                                class="flex items-center gap-2 px-3 py-2.5 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                PDF
                            </a>
                        </div>
                    </div>
                    <button
                        onclick={() => openModal()}
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Registrar movimiento
                    </button>
                {/if}
            </div>
        </div>

        <!-- Filtros -->
        <div class="px-8 py-4 border-b border-slate-800 flex flex-wrap items-center gap-3">
            <!-- Búsqueda -->
            <div class="relative flex-1 min-w-52">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    bind:value={search}
                    oninput={onSearchInput}
                    placeholder="Buscar por producto o SKU…"
                    class="w-full pl-9 pr-4 py-2 bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"
                />
            </div>

            <!-- Tipo -->
            <select
                bind:value={type}
                onchange={applyFilters}
                class="px-3 py-2 bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"
            >
                <option value="">Todos los tipos</option>
                {#each types as t}
                    <option value={t.value}>{t.label}</option>
                {/each}
            </select>

            <!-- Desde -->
            <input
                type="date"
                bind:value={from}
                onchange={applyFilters}
                class="px-3 py-2 bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"
                title="Desde"
            />

            <!-- Hasta -->
            <input
                type="date"
                bind:value={to}
                onchange={applyFilters}
                class="px-3 py-2 bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"
                title="Hasta"
            />

            {#if search || type || from || to}
                <button
                    onclick={() => { search = ''; type = ''; from = ''; to = ''; applyFilters(); }}
                    class="text-sm text-slate-400 hover:text-white transition-colors"
                >
                    Limpiar filtros
                </button>
            {/if}
        </div>

        <!-- Tabla -->
        <div class="px-8 py-6">
            {#if movements.data.length === 0}
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <svg class="w-12 h-12 text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
                    </svg>
                    <p class="text-slate-400 font-medium">No hay movimientos registrados</p>
                    {#if !search && !type && !from && !to}
                        <button onclick={() => openModal()} class="mt-3 text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
                            Registrar el primer movimiento →
                        </button>
                    {/if}
                </div>
            {:else}
                <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800">
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Producto</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Cantidad</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Antes → Después</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Bodega</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuario</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Referencia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            {#each movements.data as mov}
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <td class="px-4 py-3 text-slate-400 text-xs whitespace-nowrap">
                                        {formatDate(mov.created_at)}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-white">{mov.product?.name ?? '—'}</div>
                                        {#if mov.product?.sku}
                                            <div class="text-xs text-slate-500 font-mono">{mov.product.sku}</div>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {typeStyle[mov.type] ?? ''}">
                                            {typeMap[mov.type]?.label ?? mov.type}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-semibold {qtyClass(mov.quantity)}">
                                        {mov.quantity > 0 ? '+' : ''}{mov.quantity}
                                    </td>
                                    <td class="px-4 py-3 text-right text-xs text-slate-400 whitespace-nowrap">
                                        <span class="font-mono">{mov.quantity_before}</span>
                                        <span class="mx-1 text-slate-600">→</span>
                                        <span class="font-mono font-semibold text-white">{mov.quantity_after}</span>
                                    </td>
                                    <td class="px-4 py-3 text-xs hidden lg:table-cell">
                                        {#if mov.warehouse?.name}
                                            <span class="text-slate-400">{mov.warehouse.name}</span>
                                        {:else}
                                            <span class="text-slate-600">—</span>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3 text-slate-300 text-sm">
                                        {mov.user?.name ?? '—'}
                                    </td>
                                    <td class="px-4 py-3 text-slate-400 text-xs max-w-36 truncate" title={mov.reference ?? mov.notes ?? ''}>
                                        {mov.reference ?? mov.notes ?? '—'}
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                {#if movements.last_page > 1}
                    <div class="mt-6 flex items-center justify-between text-sm text-slate-400">
                        <span>Página {movements.current_page} de {movements.last_page}</span>
                        <div class="flex gap-2">
                            {#each movements.links as link}
                                {#if link.url}
                                    <a
                                        href={link.url}
                                        class="px-3 py-1.5 rounded-lg transition-colors
                                            {link.active
                                                ? 'bg-indigo-600 text-white font-semibold'
                                                : 'bg-slate-800 hover:bg-slate-700 text-slate-300'}"
                                    >
                                        {@html link.label}
                                    </a>
                                {:else}
                                    <span class="px-3 py-1.5 rounded-lg bg-slate-900 text-slate-600 cursor-default">
                                        {@html link.label}
                                    </span>
                                {/if}
                            {/each}
                        </div>
                    </div>
                {/if}
            {/if}
        </div>
    </div>
</AppLayout>

<!-- Modal registrar movimiento -->
{#if showModal}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick={closeModal}></div>
        <div class="relative bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-md shadow-2xl">

            <h3 class="text-base font-semibold text-white mb-5">Registrar movimiento de stock</h3>

            <form onsubmit={submitMovement} class="space-y-4">

                <!-- Producto -->
                <div>
                    <label for="sm-product" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Producto <span class="text-red-400">*</span>
                    </label>
                    <select
                        id="sm-product"
                        bind:value={selectedProduct}
                        class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white outline-none transition-colors
                            {!selectedProduct && $form.processing ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                    >
                        <option value={null}>— Selecciona un producto —</option>
                        {#each products as p}
                            <option value={p}>{p.name} {p.sku ? `(${p.sku})` : ''} · Stock: {p.stock_quantity}</option>
                        {/each}
                    </select>
                </div>

                <!-- Tipo -->
                <div>
                    <label for="sm-type" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Tipo de movimiento <span class="text-red-400">*</span>
                    </label>
                    <select
                        id="sm-type"
                        bind:value={$form.type}
                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"
                    >
                        {#each types as t}
                            <option value={t.value}>{t.label}</option>
                        {/each}
                    </select>
                    {#if $form.errors.type}
                        <p class="mt-1 text-xs text-red-400">{$form.errors.type}</p>
                    {/if}
                </div>

                <!-- Cantidad -->
                <div>
                    <label for="sm-qty" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Cantidad <span class="text-red-400">*</span>
                        {#if $form.type === 'adjustment'}
                            <span class="text-xs text-amber-400 ml-1">(valor absoluto final)</span>
                        {/if}
                    </label>
                    <input
                        id="sm-qty"
                        type="number"
                        min="1"
                        bind:value={$form.quantity}
                        class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white outline-none transition-colors
                            {$form.errors.quantity ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                    />
                    {#if $form.errors.quantity}
                        <p class="mt-1 text-xs text-red-400">{$form.errors.quantity}</p>
                    {/if}
                    {#if selectedProduct && $form.quantity >= 1}
                        {@const preview = (() => {
                            const qty = parseInt($form.quantity) || 0;
                            const cur = selectedProduct.stock_quantity;
                            if ($form.type === 'in' || $form.type === 'return') return cur + qty;
                            if ($form.type === 'out' || $form.type === 'loss') return Math.max(0, cur - qty);
                            if ($form.type === 'adjustment') return qty;
                            return cur;
                        })()}
                        <p class="mt-1 text-xs text-slate-400">
                            Stock actual: <span class="font-mono text-white">{selectedProduct.stock_quantity}</span>
                            → <span class="font-mono font-semibold text-indigo-300">{preview}</span>
                        </p>
                    {/if}
                </div>

                <!-- Bodega (opcional) -->
                {#if warehouses.length > 0}
                    <div>
                        <label for="sm-wh" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Bodega <span class="text-slate-500 text-xs">(opcional — actualiza stock por bodega)</span>
                        </label>
                        <select
                            id="sm-wh"
                            bind:value={$form.warehouse_id}
                            class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"
                        >
                            <option value={null}>— Sin bodega específica —</option>
                            {#each warehouses as wh}
                                <option value={wh.id}>[{wh.code}] {wh.name}</option>
                            {/each}
                        </select>
                    </div>
                {/if}

                <!-- Costo unitario (opcional) -->
                <div>
                    <label for="sm-cost" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Costo unitario <span class="text-slate-500 text-xs">(opcional)</span>
                    </label>
                    <input
                        id="sm-cost"
                        type="number"
                        min="0"
                        step="0.01"
                        bind:value={$form.unit_cost}
                        placeholder="0.00"
                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"
                    />
                </div>

                <!-- Referencia y notas -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="sm-ref" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Referencia <span class="text-slate-500 text-xs">(opcional)</span>
                        </label>
                        <input
                            id="sm-ref"
                            type="text"
                            bind:value={$form.reference}
                            placeholder="Ej: OC-0042"
                            maxlength="100"
                            class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"
                        />
                    </div>
                    <div>
                        <label for="sm-notes" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Notas <span class="text-slate-500 text-xs">(opcional)</span>
                        </label>
                        <input
                            id="sm-notes"
                            type="text"
                            bind:value={$form.notes}
                            placeholder="Observación breve"
                            class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"
                        />
                    </div>
                </div>

                <div class="flex gap-3 pt-1">
                    <button
                        type="submit"
                        disabled={$form.processing || !selectedProduct}
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors"
                    >
                        {$form.processing ? 'Registrando...' : 'Registrar movimiento'}
                    </button>
                    <button
                        type="button"
                        onclick={closeModal}
                        class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-lg transition-colors"
                    >
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}
