<script>
    import { router } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';

    let { warehouses, filters = {}, types = [] } = $props();

    let search   = $state(filters.search ?? '');
    let type     = $state(filters.type   ?? '');
    let deleting = $state(null);

    let searchTimer;
    function onSearchInput() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilters, 350);
    }

    function applyFilters() {
        router.get('/warehouses', {
            search: search || undefined,
            type:   type   || undefined,
        }, { preserveState: true, replace: true });
    }

    function confirmDelete(warehouse) { deleting = warehouse; }
    function cancelDelete()           { deleting = null; }

    function doDelete() {
        if (!deleting) return;
        router.delete(`/warehouses/${deleting.uuid}`, {
            onFinish: () => { deleting = null; },
        });
    }

    const typeLabels = Object.fromEntries(types.map(t => [t.value, t.label]));

    const typeStyle = {
        warehouse: 'bg-slate-500/10 text-slate-400 border border-slate-500/20',
        branch:    'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
        store:     'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    };
</script>

<svelte:head><title>Bodegas</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">Bodegas y sucursales</h1>
                <p class="text-sm text-slate-400 mt-0.5">{warehouses.total} ubicación{warehouses.total !== 1 ? 'es' : ''} registrada{warehouses.total !== 1 ? 's' : ''}</p>
            </div>
            <a
                href="/warehouses/create"
                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva bodega
            </a>
        </div>

        <!-- Filtros -->
        <div class="px-8 py-4 border-b border-slate-800 flex items-center gap-3 flex-wrap">
            <div class="relative flex-1 min-w-52">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    bind:value={search}
                    oninput={onSearchInput}
                    placeholder="Buscar por nombre, código, ciudad…"
                    class="w-full pl-9 pr-4 py-2 bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"
                />
            </div>
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
        </div>

        <!-- Tabla -->
        <div class="px-8 py-6">
            {#if warehouses.data.length === 0}
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <svg class="w-12 h-12 text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <p class="text-slate-400 font-medium">No hay bodegas registradas</p>
                    <a href="/warehouses/create" class="mt-3 text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
                        Crear la primera bodega →
                    </a>
                </div>
            {:else}
                <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800">
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Código</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nombre</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Ciudad</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Responsable</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            {#each warehouses.data as wh}
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs bg-slate-800 text-slate-300 px-2 py-0.5 rounded">{wh.code}</span>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-white">{wh.name}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {typeStyle[wh.type] ?? ''}">
                                            {typeLabels[wh.type] ?? wh.type}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-400">{wh.city ?? '—'}</td>
                                    <td class="px-4 py-3 text-slate-400">{wh.manager_name ?? '—'}</td>
                                    <td class="px-4 py-3 text-center">
                                        {#if wh.is_active}
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Activa</span>
                                        {:else}
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-500/10 text-slate-400 border border-slate-500/20">Inactiva</span>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <a
                                                href="/warehouses/{wh.uuid}/edit"
                                                class="p-1.5 text-slate-500 hover:text-indigo-400 hover:bg-slate-700 rounded-md transition-colors"
                                                title="Editar"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <button
                                                onclick={() => confirmDelete(wh)}
                                                class="p-1.5 text-slate-500 hover:text-red-400 hover:bg-slate-700 rounded-md transition-colors"
                                                title="Eliminar"
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

                {#if warehouses.last_page > 1}
                    <div class="mt-6 flex items-center justify-between text-sm text-slate-400">
                        <span>Mostrando {warehouses.from}–{warehouses.to} de {warehouses.total}</span>
                        <div class="flex gap-2">
                            {#each warehouses.links as link}
                                {#if link.url}
                                    <a href={link.url} class="px-3 py-1.5 rounded-lg transition-colors {link.active ? 'bg-indigo-600 text-white font-semibold' : 'bg-slate-800 hover:bg-slate-700 text-slate-300'}">
                                        {@html link.label}
                                    </a>
                                {:else}
                                    <span class="px-3 py-1.5 text-slate-600 cursor-default">{@html link.label}</span>
                                {/if}
                            {/each}
                        </div>
                    </div>
                {/if}
            {/if}
        </div>
    </div>
</AppLayout>

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
                    <h3 class="font-semibold text-white">Eliminar bodega</h3>
                    <p class="text-sm text-slate-400 mt-0.5">Esta acción no se puede deshacer</p>
                </div>
            </div>
            <p class="text-sm text-slate-300 mb-6">
                ¿Estás seguro de eliminar <span class="font-semibold text-white">"{deleting.name}"</span>?
            </p>
            <div class="flex gap-3">
                <button onclick={doDelete} class="flex-1 py-2.5 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-lg transition-colors">Eliminar</button>
                <button onclick={cancelDelete} class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-lg transition-colors">Cancelar</button>
            </div>
        </div>
    </div>
{/if}
