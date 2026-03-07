<script>
    import { router } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';

    let { suppliers, filters = {} } = $props();

    let search      = $state(filters.search      ?? '');
    let only_active = $state(filters.only_active ?? false);
    let deleting    = $state(null);

    let searchTimer;
    function onSearchInput() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilters, 350);
    }

    function applyFilters() {
        router.get('/suppliers', {
            search:      search      || undefined,
            only_active: only_active || undefined,
        }, { preserveState: true, replace: true });
    }

    function confirmDelete(supplier) {
        deleting = supplier;
    }

    function cancelDelete() {
        deleting = null;
    }

    function doDelete() {
        if (!deleting) return;
        router.delete(`/suppliers/${deleting.uuid}`, {
            onFinish: () => { deleting = null; },
        });
    }
</script>

<svelte:head><title>Proveedores</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">Proveedores</h1>
                <p class="text-sm text-slate-400 mt-0.5">{suppliers.total} proveedor{suppliers.total !== 1 ? 'es' : ''} registrado{suppliers.total !== 1 ? 's' : ''}</p>
            </div>
            <a
                href="/suppliers/create"
                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo proveedor
            </a>
        </div>

        <!-- Filters -->
        <div class="px-8 py-4 border-b border-slate-800 flex items-center gap-3 flex-wrap">
            <div class="relative flex-1 min-w-[220px] max-w-xs">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input
                    type="text"
                    bind:value={search}
                    oninput={onSearchInput}
                    placeholder="Buscar por nombre, contacto, email..."
                    class="w-full pl-9 pr-3.5 py-2 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"
                />
            </div>

            <label class="flex items-center gap-2 cursor-pointer px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-lg text-sm text-slate-300 select-none">
                <input
                    type="checkbox"
                    bind:checked={only_active}
                    onchange={applyFilters}
                    class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900"
                />
                Solo activos
            </label>
        </div>

        <!-- Content -->
        <div class="px-8 py-6">
            {#if suppliers.data.length === 0}
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <svg class="w-12 h-12 text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                    <p class="text-slate-400 font-medium">No se encontraron proveedores</p>
                    <p class="text-slate-600 text-sm mt-1">Prueba ajustando los filtros o crea uno nuevo</p>
                </div>
            {:else}
                <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800">
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Proveedor</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Contacto</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Teléfono</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Productos</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            {#each suppliers.data as supplier}
                                <tr class="hover:bg-slate-800/50 transition-colors">
                                    <!-- Name + email -->
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-white">{supplier.name}</p>
                                        {#if supplier.email}
                                            <p class="text-xs text-slate-500 mt-0.5">{supplier.email}</p>
                                        {/if}
                                    </td>

                                    <!-- Contact -->
                                    <td class="px-4 py-3">
                                        <span class="text-slate-300">{supplier.contact_name ?? '—'}</span>
                                    </td>

                                    <!-- Phone -->
                                    <td class="px-4 py-3">
                                        {#if supplier.phone}
                                            <a href="tel:{supplier.phone}" class="text-slate-300 hover:text-indigo-400 transition-colors">
                                                {supplier.phone}
                                            </a>
                                        {:else}
                                            <span class="text-slate-600">—</span>
                                        {/if}
                                    </td>

                                    <!-- Products count -->
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-6 rounded-full text-xs font-semibold
                                            {supplier.products_count > 0
                                                ? 'bg-indigo-500/10 text-indigo-400'
                                                : 'bg-slate-700 text-slate-500'}">
                                            {supplier.products_count}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3 text-center">
                                        {#if supplier.is_active}
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Activo</span>
                                        {:else}
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-500/10 text-slate-400 border border-slate-500/20">Inactivo</span>
                                        {/if}
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <a
                                                href="/suppliers/{supplier.uuid}/edit"
                                                class="p-1.5 text-slate-500 hover:text-indigo-400 hover:bg-slate-700 rounded-md transition-colors"
                                                title="Editar"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <button
                                                onclick={() => confirmDelete(supplier)}
                                                class="p-1.5 text-slate-500 hover:text-red-400 hover:bg-slate-700 rounded-md transition-colors"
                                                title="Eliminar"
                                                disabled={supplier.products_count > 0}
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
                {#if suppliers.last_page > 1}
                    <div class="mt-4 flex items-center justify-between text-sm text-slate-400">
                        <span>Mostrando {suppliers.from}–{suppliers.to} de {suppliers.total}</span>
                        <div class="flex items-center gap-1">
                            {#each suppliers.links as link}
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
                    <h3 class="font-semibold text-white">Eliminar proveedor</h3>
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
