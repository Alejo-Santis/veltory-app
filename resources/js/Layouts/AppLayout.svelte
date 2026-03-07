<script>
    import { page, router } from '@inertiajs/svelte';
    import { Toaster, toast } from 'svelte-sonner';
    import VeltoryLogo from '../Components/VeltoryLogo.svelte';

    let { children } = $props();

    // Persiste el estado del sidebar en localStorage
    let collapsed = $state(
        typeof localStorage !== 'undefined'
            ? localStorage.getItem('sidebar-collapsed') === 'true'
            : false
    );

    function toggleSidebar() {
        collapsed = !collapsed;
        localStorage.setItem('sidebar-collapsed', String(collapsed));
    }

    function logout(e) {
        e.preventDefault();
        router.post('/logout');
    }

    $effect(() => {
        const flash = $page.props.flash;
        if (flash?.success) toast.success(flash.success);
        if (flash?.error)   toast.error(flash.error);
        if (flash?.warning) toast.warning(flash.warning);
        if (flash?.info)    toast.info(flash.info);
    });

    const navigation = [
        { name: 'Dashboard',    href: '/',               icon: 'grid' },
        { name: 'Productos',    href: '/products',       icon: 'package' },
        { name: 'Categorías',   href: '/categories',     icon: 'tag' },
        { name: 'Proveedores',  href: '/suppliers',      icon: 'truck' },
        { name: 'Unidades',     href: '/units',          icon: 'ruler' },
        { name: 'Movimientos',  href: '/stock-movements',icon: 'activity' },
        { name: 'Bodegas',      href: '/warehouses',     icon: 'warehouse' },
        { name: 'Traslados',    href: '/transfers',      icon: 'transfer' },
    ];

    // Sección de administración — solo visible para admin
    const adminNav = [
        { name: 'Usuarios',     href: '/users',          icon: 'users' },
    ];

    const isAdmin = $derived($page.props.auth?.user?.is_admin ?? false);

    function isActive(href) {
        if (href === '/') return $page.url === '/';
        return $page.url.startsWith(href);
    }
</script>

<div class="flex h-screen bg-slate-950 text-slate-100 overflow-hidden">
    <!-- Sidebar -->
    <aside
        class="flex-shrink-0 flex flex-col bg-slate-900 border-r border-slate-800 transition-all duration-300 ease-in-out
            {collapsed ? 'w-16' : 'w-64'}"
    >
        <!-- Logo + toggle -->
        <div class="flex items-center h-16 border-b border-slate-800 {collapsed ? 'justify-center px-0' : 'px-4 gap-2'}">
            {#if !collapsed}
                <div class="flex-1 min-w-0">
                    <VeltoryLogo size={26} showWordmark={true} />
                </div>
            {:else}
                <VeltoryLogo size={26} showWordmark={false} />
            {/if}
            <button
                onclick={toggleSidebar}
                title={collapsed ? 'Expandir menú' : 'Colapsar menú'}
                class="flex-shrink-0 w-7 h-7 flex items-center justify-center rounded-md text-slate-500 hover:text-slate-200 hover:bg-slate-800 transition-colors
                    {collapsed ? 'absolute left-1/2 -translate-x-1/2 mt-9' : ''}"
            >
                {#if collapsed}
                    <!-- Chevron right (expand) -->
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                {:else}
                    <!-- Hamburger -->
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                {/if}
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto overflow-x-hidden">
            {#each [...navigation, ...(isAdmin ? adminNav : [])] as item}
                {@const active = isActive(item.href)}
                <a
                    href={item.href}
                    title={collapsed ? item.name : ''}
                    class="flex items-center rounded-lg text-sm font-medium transition-colors
                        {collapsed ? 'justify-center px-0 py-2.5' : 'gap-3 px-3 py-2'}
                        {active ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800'}"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {#if item.icon === 'grid'}
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        {:else if item.icon === 'package'}
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        {:else if item.icon === 'tag'}
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        {:else if item.icon === 'truck'}
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                        {:else if item.icon === 'ruler'}
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        {:else if item.icon === 'activity'}
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        {:else if item.icon === 'warehouse'}
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        {:else if item.icon === 'transfer'}
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        {:else if item.icon === 'users'}
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        {/if}
                    </svg>
                    {#if !collapsed}
                        <span class="truncate">{item.name}</span>
                    {/if}
                </a>
            {/each}
        </nav>

        <!-- User -->
        <div class="px-2 py-3 border-t border-slate-800">
            {#if !collapsed}
                <div class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition-colors cursor-pointer">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-semibold text-white flex-shrink-0">
                        {$page.props.auth?.user?.name?.[0]?.toUpperCase() ?? 'U'}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-slate-200 truncate">{$page.props.auth?.user?.name ?? 'Usuario'}</p>
                        <p class="text-xs text-slate-500 truncate">{$page.props.auth?.user?.email ?? ''}</p>
                    </div>
                </div>
                <form onsubmit={logout} class="mt-1">
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-slate-100 hover:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Cerrar sesión
                    </button>
                </form>
            {:else}
                <!-- Collapsed: avatar + logout icon -->
                <div class="flex flex-col items-center gap-1">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-semibold text-white"
                        title={$page.props.auth?.user?.name ?? 'Usuario'}>
                        {$page.props.auth?.user?.name?.[0]?.toUpperCase() ?? 'U'}
                    </div>
                    <form onsubmit={logout}>
                        <button type="submit" title="Cerrar sesión"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-red-400 hover:bg-slate-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            {/if}
        </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        {@render children()}
    </div>
</div>

<Toaster
    theme="dark"
    position="bottom-right"
    richColors={true}
    closeButton={true}
    duration={4000}
/>
