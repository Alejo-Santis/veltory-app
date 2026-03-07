<script>
    import { useForm } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';

    let { product, units = [], suppliers = [], categories = [], statuses = [] } = $props();

    const form = useForm({
        name:               product.name,
        sku:                product.sku ?? '',
        barcode:            product.barcode ?? '',
        short_description:  product.short_description ?? '',
        description:        product.description ?? '',
        unit_id:            product.unit_id ?? null,
        supplier_id:        product.supplier_id ?? null,
        cost_price:         product.cost_price ?? '',
        sale_price:         product.sale_price ?? '',
        compare_price:      product.compare_price ?? '',
        tax_rate:           product.tax_rate ?? '0',
        stock_quantity:     product.stock_quantity ?? '0',
        min_stock:          product.min_stock ?? '0',
        max_stock:          product.max_stock ?? '',
        track_stock:        product.track_stock ?? true,
        allow_backorder:    product.allow_backorder ?? false,
        weight:             product.weight ?? '',
        dimensions_length:  product.dimensions_length ?? '',
        dimensions_width:   product.dimensions_width ?? '',
        dimensions_height:  product.dimensions_height ?? '',
        status:             product.status ?? 'active',
        featured:           product.featured ?? false,
        notes:              product.notes ?? '',
        categories:         product.category_ids ?? [],
    });

    function toggleCategory(id) {
        const idx = $form.categories.indexOf(id);
        if (idx === -1) {
            $form.categories = [...$form.categories, id];
        } else {
            $form.categories = $form.categories.filter(c => c !== id);
        }
    }

    function submit(e) {
        e.preventDefault();
        $form.put(`/products/${product.uuid}`);
    }
</script>

