<script>
    import { useForm } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';
    import Breadcrumb from '@/Components/Breadcrumb.svelte';

    const form = useForm({
        name:         '',
        contact_name: '',
        email:        '',
        phone:        '',
        address:      '',
        notes:        '',
        is_active:    true,
    });

    function submit(e) {
        e.preventDefault();
        $form.post('/suppliers');
    }
</script>

<svelte:head><title>Nuevo proveedor</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">
        <div class="border-b border-slate-800 px-8 py-5 flex items-center gap-4">
            <a href="/suppliers" class="text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-white">Nuevo proveedor</h1>
                <p class="text-sm text-slate-400 mt-0.5">Completa los datos para registrar el proveedor</p>
            </div>
        </div>

        <div class="p-8 max-w-2xl">
            <Breadcrumb items={[{ label: 'Proveedores', href: '/suppliers' }, { label: 'Nuevo proveedor' }]} />
            <form onsubmit={submit} class="space-y-6">

                <!-- Datos principales -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-white">Información del proveedor</h2>

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Nombre <span class="text-red-400">*</span>
                        </label>
                        <input
                            id="name"
                            type="text"
                            bind:value={$form.name}
                            placeholder="Ej: Distribuidora Tech S.A."
                            class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors
                                {$form.errors.name ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                        />
                        {#if $form.errors.name}
                            <p class="mt-1.5 text-xs text-red-400">{$form.errors.name}</p>
                        {/if}
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="contact_name" class="block text-sm font-medium text-slate-300 mb-1.5">Nombre de contacto</label>
                            <input
                                id="contact_name"
                                type="text"
                                bind:value={$form.contact_name}
                                placeholder="Ej: Juan Pérez"
                                class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"
                            />
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-300 mb-1.5">Teléfono</label>
                            <input
                                id="phone"
                                type="tel"
                                bind:value={$form.phone}
                                placeholder="Ej: +52 55 1234 5678"
                                class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Correo electrónico</label>
                        <input
                            id="email"
                            type="email"
                            bind:value={$form.email}
                            placeholder="contacto@proveedor.com"
                            class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors
                                {$form.errors.email ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                        />
                        {#if $form.errors.email}
                            <p class="mt-1.5 text-xs text-red-400">{$form.errors.email}</p>
                        {/if}
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-slate-300 mb-1.5">Dirección</label>
                        <textarea
                            id="address"
                            bind:value={$form.address}
                            rows="2"
                            placeholder="Calle, número, colonia, ciudad..."
                            class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors resize-none"
                        ></textarea>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-slate-300 mb-1.5">Notas internas</label>
                        <textarea
                            id="notes"
                            bind:value={$form.notes}
                            rows="3"
                            placeholder="Condiciones de pago, tiempos de entrega, observaciones..."
                            class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors resize-none"
                        ></textarea>
                    </div>
                </div>

                <!-- Estado -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            bind:checked={$form.is_active}
                            class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900"
                        />
                        <div>
                            <span class="text-sm font-medium text-slate-300">Proveedor activo</span>
                            <p class="text-xs text-slate-500">Los proveedores inactivos no aparecen al asignar productos</p>
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
                        {$form.processing ? 'Guardando...' : 'Crear proveedor'}
                    </button>
                    <a href="/suppliers" class="px-5 py-2.5 text-sm text-slate-400 hover:text-white transition-colors">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>
</AppLayout>
