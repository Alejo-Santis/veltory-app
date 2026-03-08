<script>
    import { router, page } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';
    import { notify } from '../../lib/toast';

    let { categories = [] } = $props();

    const canWrite = $derived($page.props.auth?.user?.can_write ?? false);

    let search   = $state('');
    let deleting = $state(null);

    const filtered = $derived.by(() => {
        if (!search.trim()) return categories;
        const q = search.toLowerCase();
        return categories.filter(c =>
            c.name.toLowerCase().includes(q) ||
            c.children?.some(ch => ch.name.toLowerCase().includes(q))
        );
    });

    const totalCategories = $derived(
        filtered.reduce((sum, c) => sum + 1 + (c.children?.length ?? 0), 0)
    );

    function confirmDelete(category) {
        deleting = category;
    }

    function cancelDelete() {
        deleting = null;
    }

    function doDelete() {
        if (!deleting) return;
        router.delete(`/categories/${deleting.uuid}`, {
            onFinish: () => (deleting = null),
        });
    }
</script>

<svelte:head><title>Categorías</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">
        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">Categorías</h1>
                <p class="text-sm text-slate-400 mt-0.5">{totalCategories} categorías en total</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Search -->
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input
                        type="text"
                        bind:value={search}
                        placeholder="Buscar categoría..."
                        class="pl-9 pr-3.5 py-2 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors w-56"
                    />
                </div>
                {#if canWrite}
                    <a
                        href="/categories/create"
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nueva categoría
                    </a>
                {/if}
            </div>
        </div>

        <div class="p-8">
            {#if filtered.length === 0}
                <div class="flex flex-col items-center justify-center py-20 text-slate-500">
                    <svg class="w-12 h-12 mb-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    {#if search}
                        <p class="text-sm font-medium">Sin resultados para "{search}"</p>
                    {:else}
                        <p class="text-sm font-medium">No hay categorías todavía</p>
                        {#if canWrite}
                            <a href="/categories/create" class="mt-3 text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
                                Crear la primera categoría →
                            </a>
                        {/if}
                    {/if}
                </div>
            {:else}
                <div class="space-y-3">
                    {#each filtered as category}
                        <!-- Categoría padre -->
                        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                            <div class="flex items-center gap-4 px-5 py-4">
                                <!-- Color dot -->
                                <div
                                    class="w-3 h-3 rounded-full flex-shrink-0"
                                    style="background-color: {category.color ?? '#6366f1'}"
                                ></div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <a href="/categories/{category.uuid}" class="font-semibold text-white text-sm hover:text-indigo-300 transition-colors">{category.name}</a>
                                        {#if !category.is_active}
                                            <span class="px-1.5 py-0.5 text-xs bg-slate-700 text-slate-400 rounded">Inactiva</span>
                                        {/if}
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {category.products_count} producto{category.products_count !== 1 ? 's' : ''}
                                        {#if category.children?.length}
                                            · {category.children.length} subcategoría{category.children.length !== 1 ? 's' : ''}
                                        {/if}
                                    </p>
                                </div>

                                <!-- Acciones -->
                                <div class="flex items-center gap-1">
                                    <a
                                        href="/categories/{category.uuid}"
                                        class="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-colors"
                                        title="Ver detalle"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    {#if canWrite}
                                        <a
                                            href="/categories/{category.uuid}/edit"
                                            class="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-colors"
                                            title="Editar"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <button
                                            onclick={() => confirmDelete(category)}
                                            class="p-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
                                            title="Eliminar"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    {/if}
                                </div>
                            </div>

                            <!-- Subcategorías -->
                            {#if category.children?.length}
                                <div class="border-t border-slate-800">
                                    {#each category.children as child, i}
                                        <div class="flex items-center gap-4 px-5 py-3 {i < category.children.length - 1 ? 'border-b border-slate-800/60' : ''} hover:bg-slate-800/40 transition-colors">
                                            <!-- Indent + connector -->
                                            <div class="flex items-center gap-2 pl-5">
                                                <div class="w-px h-4 bg-slate-700"></div>
                                                <div class="w-3 h-px bg-slate-700"></div>
                                                <div
                                                    class="w-2 h-2 rounded-full flex-shrink-0"
                                                    style="background-color: {child.color ?? category.color ?? '#6366f1'}"
                                                ></div>
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm text-slate-300">{child.name}</span>
                                                    {#if !child.is_active}
                                                        <span class="px-1.5 py-0.5 text-xs bg-slate-700 text-slate-500 rounded">Inactiva</span>
                                                    {/if}
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-1">
                                                <a
                                                    href="/categories/{child.uuid}"
                                                    class="p-1.5 text-slate-500 hover:text-white hover:bg-slate-700 rounded-lg transition-colors"
                                                    title="Ver detalle"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                                {#if canWrite}
                                                    <a
                                                        href="/categories/{child.uuid}/edit"
                                                        class="p-1.5 text-slate-500 hover:text-white hover:bg-slate-700 rounded-lg transition-colors"
                                                        title="Editar"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </a>
                                                    <button
                                                        onclick={() => confirmDelete(child)}
                                                        class="p-1.5 text-slate-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
                                                        title="Eliminar"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                {/if}
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            {/if}
                        </div>
                    {/each}
                </div>
            {/if}
        </div>
    </div>

    <!-- Modal de confirmación de borrado -->
    {#if deleting}
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick={cancelDelete}></div>
            <div class="relative bg-slate-900 border border-slate-700 rounded-xl p-6 w-full max-w-sm shadow-2xl">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-red-500/15 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-white">Eliminar categoría</h3>
                        <p class="text-sm text-slate-400 mt-1">
                            ¿Confirmas eliminar <strong class="text-white">"{deleting.name}"</strong>?
                            Esta acción no se puede deshacer.
                        </p>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button
                        onclick={cancelDelete}
                        class="flex-1 px-4 py-2 text-sm font-medium text-slate-300 bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        onclick={doDelete}
                        class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-500 rounded-lg transition-colors"
                    >
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    {/if}
</AppLayout>