<svelte:head><title>Editar producto</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">
        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center gap-4">
            <a href="/products" class="text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-white">Editar producto</h1>
                <p class="text-sm text-slate-400 mt-0.5">{product.name}</p>
            </div>
        </div>

        <div class="p-8">
            <form onsubmit={submit}>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Left column (2/3) -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Información básica -->
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
                            <h2 class="text-sm font-semibold text-white">Información básica</h2>

                            <div>
                                <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">
                                    Nombre <span class="text-red-400">*</span>
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    bind:value={$form.name}
                                    class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors
                                        {$form.errors.name ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                                />
                                {#if $form.errors.name}
                                    <p class="mt-1.5 text-xs text-red-400">{$form.errors.name}</p>
                                {/if}
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="sku" class="block text-sm font-medium text-slate-300 mb-1.5">SKU</label>
                                    <input
                                        id="sku"
                                        type="text"
                                        bind:value={$form.sku}
                                        class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm font-mono text-white placeholder-slate-500 outline-none transition-colors
                                            {$form.errors.sku ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                                    />
                                    {#if $form.errors.sku}
                                        <p class="mt-1.5 text-xs text-red-400">{$form.errors.sku}</p>
                                    {/if}
                                </div>
                                <div>
                                    <label for="barcode" class="block text-sm font-medium text-slate-300 mb-1.5">Código de barras</label>
                                    <input
                                        id="barcode"
                                        type="text"
                                        bind:value={$form.barcode}
                                        class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm font-mono text-white placeholder-slate-500 outline-none transition-colors
                                            {$form.errors.barcode ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                                    />
                                    {#if $form.errors.barcode}
                                        <p class="mt-1.5 text-xs text-red-400">{$form.errors.barcode}</p>
                                    {/if}
                                </div>
                            </div>

                            <div>
                                <label for="short_description" class="block text-sm font-medium text-slate-300 mb-1.5">Descripción corta</label>
                                <input
                                    id="short_description"
                                    type="text"
                                    bind:value={$form.short_description}
                                    class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"
                                />
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-slate-300 mb-1.5">Descripción completa</label>
                                <textarea
                                    id="description"
                                    bind:value={$form.description}
                                    rows="4"
                                    class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors resize-none"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Precios -->
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
                            <h2 class="text-sm font-semibold text-white">Precios</h2>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="cost_price" class="block text-sm font-medium text-slate-300 mb-1.5">Costo</label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm">$</span>
                                        <input id="cost_price" type="number" bind:value={$form.cost_price} min="0" step="0.01"
                                            class="w-full pl-7 pr-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"/>
                                    </div>
                                </div>
                                <div>
                                    <label for="sale_price" class="block text-sm font-medium text-slate-300 mb-1.5">Precio de venta</label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm">$</span>
                                        <input id="sale_price" type="number" bind:value={$form.sale_price} min="0" step="0.01"
                                            class="w-full pl-7 pr-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"/>
                                    </div>
                                </div>
                                <div>
                                    <label for="compare_price" class="block text-sm font-medium text-slate-300 mb-1.5">Precio comparativo</label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm">$</span>
                                        <input id="compare_price" type="number" bind:value={$form.compare_price} min="0" step="0.01"
                                            class="w-full pl-7 pr-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"/>
                                    </div>
                                </div>
                                <div>
                                    <label for="tax_rate" class="block text-sm font-medium text-slate-300 mb-1.5">IVA (%)</label>
                                    <div class="relative">
                                        <input id="tax_rate" type="number" bind:value={$form.tax_rate} min="0" max="100" step="0.01"
                                            class="w-full pr-8 pl-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"/>
                                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inventario -->
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
                            <h2 class="text-sm font-semibold text-white">Inventario</h2>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label for="stock_quantity" class="block text-sm font-medium text-slate-300 mb-1.5">Stock actual</label>
                                    <input id="stock_quantity" type="number" bind:value={$form.stock_quantity} min="0"
                                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"/>
                                </div>
                                <div>
                                    <label for="min_stock" class="block text-sm font-medium text-slate-300 mb-1.5">Stock mínimo</label>
                                    <input id="min_stock" type="number" bind:value={$form.min_stock} min="0"
                                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"/>
                                </div>
                                <div>
                                    <label for="max_stock" class="block text-sm font-medium text-slate-300 mb-1.5">Stock máximo</label>
                                    <input id="max_stock" type="number" bind:value={$form.max_stock} min="0" placeholder="Sin límite"
                                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"/>
                                </div>
                            </div>

                            <div class="flex gap-6">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="checkbox" bind:checked={$form.track_stock}
                                        class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900"/>
                                    <span class="text-sm text-slate-300">Rastrear inventario</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="checkbox" bind:checked={$form.allow_backorder}
                                        class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900"/>
                                    <span class="text-sm text-slate-300">Permitir pedidos sin stock</span>
                                </label>
                            </div>
                        </div>

                        <!-- Datos físicos -->
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
                            <h2 class="text-sm font-semibold text-white">Datos físicos <span class="text-slate-500 font-normal">(opcional)</span></h2>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="weight" class="block text-sm font-medium text-slate-300 mb-1.5">Peso (kg)</label>
                                    <input id="weight" type="number" bind:value={$form.weight} min="0" step="0.001" placeholder="0.000"
                                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"/>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1.5">Dimensiones (cm) — largo × ancho × alto</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <input type="number" bind:value={$form.dimensions_length} min="0" step="0.01" placeholder="Largo"
                                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"/>
                                    <input type="number" bind:value={$form.dimensions_width} min="0" step="0.01" placeholder="Ancho"
                                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"/>
                                    <input type="number" bind:value={$form.dimensions_height} min="0" step="0.01" placeholder="Alto"
                                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"/>
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                            <label for="notes" class="block text-sm font-semibold text-white mb-3">Notas internas</label>
                            <textarea id="notes" bind:value={$form.notes} rows="3"
                                class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors resize-none"
                            ></textarea>
                        </div>

                    </div>

                    <!-- Right column (1/3) -->
                    <div class="space-y-6">

                        <!-- Estado -->
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-4">
                            <h2 class="text-sm font-semibold text-white">Estado</h2>

                            <div>
                                <label for="status" class="block text-sm font-medium text-slate-300 mb-1.5">Estado del producto</label>
                                <select id="status" bind:value={$form.status}
                                    class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors">
                                    {#each statuses as s}
                                        <option value={s.value}>{s.label}</option>
                                    {/each}
                                </select>
                            </div>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" bind:checked={$form.featured}
                                    class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900"/>
                                <div>
                                    <span class="text-sm font-medium text-slate-300">Producto destacado</span>
                                    <p class="text-xs text-slate-500">Aparece marcado con estrella</p>
                                </div>
                            </label>
                        </div>

                        <!-- Organización -->
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-4">
                            <h2 class="text-sm font-semibold text-white">Organización</h2>

                            <div>
                                <label for="unit_id" class="block text-sm font-medium text-slate-300 mb-1.5">Unidad de medida</label>
                                <select id="unit_id" bind:value={$form.unit_id}
                                    class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors">
                                    <option value={null}>— Sin unidad —</option>
                                    {#each units as unit}
                                        <option value={unit.id}>{unit.name} ({unit.abbreviation})</option>
                                    {/each}
                                </select>
                            </div>

                            <div>
                                <label for="supplier_id" class="block text-sm font-medium text-slate-300 mb-1.5">Proveedor</label>
                                <select id="supplier_id" bind:value={$form.supplier_id}
                                    class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors">
                                    <option value={null}>— Sin proveedor —</option>
                                    {#each suppliers as supplier}
                                        <option value={supplier.id}>{supplier.name}</option>
                                    {/each}
                                </select>
                            </div>
                        </div>

                        <!-- Categorías -->
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-3">
                            <h2 class="text-sm font-semibold text-white">Categorías</h2>

                            {#if categories.length === 0}
                                <p class="text-xs text-slate-500">No hay categorías activas.</p>
                            {:else}
                                <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                                    {#each categories as parent}
                                        <div>
                                            <label class="flex items-center gap-2.5 cursor-pointer group">
                                                <input
                                                    type="checkbox"
                                                    checked={$form.categories.includes(parent.id)}
                                                    onchange={() => toggleCategory(parent.id)}
                                                    class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900"
                                                />
                                                <span class="text-sm font-medium text-slate-300 group-hover:text-white transition-colors">{parent.name}</span>
                                            </label>

                                            {#if parent.children && parent.children.length > 0}
                                                <div class="ml-6 mt-1.5 space-y-1.5">
                                                    {#each parent.children as child}
                                                        <label class="flex items-center gap-2.5 cursor-pointer group">
                                                            <input
                                                                type="checkbox"
                                                                checked={$form.categories.includes(child.id)}
                                                                onchange={() => toggleCategory(child.id)}
                                                                class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900"
                                                            />
                                                            <span class="text-sm text-slate-400 group-hover:text-slate-200 transition-colors">{child.name}</span>
                                                        </label>
                                                    {/each}
                                                </div>
                                            {/if}
                                        </div>
                                    {/each}
                                </div>
                            {/if}
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-2">
                            <button
                                type="submit"
                                disabled={$form.processing}
                                class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold rounded-lg transition-colors"
                            >
                                {$form.processing ? 'Guardando...' : 'Guardar cambios'}
                            </button>
                            <a href="/products" class="w-full py-2.5 text-center text-sm text-slate-400 hover:text-white transition-colors">
                                Cancelar
                            </a>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</AppLayout>
