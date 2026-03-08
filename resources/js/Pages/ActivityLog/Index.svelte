<script>
    import AppLayout from '@/Layouts/AppLayout.svelte';
    import { router, page } from '@inertiajs/svelte';

    let { logs, filters, events } = $props();

    let search = $state(filters.search ?? '');
    let event  = $state(filters.event  ?? '');
    let from   = $state(filters.from   ?? '');
    let to     = $state(filters.to     ?? '');

    let expandedId = $state(null);

    function applyFilters() {
        router.get('/activity-log', { search, event, from, to }, { preserveState: true, replace: true });
    }

    function clearFilters() {
        search = ''; event = ''; from = ''; to = '';
        router.get('/activity-log', {}, { replace: true });
    }

    function toggleExpand(id) {
        expandedId = expandedId === id ? null : id;
    }

    const eventColors = {
        created: 'bg-emerald-500/15 text-emerald-400',
        updated: 'bg-amber-500/15 text-amber-400',
        deleted: 'bg-red-500/15 text-red-400',
    };

    const eventLabels = {
        created: 'Creado',
        updated: 'Actualizado',
        deleted: 'Eliminado',
    };

    const modelLabels = {
        Product:       'Producto',
        Category:      'Categoría',
        Supplier:      'Proveedor',
        Warehouse:     'Bodega',
        Transfer:      'Traslado',
        StockMovement: 'Movimiento',
    };
</script>

<AppLayout>
    <div class="p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-100">Auditoría del sistema</h2>
                <p class="text-sm text-slate-400 mt-0.5">Historial de cambios en entidades clave</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <div class="flex flex-wrap gap-3">
                <input
                    type="text"
                    bind:value={search}
                    placeholder="Buscar..."
                    onkeydown={(e) => e.key === 'Enter' && applyFilters()}
                    class="bg-slate-700/50 border border-slate-600 text-slate-200 rounded-lg px-3 py-2 text-sm w-52 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder:text-slate-500"
                />
                <select
                    bind:value={event}
                    class="bg-slate-700/50 border border-slate-600 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Todos los eventos</option>
                    {#each events as ev}
                        <option value={ev}>{eventLabels[ev] ?? ev}</option>
                    {/each}
                </select>
                <input
                    type="date"
                    bind:value={from}
                    class="bg-slate-700/50 border border-slate-600 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <span class="text-slate-500 self-center text-sm">→</span>
                <input
                    type="date"
                    bind:value={to}
                    class="bg-slate-700/50 border border-slate-600 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <button
                    onclick={applyFilters}
                    class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors"
                >
                    Filtrar
                </button>
                {#if search || event || from || to}
                    <button
                        onclick={clearFilters}
                        class="text-slate-400 hover:text-slate-200 text-sm px-3 py-2 rounded-lg transition-colors"
                    >
                        Limpiar
                    </button>
                {/if}
            </div>
        </div>

        <!-- Table -->
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700/50">
                        <th class="text-left text-slate-400 font-medium px-4 py-3 w-36">Fecha</th>
                        <th class="text-left text-slate-400 font-medium px-4 py-3 w-24">Evento</th>
                        <th class="text-left text-slate-400 font-medium px-4 py-3">Entidad</th>
                        <th class="text-left text-slate-400 font-medium px-4 py-3">Descripción</th>
                        <th class="text-left text-slate-400 font-medium px-4 py-3">Usuario</th>
                        <th class="w-8 px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    {#each logs.data as log (log.id)}
                        <tr
                            class="border-b border-slate-700/30 hover:bg-slate-700/20 cursor-pointer"
                            onclick={() => (log.old || log.attributes) && toggleExpand(log.id)}
                        >
                            <td class="px-4 py-3 text-slate-400 text-xs whitespace-nowrap">
                                {new Date(log.created_at).toLocaleString('es', { dateStyle: 'short', timeStyle: 'short' })}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {eventColors[log.event] ?? 'bg-slate-500/15 text-slate-400'}">
                                    {eventLabels[log.event] ?? log.event}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-300">
                                {modelLabels[log.subject_type] ?? log.subject_type ?? '—'}
                                {#if log.subject_id}
                                    <span class="text-slate-500 text-xs ml-1">#{log.subject_id}</span>
                                {/if}
                            </td>
                            <td class="px-4 py-3 text-slate-400 max-w-xs truncate">{log.description}</td>
                            <td class="px-4 py-3">
                                {#if log.causer}
                                    <span class="text-slate-300">{log.causer.name}</span>
                                {:else}
                                    <span class="text-slate-600">Sistema</span>
                                {/if}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {#if log.old || log.attributes}
                                    <svg class="w-4 h-4 transition-transform {expandedId === log.id ? 'rotate-90' : ''}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                {/if}
                            </td>
                        </tr>

                        <!-- Expanded diff row -->
                        {#if expandedId === log.id && (log.old || log.attributes)}
                            <tr class="bg-slate-900/50">
                                <td colspan="6" class="px-6 py-4">
                                    <div class="grid grid-cols-2 gap-4 text-xs">
                                        {#if log.old}
                                            <div>
                                                <p class="text-slate-500 font-medium uppercase tracking-wide mb-2">Antes</p>
                                                <div class="space-y-1">
                                                    {#each Object.entries(log.old) as [key, val]}
                                                        <div class="flex gap-2">
                                                            <span class="text-slate-500 w-32 shrink-0">{key}</span>
                                                            <span class="text-red-400">{String(val ?? '—')}</span>
                                                        </div>
                                                    {/each}
                                                </div>
                                            </div>
                                        {/if}
                                        {#if log.attributes}
                                            <div>
                                                <p class="text-slate-500 font-medium uppercase tracking-wide mb-2">Después</p>
                                                <div class="space-y-1">
                                                    {#each Object.entries(log.attributes) as [key, val]}
                                                        <div class="flex gap-2">
                                                            <span class="text-slate-500 w-32 shrink-0">{key}</span>
                                                            <span class="text-emerald-400">{String(val ?? '—')}</span>
                                                        </div>
                                                    {/each}
                                                </div>
                                            </div>
                                        {/if}
                                    </div>
                                </td>
                            </tr>
                        {/if}
                    {/each}

                    {#if logs.data.length === 0}
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500">
                                No hay registros de auditoría.
                            </td>
                        </tr>
                    {/if}
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {#if logs.last_page > 1}
            <div class="flex items-center justify-between text-sm text-slate-400">
                <span>{logs.from}–{logs.to} de {logs.total} registros</span>
                <div class="flex gap-1">
                    {#each logs.links as link}
                        {#if link.url}
                            <a
                                href={link.url}
                                class="px-3 py-1.5 rounded-lg transition-colors {link.active ? 'bg-indigo-600 text-white' : 'hover:bg-slate-700 text-slate-400'}"
                            >
                                {@html link.label}
                            </a>
                        {:else}
                            <span class="px-3 py-1.5 text-slate-600">{@html link.label}</span>
                        {/if}
                    {/each}
                </div>
            </div>
        {/if}
    </div>
</AppLayout>
