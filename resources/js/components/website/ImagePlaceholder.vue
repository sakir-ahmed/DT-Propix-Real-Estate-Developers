<script setup>
defineProps({
    src: { type: String, default: null },
    alt: { type: String, default: '' },
    sizes: { type: String, default: '(min-width: 1024px) 33vw, 100vw' },
    eager: { type: Boolean, default: false },
});
</script>

<template>
    <img
        v-if="src"
        :src="src"
        :alt="alt"
        :sizes="sizes"
        :loading="eager ? 'eager' : 'lazy'"
        decoding="async"
        class="h-full w-full object-cover"
    />
    <div v-else class="relative h-full w-full overflow-hidden bg-gradient-to-br from-primary via-[#0a3e85] to-foreground" role="img" :aria-label="alt">
        <svg class="absolute inset-x-0 bottom-0 w-full text-white/15" viewBox="0 0 400 120" preserveAspectRatio="none" aria-hidden="true">
            <path fill="currentColor" d="M0 120V70h30V40h20v30h25V20h30v50h20V55h35V30h25v40h30V15h30v55h25V45h30v25h30V35h20v35h30v50z" />
        </svg>
        <img :src="'/images/brand/logo-mark.svg'" alt="" class="absolute left-1/2 top-1/2 h-12 w-12 -translate-x-1/2 -translate-y-1/2 opacity-70" width="48" height="48" />
    </div>
</template>
