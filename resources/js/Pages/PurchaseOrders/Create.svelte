<script>
    import { router } from '@inertiajs/svelte';
    import AppLayout from '@/Layouts/AppLayout.svelte';
    import Breadcrumb from '@/Components/Breadcrumb.svelte';

    let { suppliers, warehouses, products } = $props();

    let form = $state({
        supplier_id:  '',
        warehouse_id: '',
        expected_at:  '',
        notes:        '',
        items: [],
    });

    let errors   = $state({});
    let submitting = $state(false);

    // Totales
    const total = $derived(
        form.items.reduce((sum, item) => {
            const qty  = Number(item.quantity_ordered) || 0;
            const cost = Number(item.unit_cost) || 0;
            return sum + qty * cost;
        }, 0)
    );

    function formatCurrency(v) {
        return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(v);
    }

    function addItem() {
        form.items = [...form.items, { product_id: '', quantity_ordered: 1, unit_cost: '', notes: '' }];
    }

    function removeItem(i) {
        form.items = form.items.filter((_, idx) => idx !== i);
    }

    function onProductChange(i, productId) {
        const product = products.find(p => String(p.id) === String(productId));
        if (product) {
            form.items[i].unit_cost = product.cost_price ?? '';
        }
    }

    function submit() {
        submitting = true;
        router.post('/purchase-orders', form, {
            onError:  (e) => { errors = e; submitting = false; },
            onSuccess: () => { submitting = false; },
        });
    }
</script>

<svelte:head><title>Nueva orden de compra</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">Nueva orden de compra</h1>
                <p class="text-sm text-slate-400 mt-0.5">Crear solicitud de compra a proveedor</p>
            </div>
            <a href="/purchase-orders" class="px-4 py-2 text-sm text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-700 rounded-lg transition-colors">
                Cancelar
            </a>
        </div>

        <form onsubmit={(e) => { e.preventDefault(); submit(); }} class="px-8 py-6 space-y-6 max-w-4xl">
            <Breadcrumb items={[{ label: 'Órdenes de compra', href: '/purchase-orders' }, { label: 'Nueva orden' }]} />

            <!-- Datos generales -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
                <h2 class="text-sm font-semibold text-white">Datos generales</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Proveedor -->
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Proveedor <span class="text-red-400">*</span></label>
                        <select
                            bind:value={form.supplier_id}
                            class="w-full px-3 py-2.5 bg-slate-800 border {errors.supplier_id ? 'border-red-500' : 'border-slate-700'} rounded-lg text-sm text-slate-200 focus:outline-none focus:border-indigo-500"
                        >
                            <option value="">Selecciona un proveedor</option>
                            {#each suppliers as s}
                                <option value={s.id}>{s.name}</option>
                            {/each}
                        </select>
                        {#if errors.supplier_id}<p class="text-xs text-red-400 mt-1">{errors.supplier_id}</p>{/if}
                    </div>

                    <!-- Bodega destino -->
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Bodega de recepción <span class="text-red-400">*</span></label>
                        <select
                            bind:value={form.warehouse_id}
                            class="w-full px-3 py-2.5 bg-slate-800 border {errors.warehouse_id ? 'border-red-500' : 'border-slate-700'} rounded-lg text-sm text-slate-200 focus:outline-none focus:border-indigo-500"
                        >
                            <option value="">Selecciona una bodega</option>
                            {#each warehouses as w}
                                <option value={w.id}>[{w.code}] {w.name}</option>
                            {/each}
                        </select>
                        {#if errors.warehouse_id}<p class="text-xs text-red-400 mt-1">{errors.warehouse_id}</p>{/if}
                    </div>

                    <!-- Fecha esperada -->
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Fecha de entrega esperada</label>
                        <input
                            type="date"
                            bind:value={form.expected_at}
                            class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-sm text-slate-200 focus:outline-none focus:border-indigo-500 [color-scheme:dark]"
                        />
                    </div>

                    <!-- Notas -->
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Notas</label>
                        <input
                            type="text"
                            placeholder="Observaciones opcionales"
                            bind:value={form.notes}
                            class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500"
                        />
                    </div>
                </div>
            </div>

            <!-- Productos -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800">
                    <h2 class="text-sm font-semibold text-white">Productos</h2>
                    <button
                        type="button"
                        onclick={addItem}
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-400 hover:text-white hover:bg-indigo-600 border border-indigo-500/40 hover:border-indigo-600 rounded-lg transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Agregar producto
                    </button>
                </div>

                {#if errors.items}
                    <p class="px-6 py-3 text-sm text-red-400 bg-red-500/5 border-b border-red-500/20">{errors.items}</p>
                {/if}

                {#if form.items.length === 0}
                    <div class="py-12 text-center text-slate-500 text-sm">
                        <svg class="w-8 h-8 mx-auto mb-2 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Haz clic en "Agregar producto" para iniciar la orden
                    </div>
                {:else}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-800">
                                    <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Producto</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider w-28">Cantidad</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider w-36">Costo unitario</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider w-32">Subtotal</th>
                                    <th class="px-4 py-2.5 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                {#each form.items as item, i}
                                    <tr>
                                        <td class="px-4 py-2.5">
                                            <select
                                                bind:value={item.product_id}
                                                onchange={(e) => onProductChange(i, e.target.value)}
                                                class="w-full px-2.5 py-2 bg-slate-800 border {errors[`items.${i}.product_id`] ? 'border-red-500' : 'border-slate-700'} rounded-lg text-sm text-slate-200 focus:outline-none focus:border-indigo-500"
                                            >
                                                <option value="">— Producto —</option>
                                                {#each products as p}
                                                    <option value={p.id}>{p.name}{p.sku ? ` (${p.sku})` : ''}</option>
                                                {/each}
                                            </select>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input
                                                type="number"
                                                min="1"
                                                bind:value={item.quantity_ordered}
                                                class="w-full px-2.5 py-2 bg-slate-800 border border-slate-700 rounded-lg text-sm text-slate-200 text-right focus:outline-none focus:border-indigo-500"
                                            />
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                placeholder="0"
                                                bind:value={item.unit_cost}
                                                class="w-full px-2.5 py-2 bg-slate-800 border border-slate-700 rounded-lg text-sm text-slate-200 text-right focus:outline-none focus:border-indigo-500"
                                            />
                                        </td>
                                        <td class="px-4 py-2.5 text-right tabular-nums text-slate-300 font-medium">
                                            {formatCurrency((Number(item.quantity_ordered) || 0) * (Number(item.unit_cost) || 0))}
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            <button type="button" onclick={() => removeItem(i)}
                                                class="p-1.5 text-slate-600 hover:text-red-400 hover:bg-red-500/10 rounded-md transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>

                    <!-- Total -->
                    <div class="flex justify-end px-6 py-4 border-t border-slate-800 gap-6">
                        <span class="text-sm text-slate-400">Total estimado</span>
                        <span class="text-base font-bold text-white tabular-nums">{formatCurrency(total)}</span>
                    </div>
                {/if}
            </div>

            <!-- Acciones -->
            <div class="flex items-center justify-end gap-3 pb-6">
                <a href="/purchase-orders" class="px-5 py-2.5 text-sm text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-700 rounded-lg transition-colors">
                    Cancelar
                </a>
                <button
                    type="submit"
                    disabled={submitting || form.items.length === 0}
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors"
                >
                    {submitting ? 'Guardando…' : 'Crear orden'}
                </button>
            </div>
        </form>
    </div>
</AppLayout>
