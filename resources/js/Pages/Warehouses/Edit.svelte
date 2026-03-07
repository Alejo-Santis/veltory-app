<script>
    import { useForm } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';

    let { warehouse, types = [] } = $props();

    const form = useForm({
        code:         warehouse.code,
        name:         warehouse.name,
        type:         warehouse.type,
        address:      warehouse.address      ?? '',
        city:         warehouse.city         ?? '',
        phone:        warehouse.phone        ?? '',
        manager_name: warehouse.manager_name ?? '',
        is_active:    warehouse.is_active,
        notes:        warehouse.notes        ?? '',
    });

    function submit(e) {
        e.preventDefault();
        $form.put(`/warehouses/${warehouse.uuid}`);
    }
</script>

<svelte:head><title>Editar {warehouse.name}</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">
        <div class="border-b border-slate-800 px-8 py-5 flex items-center gap-4">
            <a href="/warehouses" class="text-slate-500 hover:text-slate-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-white">Editar bodega</h1>
                <p class="text-sm text-slate-400 mt-0.5">{warehouse.name}</p>
            </div>
        </div>

        <div class="px-8 py-6 max-w-2xl">
            <form onsubmit={submit} class="space-y-5">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="w-code" class="block text-sm font-medium text-slate-300 mb-1.5">Código <span class="text-red-400">*</span></label>
                        <input id="w-code" type="text" bind:value={$form.code} maxlength="20"
                            class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm font-mono text-white outline-none transition-colors
                                {$form.errors.code ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"/>
                        {#if $form.errors.code}<p class="mt-1 text-xs text-red-400">{$form.errors.code}</p>{/if}
                    </div>
                    <div>
                        <label for="w-type" class="block text-sm font-medium text-slate-300 mb-1.5">Tipo <span class="text-red-400">*</span></label>
                        <select id="w-type" bind:value={$form.type}
                            class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors">
                            {#each types as t}<option value={t.value}>{t.label}</option>{/each}
                        </select>
                    </div>
                </div>

                <div>
                    <label for="w-name" class="block text-sm font-medium text-slate-300 mb-1.5">Nombre <span class="text-red-400">*</span></label>
                    <input id="w-name" type="text" bind:value={$form.name}
                        class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white outline-none transition-colors
                            {$form.errors.name ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"/>
                    {#if $form.errors.name}<p class="mt-1 text-xs text-red-400">{$form.errors.name}</p>{/if}
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="w-city" class="block text-sm font-medium text-slate-300 mb-1.5">Ciudad</label>
                        <input id="w-city" type="text" bind:value={$form.city} placeholder="Bogotá"
                            class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"/>
                    </div>
                    <div>
                        <label for="w-phone" class="block text-sm font-medium text-slate-300 mb-1.5">Teléfono</label>
                        <input id="w-phone" type="text" bind:value={$form.phone}
                            class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"/>
                    </div>
                </div>

                <div>
                    <label for="w-manager" class="block text-sm font-medium text-slate-300 mb-1.5">Responsable</label>
                    <input id="w-manager" type="text" bind:value={$form.manager_name}
                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"/>
                </div>

                <div>
                    <label for="w-address" class="block text-sm font-medium text-slate-300 mb-1.5">Dirección</label>
                    <textarea id="w-address" bind:value={$form.address} rows="2"
                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors resize-none"></textarea>
                </div>

                <div>
                    <label for="w-notes" class="block text-sm font-medium text-slate-300 mb-1.5">Notas</label>
                    <textarea id="w-notes" bind:value={$form.notes} rows="2"
                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors resize-none"></textarea>
                </div>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" bind:checked={$form.is_active}
                        class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900"/>
                    <span class="text-sm text-slate-300">Bodega activa</span>
                </label>

                <div class="flex gap-3 pt-2">
                    <button type="submit" disabled={$form.processing}
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold rounded-lg transition-colors">
                        {$form.processing ? 'Guardando...' : 'Guardar cambios'}
                    </button>
                    <a href="/warehouses" class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-lg transition-colors">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</AppLayout>
