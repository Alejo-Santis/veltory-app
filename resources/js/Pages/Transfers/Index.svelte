<script>
    import { router } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';

    let { transfers, filters = {}, statuses = [], warehouses = [] } = $props();

    let search    = $state(filters.search    ?? '');
    let status    = $state(filters.status    ?? '');
    let warehouse = $state(filters.warehouse ?? '');

    let searchTimer;
    function onSearchInput() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilters, 350);
    }

    function applyFilters() {
        router.get('/transfers', {
            search:    search    || undefined,
            status:    status    || undefined,
            warehouse: warehouse || undefined,
        }, { preserveState: true, replace: true });
    }

    const statusMap   = Object.fromEntries(statuses.map(s => [s.value, s]));

    const statusStyle = {
        draft:      'bg-slate-500/10 text-slate-400 border border-slate-500/20',
        requested:  'bg-amber-500/10 text-amber-400 border border-amber-500/20',
        approved:   'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
        in_transit: 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
        completed:  'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
        cancelled:  'bg-red-500/10 text-red-400 border border-red-500/20',
    };

    function formatDate(d) {
        return new Date(d).toLocaleDateString('es-CO', { year: 'numeric', month: 'short', day: 'numeric' });
    }
</script>

<svelte:head><title>Traslados</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">Traslados</h1>
                <p class="text-sm text-slate-400 mt-0.5">{transfers.total} traslado{transfers.total !== 1 ? 's' : ''} registrado{transfers.total !== 1 ? 's' : ''}</p>
            </div>
            <a
                href="/transfers/create"
                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo traslado
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
                    placeholder="Buscar por referencia o bodega…"
                    class="w-full pl-9 pr-4 py-2 bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"
                />
            </div>
            <select bind:value={status} onchange={applyFilters}
                class="px-3 py-2 bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors">
                <option value="">Todos los estados</option>
                {#each statuses as s}<option value={s.value}>{s.label}</option>{/each}
            </select>
            <select bind:value={warehouse} onchange={applyFilters}
                class="px-3 py-2 bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors">
                <option value="">Todas las bodegas</option>
                {#each warehouses as w}<option value={w.id}>{w.name} ({w.code})</option>{/each}
            </select>
        </div>

        <!-- Tabla -->
        <div class="px-8 py-6">
            {#if transfers.data.length === 0}
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <svg class="w-12 h-12 text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <p class="text-slate-400 font-medium">No hay traslados registrados</p>
                    <a href="/transfers/create" class="mt-3 text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
                        Crear el primer traslado →
                    </a>
                </div>
            {:else}
                <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800">
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Referencia</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Origen → Destino</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Ítems</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Solicitado por</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            {#each transfers.data as tr}
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs bg-slate-800 text-slate-300 px-2 py-0.5 rounded">
                                            {tr.reference ?? '—'}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="text-slate-300">{tr.from_warehouse?.name ?? '—'}</span>
                                            <svg class="w-3 h-3 text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            <span class="text-white font-medium">{tr.to_warehouse?.name ?? '—'}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {statusStyle[tr.status] ?? ''}">
                                            {statusMap[tr.status]?.label ?? tr.status}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-7 h-6 rounded-full text-xs font-semibold bg-slate-800 text-slate-400">
                                            {tr.items_count}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-400">{tr.requested_by?.name ?? '—'}</td>
                                    <td class="px-4 py-3 text-slate-500 text-xs">{formatDate(tr.created_at)}</td>
                                    <td class="px-4 py-3">
                                        <a
                                            href="/transfers/{tr.uuid}"
                                            class="p-1.5 text-slate-500 hover:text-indigo-400 hover:bg-slate-700 rounded-md transition-colors inline-flex"
                                            title="Ver detalle"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                {#if transfers.last_page > 1}
                    <div class="mt-6 flex items-center justify-between text-sm text-slate-400">
                        <span>Mostrando {transfers.from}–{transfers.to} de {transfers.total}</span>
                        <div class="flex gap-2">
                            {#each transfers.links as link}
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
