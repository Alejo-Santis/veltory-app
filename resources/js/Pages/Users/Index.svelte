<script>
    import { useForm, router } from '@inertiajs/svelte';
    import AppLayout from '../../Layouts/AppLayout.svelte';

    let { users, filters = {}, roles = [] } = $props();

    let search      = $state(filters.search ?? '');
    let roleFilter  = $state(filters.role   ?? '');
    let assigning   = $state(null);
    let deleting    = $state(null);
    let showCreate  = $state(false);
    let editing     = $state(null);

    const assignForm = useForm({ role: '' });
    const createForm = useForm({ name: '', email: '', password: '', role: 'viewer' });
    const editForm   = useForm({ name: '', email: '', password: '' });

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
        assigning        = user;
        $assignForm.role = user.roles?.[0]?.name ?? '';
        $assignForm.clearErrors();
    }

    function closeAssign() {
        assigning = null;
        $assignForm.reset();
    }

    function submitAssign(e) {
        e.preventDefault();
        $assignForm.post(`/users/${assigning.id}/roles`, {
            onSuccess: closeAssign,
        });
    }

    function openCreate() {
        $createForm.reset();
        $createForm.clearErrors();
        showCreate = true;
    }

    function closeCreate() {
        showCreate = false;
        $createForm.reset();
        $createForm.clearErrors();
    }

    function submitCreate(e) {
        e.preventDefault();
        $createForm.post('/users', { onSuccess: closeCreate });
    }

    function openEdit(user) {
        editing = user;
        $editForm.name     = user.name;
        $editForm.email    = user.email;
        $editForm.password = '';
        $editForm.clearErrors();
    }

    function closeEdit() {
        editing = null;
        $editForm.reset();
        $editForm.clearErrors();
    }

    function submitEdit(e) {
        e.preventDefault();
        $editForm.put(`/users/${editing.id}`, { onSuccess: closeEdit });
    }

    function confirmDelete(user) {
        deleting = user;
    }

    function doDelete() {
        if (!deleting) return;
        router.delete(`/users/${deleting.id}`, {
            onFinish: () => { deleting = null; },
        });
    }

    const roleLabels = { admin: 'Administrador', manager: 'Manager', viewer: 'Visor' };
    const roleColors = {
        admin:   'bg-red-500/10 text-red-400 border border-red-500/20',
        manager: 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
        viewer:  'bg-slate-500/10 text-slate-400 border border-slate-500/20',
    };
    const roleDescriptions = {
        admin:   'Acceso completo, gestión de usuarios y configuración.',
        manager: 'Gestión de inventario, traslados y reportes.',
        viewer:  'Solo lectura. No puede modificar datos.',
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
            <button
                onclick={openCreate}
                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo usuario
            </button>
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
                                        <div class="flex items-center gap-1 justify-end">
                                            <button
                                                onclick={() => openEdit(user)}
                                                class="p-1.5 text-slate-500 hover:text-white hover:bg-slate-700 rounded-md transition-colors"
                                                title="Editar usuario"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <button
                                                onclick={() => openAssign(user)}
                                                class="p-1.5 text-slate-500 hover:text-indigo-400 hover:bg-slate-700 rounded-md transition-colors"
                                                title="Cambiar rol"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </button>
                                            <button
                                                onclick={() => confirmDelete(user)}
                                                class="p-1.5 text-slate-500 hover:text-red-400 hover:bg-slate-700 rounded-md transition-colors"
                                                title="Eliminar usuario"
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

<!-- Modal: Nuevo usuario -->
{#if showCreate}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick={closeCreate} role="presentation"></div>
        <div class="relative bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-md shadow-2xl">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-white">Nuevo usuario</h3>
                <p class="text-sm text-slate-400 mt-0.5">El usuario recibirá acceso según el rol asignado.</p>
            </div>

            <form onsubmit={submitCreate} class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Nombre <span class="text-red-400">*</span></label>
                    <input
                        type="text"
                        bind:value={$createForm.name}
                        placeholder="Nombre completo"
                        class="w-full px-3 py-2.5 bg-slate-800 border {$createForm.errors.name ? 'border-red-500' : 'border-slate-700'} rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                    />
                    {#if $createForm.errors.name}
                        <p class="mt-1 text-xs text-red-400">{$createForm.errors.name}</p>
                    {/if}
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Correo electrónico <span class="text-red-400">*</span></label>
                    <input
                        type="email"
                        bind:value={$createForm.email}
                        placeholder="correo@ejemplo.com"
                        class="w-full px-3 py-2.5 bg-slate-800 border {$createForm.errors.email ? 'border-red-500' : 'border-slate-700'} rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                    />
                    {#if $createForm.errors.email}
                        <p class="mt-1 text-xs text-red-400">{$createForm.errors.email}</p>
                    {/if}
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Contraseña <span class="text-red-400">*</span></label>
                    <input
                        type="password"
                        bind:value={$createForm.password}
                        placeholder="Mín. 8 caracteres con mayúsculas y números"
                        class="w-full px-3 py-2.5 bg-slate-800 border {$createForm.errors.password ? 'border-red-500' : 'border-slate-700'} rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                    />
                    {#if $createForm.errors.password}
                        <p class="mt-1 text-xs text-red-400">{$createForm.errors.password}</p>
                    {/if}
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Rol <span class="text-red-400">*</span></label>
                    <div class="space-y-2">
                        {#each roles as r}
                            <label class="flex items-start gap-3 p-3 bg-slate-800 rounded-lg cursor-pointer hover:bg-slate-700 transition-colors border-2
                                {$createForm.role === r.name ? 'border-indigo-500' : 'border-transparent'}">
                                <input type="radio" name="create-role" value={r.name}
                                    bind:group={$createForm.role}
                                    class="mt-0.5 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-800"
                                />
                                <div>
                                    <p class="text-sm font-medium text-white">{roleLabels[r.name] ?? r.name}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{roleDescriptions[r.name] ?? ''}</p>
                                </div>
                            </label>
                        {/each}
                    </div>
                    {#if $createForm.errors.role}
                        <p class="mt-1 text-xs text-red-400">{$createForm.errors.role}</p>
                    {/if}
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit" disabled={$createForm.processing}
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold rounded-lg transition-colors">
                        {$createForm.processing ? 'Creando...' : 'Crear usuario'}
                    </button>
                    <button type="button" onclick={closeCreate}
                        class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-lg transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}

<!-- Modal: Editar usuario -->
{#if editing}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick={closeEdit} role="presentation"></div>
        <div class="relative bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-md shadow-2xl">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-white">Editar usuario</h3>
                <p class="text-sm text-slate-400 mt-0.5">Modifica los datos de la cuenta.</p>
            </div>

            <form onsubmit={submitEdit} class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Nombre <span class="text-red-400">*</span></label>
                    <input
                        type="text"
                        bind:value={$editForm.name}
                        placeholder="Nombre completo"
                        class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors
                            {$editForm.errors.name ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                    />
                    {#if $editForm.errors.name}
                        <p class="mt-1 text-xs text-red-400">{$editForm.errors.name}</p>
                    {/if}
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Correo electrónico <span class="text-red-400">*</span></label>
                    <input
                        type="email"
                        bind:value={$editForm.email}
                        placeholder="correo@ejemplo.com"
                        class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors
                            {$editForm.errors.email ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                    />
                    {#if $editForm.errors.email}
                        <p class="mt-1 text-xs text-red-400">{$editForm.errors.email}</p>
                    {/if}
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">
                        Nueva contraseña
                        <span class="text-slate-500 font-normal text-xs">(dejar vacío para no cambiarla)</span>
                    </label>
                    <input
                        type="password"
                        bind:value={$editForm.password}
                        placeholder="Mínimo 8 caracteres"
                        class="w-full px-3.5 py-2.5 bg-slate-800 border rounded-lg text-sm text-white placeholder-slate-500 outline-none transition-colors
                            {$editForm.errors.password ? 'border-red-500' : 'border-slate-700 focus:border-indigo-500'}"
                    />
                    {#if $editForm.errors.password}
                        <p class="mt-1 text-xs text-red-400">{$editForm.errors.password}</p>
                    {/if}
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit" disabled={$editForm.processing}
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold rounded-lg transition-colors">
                        {$editForm.processing ? 'Guardando...' : 'Guardar cambios'}
                    </button>
                    <button type="button" onclick={closeEdit}
                        class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-lg transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}

<!-- Modal: Cambiar rol -->
{#if assigning}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick={closeAssign} role="presentation"></div>
        <div class="relative bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-sm shadow-2xl">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-white">Cambiar rol</h3>
                <p class="text-sm text-slate-400 mt-0.5">{assigning.name}</p>
            </div>

            <form onsubmit={submitAssign} class="space-y-4">
                <div class="space-y-2">
                    {#each roles as r}
                        <label class="flex items-start gap-3 p-3 bg-slate-800 rounded-lg cursor-pointer hover:bg-slate-700 transition-colors border-2
                            {$assignForm.role === r.name ? 'border-indigo-500' : 'border-transparent'}">
                            <input type="radio" name="assign-role" value={r.name}
                                bind:group={$assignForm.role}
                                class="mt-0.5 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-800"
                            />
                            <div>
                                <p class="text-sm font-medium text-white">{roleLabels[r.name] ?? r.name}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{roleDescriptions[r.name] ?? ''}</p>
                            </div>
                        </label>
                    {/each}
                </div>
                {#if $assignForm.errors.role}
                    <p class="mt-1 text-xs text-red-400">{$assignForm.errors.role}</p>
                {/if}

                <div class="flex gap-3 pt-1">
                    <button type="submit" disabled={$assignForm.processing || !$assignForm.role}
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold rounded-lg transition-colors">
                        {$assignForm.processing ? 'Guardando...' : 'Guardar rol'}
                    </button>
                    <button type="button" onclick={closeAssign}
                        class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-lg transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}

<!-- Modal: Confirmar eliminar -->
{#if deleting}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick={() => deleting = null} role="presentation"></div>
        <div class="relative bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-sm shadow-2xl">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-500/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-white">Eliminar usuario</h3>
                    <p class="text-sm text-slate-400 mt-0.5">¿Eliminar a <span class="text-white font-medium">{deleting.name}</span>? Esta acción no se puede deshacer.</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button onclick={doDelete}
                    class="flex-1 py-2.5 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-lg transition-colors">
                    Sí, eliminar
                </button>
                <button onclick={() => deleting = null}
                    class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-lg transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
{/if}
