<script>
    /** @type {{ items: Array<{label: string, href?: string}> }} */
    let { items = [] } = $props();
</script>

{#if items.length > 0}
    <nav aria-label="Miga de pan" class="flex items-center gap-1.5 text-xs text-slate-500 mb-5">
        <a href="/" class="hover:text-slate-300 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
        </a>

        {#each items as item, i}
            <svg class="w-3 h-3 text-slate-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>

            {#if item.href && i < items.length - 1}
                <a href={item.href} class="hover:text-slate-300 transition-colors truncate max-w-[180px]">
                    {item.label}
                </a>
            {:else}
                <span class="text-slate-300 font-medium truncate max-w-[220px]">{item.label}</span>
            {/if}
        {/each}
    </nav>
{/if}
