<script>
    import { useForm } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';

    let { user } = $props();

    // Formulario de datos personales
    const profileForm = useForm({
        name:  user.name,
        email: user.email,
    });

    // Formulario de contraseña
    const passwordForm = useForm({
        current_password: '',
        password:         '',
        password_confirmation: '',
    });

    function submitProfile(e) {
        e.preventDefault();
        $profileForm.put('/profile');
    }

    function submitPassword(e) {
        e.preventDefault();
        $passwordForm.put('/profile/password', {
            onSuccess: () => {
                $passwordForm.reset();
            },
        });
    }

    const roleLabels = { admin: 'Administrador', manager: 'Manager', viewer: 'Visor' };
    const roleColors = {
        admin:   'bg-red-500/10 text-red-400 border border-red-500/20',
        manager: 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
        viewer:  'bg-slate-500/10 text-slate-400 border border-slate-500/20',
    };
</script>

<svelte:head><title>Mi perfil</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5">
            <h1 class="text-xl font-semibold text-white">Mi perfil</h1>
            <p class="text-sm text-slate-400 mt-0.5">Administra tu información personal y contraseña</p>
        </div>

        <div class="px-8 py-6 max-w-2xl space-y-6">

            <!-- Rol actual -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-slate-300 mb-3">Rol en el sistema</h2>
                <div class="flex flex-wrap gap-2">
                    {#each (user.roles ?? []) as role}
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {roleColors[role] ?? roleColors.viewer}">
                            {roleLabels[role] ?? role}
                        </span>
                    {:else}
                        <span class="text-sm text-slate-500">Sin rol asignado</span>
                    {/each}
                </div>
            </div>

            <!-- Datos personales -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-slate-300 mb-4">Información personal</h2>

                <form onsubmit={submitProfile} class="space-y-4">
                    <div>
                        <label for="p-name" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Nombre <span class="text-red-400">*</span>
                        </label>
                        <input
                            id="p-name"
                            type="text"
                            bind:value={$profileForm.name}
                            class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white outline-none transition-colors
                                {$profileForm.errors.name ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                        />
                        {#if $profileForm.errors.name}
                            <p class="mt-1 text-xs text-red-400">{$profileForm.errors.name}</p>
                        {/if}
                    </div>

                    <div>
                        <label for="p-email" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Correo electrónico <span class="text-red-400">*</span>
                        </label>
                        <input
                            id="p-email"
                            type="email"
                            bind:value={$profileForm.email}
                            class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white outline-none transition-colors
                                {$profileForm.errors.email ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                        />
                        {#if $profileForm.errors.email}
                            <p class="mt-1 text-xs text-red-400">{$profileForm.errors.email}</p>
                        {/if}
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <button
                            type="submit"
                            disabled={$profileForm.processing || !$profileForm.isDirty}
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-lg transition-colors"
                        >
                            {$profileForm.processing ? 'Guardando...' : 'Guardar cambios'}
                        </button>
                        {#if $profileForm.recentlySuccessful}
                            <span class="text-sm text-emerald-400">Guardado correctamente.</span>
                        {/if}
                    </div>
                </form>
            </div>

            <!-- Cambiar contraseña -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-slate-300 mb-4">Cambiar contraseña</h2>

                <form onsubmit={submitPassword} class="space-y-4">
                    <div>
                        <label for="pw-current" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Contraseña actual <span class="text-red-400">*</span>
                        </label>
                        <input
                            id="pw-current"
                            type="password"
                            bind:value={$passwordForm.current_password}
                            autocomplete="current-password"
                            class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white outline-none transition-colors
                                {$passwordForm.errors.current_password ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                        />
                        {#if $passwordForm.errors.current_password}
                            <p class="mt-1 text-xs text-red-400">{$passwordForm.errors.current_password}</p>
                        {/if}
                    </div>

                    <div>
                        <label for="pw-new" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Nueva contraseña <span class="text-red-400">*</span>
                        </label>
                        <input
                            id="pw-new"
                            type="password"
                            bind:value={$passwordForm.password}
                            autocomplete="new-password"
                            class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white outline-none transition-colors
                                {$passwordForm.errors.password ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                        />
                        {#if $passwordForm.errors.password}
                            <p class="mt-1 text-xs text-red-400">{$passwordForm.errors.password}</p>
                        {/if}
                    </div>

                    <div>
                        <label for="pw-confirm" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Confirmar nueva contraseña <span class="text-red-400">*</span>
                        </label>
                        <input
                            id="pw-confirm"
                            type="password"
                            bind:value={$passwordForm.password_confirmation}
                            autocomplete="new-password"
                            class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white outline-none transition-colors
                                {$passwordForm.errors.password_confirmation ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                        />
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <button
                            type="submit"
                            disabled={$passwordForm.processing}
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-lg transition-colors"
                        >
                            {$passwordForm.processing ? 'Actualizando...' : 'Actualizar contraseña'}
                        </button>
                        {#if $passwordForm.recentlySuccessful}
                            <span class="text-sm text-emerald-400">Contraseña actualizada.</span>
                        {/if}
                    </div>
                </form>
            </div>

        </div>
    </div>
</AppLayout>
