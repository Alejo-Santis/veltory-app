<script>
    import { useForm, router } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';

    let { users, filters = {}, roles = [] } = $props();

    let search      = $state(filters.search ?? '');
    let roleFilter  = $state(filters.role   ?? '');
    let assigning   = $state(null); // usuario al que se está asignando rol

    const assignForm = useForm({ role: '' });

    let searchTimer;
    function onSearchInput() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilters, 350);
    }

    function applyFilters() {
        router.get('/users', {
            search: search     || undefined,
            role:   roleFilter || undefined,
        }, { preserveState: true, replace: true });
    }

    function openAssign(user) {
        assigning       = user;
        $assignForm.role = user.roles?.[0]?.name ?? '';
        $assignForm.clearErrors();
    }

    function closeAssign() {
        assigning = null;
        $assignForm.reset();
    }

    function submitAssign(e) {
        e.preventDefault();
        $assignForm.post(`/users/${assigning.uuid}/roles`, {
            onSuccess: closeAssign,
        });
    }

    const roleLabels = { admin: 'Administrador', manager: 'Manager', viewer: 'Visor' };
    const roleColors = {
        admin:   'bg-red-500/10 text-red-400 border border-red-500/20',
        manager: 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
        viewer:  'bg-slate-500/10 text-slate-400 border border-slate-500/20',
    };

    function formatDate(d) {
        return new Date(d).toLocaleDateString('es-CO', { year: 'numeric', month: 'short', day: 'numeric' });
    }
</script>

<svelte:head><title>Usuarios</title></svelte:head>

<AppLayout>
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">Gestión de usuarios</h1>
                <p class="text-sm text-slate-400 mt-0.5">{users.total} usuario{users.total !== 1 ? 's' : ''} registrado{users.total !== 1 ? 's' : ''}</p>
            </div>
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
                    placeholder="Buscar por nombre o email…"
                    class="w-full pl-9 pr-4 py-2 bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors"
                />
            </div>
            <select bind:value={roleFilter} onchange={applyFilters}
                class="px-3 py-2 bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-lg text-sm text-white outline-none transition-colors">
                <option value="">Todos los roles</option>
                {#each roles as r}
                    <option value={r.name}>{roleLabels[r.name] ?? r.name}</option>
                {/each}
            </select>
        </div>

        <!-- Tabla -->
        <div class="px-8 py-6">
            {#if users.data.length === 0}
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <svg class="w-12 h-12 text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-slate-400 font-medium">No se encontraron usuarios</p>
                </div>
            {:else}
                <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800">
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuario</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Correo</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Rol</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Registrado</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            {#each users.data as user}
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-semibold text-white flex-shrink-0">
                                                {user.name[0]?.toUpperCase()}
                                            </div>
                                            <span class="font-medium text-white">{user.name}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-400">{user.email}</td>
                                    <td class="px-4 py-3">
                                        {#if user.roles?.length}
                                            <div class="flex flex-wrap gap-1">
                                                {#each user.roles as role}
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {roleColors[role.name] ?? roleColors.viewer}">
                                                        {roleLabels[role.name] ?? role.name}
                                                    </span>
                                                {/each}
                                            </div>
                                        {:else}
                                            <span class="text-xs text-slate-600">Sin rol</span>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3 text-slate-500 text-xs">{formatDate(user.created_at)}</td>
                                    <td class="px-4 py-3">
                                        <button
                                            onclick={() => openAssign(user)}
                                            class="p-1.5 text-slate-500 hover:text-indigo-400 hover:bg-slate-700 rounded-md transition-colors"
                                            title="Asignar rol"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                {#if users.last_page > 1}
                    <div class="mt-6 flex items-center justify-between text-sm text-slate-400">
                        <span>Mostrando {users.from}–{users.to} de {users.total}</span>
                        <div class="flex gap-2">
                            {#each users.links as link}
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

<!-- Modal asignar rol -->
{#if assigning}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick={closeAssign}></div>
        <div class="relative bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-sm shadow-2xl">

            <div class="mb-5">
                <h3 class="text-base font-semibold text-white">Asignar rol</h3>
                <p class="text-sm text-slate-400 mt-0.5">{assigning.name}</p>
            </div>

            <form onsubmit={submitAssign} class="space-y-4">
                <div>
                    <label for="assign-role" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Rol <span class="text-red-400">*</span>
                    </label>
                    <div class="space-y-2">
                        {#each roles as r}
                            <label class="flex items-start gap-3 p-3 bg-slate-800 rounded-lg cursor-pointer hover:bg-slate-700 transition-colors border-2
                                {$assignForm.role === r.name ? 'border-indigo-500' : 'border-transparent'}">
                                <input
                                    type="radio"
                                    name="role"
                                    value={r.name}
                                    bind:group={$assignForm.role}
                                    class="mt-0.5 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-800"
                                />
                                <div>
                                    <p class="text-sm font-medium text-white">{roleLabels[r.name] ?? r.name}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {#if r.name === 'admin'}Acceso completo al sistema, gestión de usuarios y configuración.
                                        {:else if r.name === 'manager'}Gestión de inventario, traslados y reportes. Sin gestión de usuarios.
                                        {:else}Solo lectura. Puede ver inventario y reportes sin modificar nada.{/if}
                                    </p>
                                </div>
                            </label>
                        {/each}
                    </div>
                    {#if $assignForm.errors.role}
                        <p class="mt-1 text-xs text-red-400">{$assignForm.errors.role}</p>
                    {/if}
                </div>

                <div class="flex gap-3 pt-1">
                    <button
                        type="submit"
                        disabled={$assignForm.processing || !$assignForm.role}
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold rounded-lg transition-colors"
                    >
                        {$assignForm.processing ? 'Asignando...' : 'Asignar rol'}
                    </button>
                    <button
                        type="button"
                        onclick={closeAssign}
                        class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-lg transition-colors"
                    >
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}
