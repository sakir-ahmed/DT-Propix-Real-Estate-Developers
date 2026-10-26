<script setup>
defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    align: { type: String, default: 'left' },
    tag: { type: String, default: 'h2' },
});
</script>

<template>
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between" :class="align === 'center' ? 'md:flex-col md:items-center md:text-center' : ''">
        <div class="max-w-2xl">
            <p v-if="eyebrow" class="text-sm font-semibold uppercase tracking-wider text-primary">{{ eyebrow }}</p>
            <component :is="tag" class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">{{ title }}</component>
            <p v-if="subtitle" class="mt-3 text-base leading-7 text-muted sm:text-lg">{{ subtitle }}</p>
        </div>
        <div v-if="$slots.default" class="shrink-0">
            <slot />
        </div>
    </div>
</template>
