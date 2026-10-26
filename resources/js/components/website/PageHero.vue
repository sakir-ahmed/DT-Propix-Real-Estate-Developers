<script setup>
import Breadcrumbs from '@/components/website/Breadcrumbs.vue';

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    eyebrow: { type: String, default: '' },
    breadcrumbs: { type: Array, default: () => [] },
});
</script>

<template>
    <section class="relative overflow-hidden bg-foreground text-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(56,168,255,0.35),transparent_55%)]" aria-hidden="true"></div>
        <div class="site-container relative py-14 sm:py-20">
            <Breadcrumbs :items="breadcrumbs" light />
            <p v-if="eyebrow" class="mt-6 text-sm font-semibold uppercase tracking-wider text-accent">{{ eyebrow }}</p>
            <h1 class="mt-3 max-w-3xl text-3xl font-semibold tracking-tight sm:text-5xl">{{ title }}</h1>
            <p v-if="subtitle" class="mt-4 max-w-2xl text-base leading-7 text-white/80 sm:text-lg">{{ subtitle }}</p>
            <div v-if="$slots.default" class="mt-6">
                <slot />
            </div>
        </div>
    </section>
</template>
