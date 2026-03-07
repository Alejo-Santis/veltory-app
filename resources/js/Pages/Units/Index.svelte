<script>
    import { useForm, router } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';

    let { units = [], types = [] } = $props();

    // Modal state
    let modal     = $state(null);  // null | 'create' | 'edit'
    let editing   = $state(null);  // unit being edited
    let deleting  = $state(null);  // unit being deleted

    const form = useForm({
        name:         '',
        abbreviation: '',
        type:         'unit',
    });

    function openCreate() {
        $form.reset();
        $form.clearErrors();
        modal = 'create';
    }

    function openEdit(unit) {
        $form.name         = unit.name;
        $form.abbreviation = unit.abbreviation;
        $form.type         = unit.type;
        $form.clearErrors();
        editing = unit;
        modal   = 'edit';
    }

    function closeModal() {
        modal   = null;
        editing = null;
        $form.reset();
        $form.clearErrors();
    }

    function submitCreate(e) {
        e.preventDefault();
        $form.post('/units', { onSuccess: closeModal });
    }

    function submitEdit(e) {
        e.preventDefault();
        $form.put(`/units/${editing.uuid}`, { onSuccess: closeModal });
    }

    function confirmDelete(unit) {
        deleting = unit;
    }

    function cancelDelete() {
        deleting = null;
    }

    function doDelete() {
        if (!deleting) return;
        router.delete(`/units/${deleting.uuid}`, {
            onFinish: () => { deleting = null; },
        });
    }

    const typeLabels = Object.fromEntries(types.map(t => [t.value, t.label]));
</script>

<svelte:head><title>Unidades de medida</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">Unidades de medida</h1>
                <p class="text-sm text-slate-400 mt-0.5">{units.length} unidad{units.length !== 1 ? 'es' : ''} registrada{units.length !== 1 ? 's' : ''}</p>
            </div>
            <button
                onclick={openCreate}
                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva unidad
            </button>
        </div>

        <!-- Table -->
        <div class="px-8 py-6">
            {#if units.length === 0}
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <svg class="w-12 h-12 text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                    </svg>
                    <p class="text-slate-400 font-medium">No hay unidades registradas</p>
                    <button onclick={openCreate} class="mt-3 text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
                        Crear la primera unidad →
                    </button>
                </div>
            {:else}
                <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden max-w-2xl">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800">
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nombre</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Abreviatura</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Productos</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            {#each units as unit}
                                <tr class="hover:bg-slate-800/50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-white">{unit.name}</td>
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-sm text-slate-300 bg-slate-800 px-2 py-0.5 rounded">{unit.abbreviation}</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-400">{typeLabels[unit.type] ?? unit.type}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-6 rounded-full text-xs font-semibold
                                            {unit.products_count > 0
                                                ? 'bg-indigo-500/10 text-indigo-400'
                                                : 'bg-slate-700 text-slate-500'}">
                                            {unit.products_count}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button
                                                onclick={() => openEdit(unit)}
                                                class="p-1.5 text-slate-500 hover:text-indigo-400 hover:bg-slate-700 rounded-md transition-colors"
                                                title="Editar"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <button
                                                onclick={() => confirmDelete(unit)}
                                                disabled={unit.products_count > 0}
                                                class="p-1.5 rounded-md transition-colors
                                                    {unit.products_count > 0
                                                        ? 'text-slate-700 cursor-not-allowed'
                                                        : 'text-slate-500 hover:text-red-400 hover:bg-slate-700'}"
                                                title={unit.products_count > 0 ? 'Tiene productos asociados' : 'Eliminar'}
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
            {/if}
        </div>
    </div>
</AppLayout>

<!-- Modal crear / editar -->
{#if modal}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick={closeModal}></div>
        <div class="relative bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-sm shadow-2xl">

            <h3 class="text-base font-semibold text-white mb-5">
                {modal === 'create' ? 'Nueva unidad de medida' : 'Editar unidad'}
            </h3>

            <form onsubmit={modal === 'create' ? submitCreate : submitEdit} class="space-y-4">

                <div>
                    <label for="u-name" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Nombre <span class="text-red-400">*</span>
                    </label>
                    <input
                        id="u-name"
                        type="text"
                        bind:value={$form.name}
                        placeholder="Ej: Kilogramo"
                        class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors
                            {$form.errors.name ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                    />
                    {#if $form.errors.name}
                        <p class="mt-1 text-xs text-red-400">{$form.errors.name}</p>
                    {/if}
                </div>

                <div>
                    <label for="u-abbr" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Abreviatura <span class="text-red-400">*</span>
                    </label>
                    <input
                        id="u-abbr"
                        type="text"
                        bind:value={$form.abbreviation}
                        placeholder="Ej: kg"
                        maxlength="10"
                        class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm font-mono text-white placeholder-slate-500 outline-none transition-colors
                            {$form.errors.abbreviation ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                    />
                    {#if $form.errors.abbreviation}
                        <p class="mt-1 text-xs text-red-400">{$form.errors.abbreviation}</p>
                    {/if}
                </div>

                <div>
                    <label for="u-type" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Tipo <span class="text-red-400">*</span>
                    </label>
                    <select
                        id="u-type"
                        bind:value={$form.type}
                        class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"
                    >
                        {#each types as t}
                            <option value={t.value}>{t.label}</option>
                        {/each}
                    </select>
                </div>

                <div class="flex gap-3 pt-1">
                    <button
                        type="submit"
                        disabled={$form.processing}
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold rounded-lg transition-colors"
                    >
                        {$form.processing ? 'Guardando...' : (modal === 'create' ? 'Crear' : 'Guardar cambios')}
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

<!-- Modal eliminar -->
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
                    <h3 class="font-semibold text-white">Eliminar unidad</h3>
                    <p class="text-sm text-slate-400 mt-0.5">Esta acción no se puede deshacer</p>
                </div>
            </div>
            <p class="text-sm text-slate-300 mb-6">
                ¿Estás seguro de eliminar <span class="font-semibold text-white">"{deleting.name}"</span>?
            </p>
            <div class="flex gap-3">
                <button onclick={doDelete} class="flex-1 py-2.5 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-lg transition-colors">
                    Eliminar
                </button>
                <button onclick={cancelDelete} class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-lg transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
{/if}
