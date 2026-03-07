<script>
    let { children } = $props();
</script>

<div class="min-h-screen bg-slate-950 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-8">
            <div class="w-12 h-12 rounded-xl bg-indigo-500 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Veltory</h1>
            <p class="text-sm text-slate-400 mt-1">Sistema de gestión de inventario</p>
        </div>

        {@render children()}
    </div>
</div>
