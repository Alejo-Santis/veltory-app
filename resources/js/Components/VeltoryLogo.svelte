<script>
    let { size = 32, showWordmark = false, class: className = '' } = $props();
</script>

<div class="flex items-center gap-2.5 {className}">
    <!-- Isometric cube mark -->
    <svg
        width={size}
        height={size * 0.9}
        viewBox="0 0 40 36"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        aria-label="Veltory logo"
    >
        <!-- Top face -->
        <polygon
            points="20,0 40,10 20,20 0,10"
            fill="#818cf8"
        />
        <!-- Left face -->
        <polygon
            points="0,10 20,20 20,36 0,26"
            fill="#4f46e5"
        />
        <!-- Right face -->
        <polygon
            points="20,20 40,10 40,26 20,36"
            fill="#6366f1"
        />
        <!-- Edge highlights for depth -->
        <line x1="20" y1="0"  x2="20" y2="20" stroke="white" stroke-width="0.4" stroke-opacity="0.25"/>
        <line x1="0"  y1="10" x2="20" y2="20" stroke="white" stroke-width="0.4" stroke-opacity="0.15"/>
        <line x1="40" y1="10" x2="20" y2="20" stroke="white" stroke-width="0.4" stroke-opacity="0.15"/>
    </svg>

    {#if showWordmark}
        <span
            style="font-size: {size * 0.5625}px; letter-spacing: -0.02em;"
            class="font-bold text-white leading-none select-none"
        >
            Veltory
        </span>
    {/if}
</div>
