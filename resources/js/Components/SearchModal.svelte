<script>
    import { router } from '@inertiajs/svelte';

    let { open = $bindable(false) } = $props();

    let query      = $state('');
    let results    = $state([]);
    let loading    = $state(false);
    let activeIdx  = $state(-1);
    let inputEl    = $state(null);

    let debounceTimer;

    function close() {
        open      = false;
        query     = '';
        results   = [];
        activeIdx = -1;
    }

    $effect(() => {
        if (open && inputEl) {
            setTimeout(() => inputEl?.focus(), 50);
        }
    });

    $effect(() => {
        if (!query.trim() || query.length < 2) {
            results   = [];
            activeIdx = -1;
            loading   = false;
            clearTimeout(debounceTimer);
            return;
        }

        loading = true;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            try {
                const res = await fetch(`/search?q=${encodeURIComponent(query)}`);
                results   = await res.json();
                activeIdx = results.length > 0 ? 0 : -1;
            } catch {
                results = [];
            } finally {
                loading = false;
            }
        }, 200);
    });

    function onKeydown(e) {
        if (!open) return;

        if (e.key === 'Escape') {
            close();
            return;
        }

        if (results.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIdx = (activeIdx + 1) % results.length;
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIdx = (activeIdx - 1 + results.length) % results.length;
        } else if (e.key === 'Enter' && activeIdx >= 0) {
            e.preventDefault();
            navigate(results[activeIdx].href);
        }
    }

    function navigate(href) {
        close();
        router.visit(href);
    }

    const typeIcons = {
        product: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>`,
        category: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>`,
        supplier: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>`,
        warehouse: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>`,
    };

    const typeLabels = {
        product:   'Producto',
        category:  'Categoría',
        supplier:  'Proveedor',
        warehouse: 'Bodega',
    };
</script>

<svelte:window onkeydown={onKeydown} />

{#if open}
    <!-- Backdrop -->
    <div
        class="fixed inset-0 z-50 flex items-start justify-center pt-20 px-4"
        role="dialog"
        aria-modal="true"
    >
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick={close}></div>

        <!-- Panel -->
        <div class="relative w-full max-w-xl bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl overflow-hidden">

            <!-- Input -->
            <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-800">
                <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input
                    bind:this={inputEl}
                    bind:value={query}
                    type="text"
                    placeholder="Buscar productos, categorías, proveedores, bodegas..."
                    class="flex-1 bg-transparent text-white placeholder-slate-500 text-sm outline-none"
                    autocomplete="off"
                />
                {#if loading}
                    <svg class="w-4 h-4 text-slate-500 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                {:else}
                    <kbd class="hidden sm:inline-flex items-center px-1.5 py-0.5 rounded text-xs font-mono text-slate-500 border border-slate-700">Esc</kbd>
                {/if}
            </div>

            <!-- Results -->
            {#if results.length > 0}
                <ul class="py-2 max-h-80 overflow-y-auto divide-y divide-slate-800/50">
                    {#each results as result, i}
                        <li>
                            <button
                                onclick={() => navigate(result.href)}
                                onmouseenter={() => activeIdx = i}
                                class="w-full flex items-center gap-3 px-4 py-2.5 text-left transition-colors
                                    {activeIdx === i ? 'bg-indigo-600/20' : 'hover:bg-slate-800/50'}"
                            >
                                <!-- Icon -->
                                <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center flex-shrink-0">
                                    {#if result.type === 'category' && result.color}
                                        <div class="w-3 h-3 rounded-full" style="background-color: {result.color}"></div>
                                    {:else}
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            {@html typeIcons[result.type] ?? ''}
                                        </svg>
                                    {/if}
                                </div>

                                <!-- Text -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white truncate">{result.label}</p>
                                    <p class="text-xs text-slate-500 truncate">{result.sub}</p>
                                </div>

                                <!-- Type badge -->
                                <span class="text-xs text-slate-600 flex-shrink-0">{typeLabels[result.type] ?? ''}</span>

                                <!-- Arrow -->
                                {#if activeIdx === i}
                                    <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                {/if}
                            </button>
                        </li>
                    {/each}
                </ul>
            {:else if query.length >= 2 && !loading}
                <div class="py-10 text-center">
                    <p class="text-sm text-slate-500">Sin resultados para <span class="text-slate-300">"{query}"</span></p>
                </div>
            {:else if !query}
                <div class="px-4 py-4">
                    <p class="text-xs text-slate-600 mb-3 font-medium uppercase tracking-wider">Accesos rápidos</p>
                    <div class="grid grid-cols-2 gap-1">
                        {#each [
                            { label: 'Productos',   href: '/products',        icon: 'product'   },
                            { label: 'Categorías',  href: '/categories',      icon: 'category'  },
                            { label: 'Proveedores', href: '/suppliers',        icon: 'supplier'  },
                            { label: 'Bodegas',     href: '/warehouses',      icon: 'warehouse' },
                            { label: 'Movimientos', href: '/stock-movements', icon: 'product'   },
                            { label: 'Traslados',   href: '/transfers',       icon: 'warehouse' },
                        ] as shortcut}
                            <button
                                onclick={() => navigate(shortcut.href)}
                                class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-slate-800 transition-colors text-left"
                            >
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {@html typeIcons[shortcut.icon] ?? ''}
                                </svg>
                                {shortcut.label}
                            </button>
                        {/each}
                    </div>
                </div>
            {/if}

            <!-- Footer hint -->
            <div class="px-4 py-2.5 border-t border-slate-800 flex items-center gap-4 text-xs text-slate-600">
                <span><kbd class="font-mono">↑↓</kbd> navegar</span>
                <span><kbd class="font-mono">Enter</kbd> abrir</span>
                <span><kbd class="font-mono">Esc</kbd> cerrar</span>
            </div>
        </div>
    </div>
{/if}
