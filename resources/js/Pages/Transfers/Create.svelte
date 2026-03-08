<script>
    import { useForm } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';
    import Breadcrumb from '@/Components/Breadcrumb.svelte';

    let { warehouses = [], products = [] } = $props();

    const form = useForm({
        from_warehouse_id: '',
        to_warehouse_id:   '',
        notes:             '',
        items: [{ product_id: '', quantity: 1, notes: '' }],
    });

    function addItem() {
        $form.items = [...$form.items, { product_id: '', quantity: 1, notes: '' }];
    }

    function removeItem(index) {
        $form.items = $form.items.filter((_, i) => i !== index);
    }

    function submit(e) {
        e.preventDefault();
        $form.post('/transfers');
    }

    const productMap = Object.fromEntries(products.map(p => [p.id, p]));
</script>

<svelte:head><title>Nuevo traslado</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">
        <div class="border-b border-slate-800 px-8 py-5 flex items-center gap-4">
            <a href="/transfers" class="text-slate-500 hover:text-slate-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-white">Nuevo traslado</h1>
                <p class="text-sm text-slate-400 mt-0.5">Registra un traslado entre bodegas</p>
            </div>
        </div>

        <div class="px-8 py-6 max-w-3xl">
            <div class="mb-6">
                <Breadcrumb items={[{ label: 'Traslados', href: '/transfers' }, { label: 'Nuevo traslado' }]} />
            </div>
            <form onsubmit={submit} class="space-y-6">

                <!-- Bodegas -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Origen y destino</h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="t-from" class="block text-sm font-medium text-slate-300 mb-1.5">
                                Bodega origen <span class="text-red-400">*</span>
                            </label>
                            <select id="t-from" bind:value={$form.from_warehouse_id}
                                class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white outline-none transition-colors
                                    {$form.errors.from_warehouse_id ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}">
                                <option value="">— Seleccionar —</option>
                                {#each warehouses as w}<option value={w.id}>{w.name} ({w.code})</option>{/each}
                            </select>
                            {#if $form.errors.from_warehouse_id}<p class="mt-1 text-xs text-red-400">{$form.errors.from_warehouse_id}</p>{/if}
                        </div>

                        <div>
                            <label for="t-to" class="block text-sm font-medium text-slate-300 mb-1.5">
                                Bodega destino <span class="text-red-400">*</span>
                            </label>
                            <select id="t-to" bind:value={$form.to_warehouse_id}
                                class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white outline-none transition-colors
                                    {$form.errors.to_warehouse_id ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}">
                                <option value="">— Seleccionar —</option>
                                {#each warehouses as w}
                                    <option value={w.id} disabled={w.id == $form.from_warehouse_id}>{w.name} ({w.code})</option>
                                {/each}
                            </select>
                            {#if $form.errors.to_warehouse_id}<p class="mt-1 text-xs text-red-400">{$form.errors.to_warehouse_id}</p>{/if}
                        </div>
                    </div>

                    <div>
                        <label for="t-notes" class="block text-sm font-medium text-slate-300 mb-1.5">Notas del traslado</label>
                        <textarea id="t-notes" bind:value={$form.notes} rows="2" placeholder="Motivo del traslado, instrucciones especiales…"
                            class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors resize-none"></textarea>
                    </div>
                </div>

                <!-- Ítems -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Productos a trasladar</h2>
                        <button type="button" onclick={addItem}
                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 hover:bg-indigo-500/20 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Agregar producto
                        </button>
                    </div>

                    {#if $form.errors.items}
                        <p class="text-xs text-red-400">{$form.errors.items}</p>
                    {/if}

                    <div class="space-y-3">
                        {#each $form.items as item, i}
                            <div class="flex items-start gap-3 p-3 bg-slate-800/50 rounded-lg border border-slate-700">
                                <!-- Producto -->
                                <div class="flex-1">
                                    <select
                                        bind:value={item.product_id}
                                        class="w-full px-3 py-2 bg-slate-800 border rounded-lg text-sm text-white outline-none transition-colors
                                            {$form.errors[`items.${i}.product_id`] ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                                    >
                                        <option value="">— Selecciona producto —</option>
                                        {#each products as p}
                                            <option value={p.id}>{p.name} {p.sku ? `(${p.sku})` : ''} · Stock: {p.stock_quantity}</option>
                                        {/each}
                                    </select>
                                    {#if $form.errors[`items.${i}.product_id`]}
                                        <p class="mt-1 text-xs text-red-400">{$form.errors[`items.${i}.product_id`]}</p>
                                    {/if}
                                </div>

                                <!-- Cantidad -->
                                <div class="w-28">
                                    <input
                                        type="number"
                                        min="1"
                                        bind:value={item.quantity}
                                        placeholder="Cant."
                                        class="w-full px-3 py-2 bg-slate-800 border rounded-lg text-sm text-white text-center outline-none transition-colors
                                            {$form.errors[`items.${i}.quantity`] ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                                    />
                                </div>

                                <!-- Notas ítem -->
                                <div class="flex-1">
                                    <input type="text" bind:value={item.notes} placeholder="Notas (opcional)"
                                        class="w-full px-3 py-2 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"/>
                                </div>

                                <!-- Eliminar -->
                                {#if $form.items.length > 1}
                                    <button type="button" onclick={() => removeItem(i)}
                                        class="mt-0.5 p-2 text-slate-600 hover:text-red-400 hover:bg-slate-700 rounded-lg transition-colors flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                {/if}
                            </div>
                        {/each}
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" disabled={$form.processing}
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold rounded-lg transition-colors">
                        {$form.processing ? 'Creando...' : 'Crear traslado'}
                    </button>
                    <a href="/transfers" class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-lg transition-colors">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</AppLayout>
