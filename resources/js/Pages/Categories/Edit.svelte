<script>
    import { useForm } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';

    let { category, parents = [] } = $props();

    const form = useForm({
        name:        category.name,
        parent_id:   category.parent_id ?? null,
        description: category.description ?? '',
        color:       category.color ?? '#6366f1',
        is_active:   category.is_active,
        sort_order:  category.sort_order ?? 0,
    });

    function submit(e) {
        e.preventDefault();
        $form.put(`/categories/${category.uuid}`);
    }
</script>

<svelte:head><title>Editar categoría</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">
        <div class="border-b border-slate-800 px-8 py-5 flex items-center gap-4">
            <a href="/categories" class="text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-white">Editar categoría</h1>
                <p class="text-sm text-slate-400 mt-0.5">{category.name}</p>
            </div>
        </div>

        <div class="p-8 max-w-2xl">
            <form onsubmit={submit} class="space-y-6">

                <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-white">Información básica</h2>

                    <!-- Nombre -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Nombre <span class="text-red-400">*</span>
                        </label>
                        <input
                            id="name"
                            type="text"
                            bind:value={$form.name}
                            placeholder="Ej: Electrónica"
                            class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors
                                {$form.errors.name ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                        />
                        {#if $form.errors.name}
                            <p class="mt-1.5 text-xs text-red-400">{$form.errors.name}</p>
                        {/if}
                    </div>

                    <!-- Categoría padre -->
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Categoría padre <span class="text-slate-500">(opcional)</span>
                        </label>
                        <select
                            id="parent_id"
                            bind:value={$form.parent_id}
                            class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"
                        >
                            <option value={null}>— Sin categoría padre —</option>
                            {#each parents as parent}
                                <option value={parent.id}>{parent.name}</option>
                            {/each}
                        </select>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Descripción <span class="text-slate-500">(opcional)</span>
                        </label>
                        <textarea
                            id="description"
                            bind:value={$form.description}
                            rows="3"
                            placeholder="Breve descripción de esta categoría..."
                            class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors resize-none"
                        ></textarea>
                    </div>
                </div>

                <!-- Apariencia -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-white">Apariencia</h2>

                    <div class="flex items-end gap-4">
                        <!-- Color picker -->
                        <div class="flex-1">
                            <label for="color" class="block text-sm font-medium text-slate-300 mb-1.5">
                                Color de identificación
                            </label>
                            <div class="flex items-center gap-3">
                                <input
                                    id="color"
                                    type="color"
                                    bind:value={$form.color}
                                    class="w-10 h-10 rounded-lg border border-slate-700 bg-slate-800 cursor-pointer p-0.5"
                                />
                                <input
                                    type="text"
                                    bind:value={$form.color}
                                    placeholder="#6366f1"
                                    maxlength="7"
                                    class="flex-1 px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none font-mono transition-colors"
                                />
                            </div>
                            {#if $form.errors.color}
                                <p class="mt-1.5 text-xs text-red-400">{$form.errors.color}</p>
                            {/if}
                        </div>

                        <!-- Sort order -->
                        <div class="w-32">
                            <label for="sort_order" class="block text-sm font-medium text-slate-300 mb-1.5">
                                Orden
                            </label>
                            <input
                                id="sort_order"
                                type="number"
                                bind:value={$form.sort_order}
                                min="0"
                                class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors"
                            />
                        </div>
                    </div>

                    <!-- Estado -->
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            bind:checked={$form.is_active}
                            class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900"
                        />
                        <div>
                            <span class="text-sm font-medium text-slate-300">Categoría activa</span>
                            <p class="text-xs text-slate-500">Las categorías inactivas no se muestran al asignar productos</p>
                        </div>
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        disabled={$form.processing}
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold rounded-lg transition-colors"
                    >
                        {$form.processing ? 'Guardando...' : 'Guardar cambios'}
                    </button>
                    <a href="/categories" class="px-5 py-2.5 text-sm text-slate-400 hover:text-white transition-colors">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</AppLayout>
