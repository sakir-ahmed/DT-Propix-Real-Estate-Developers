<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap justify-center gap-1" aria-label="Pagination">
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md border px-3 text-sm font-medium"
                :class="link.active ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-surface hover:border-primary hover:text-primary'"
                :aria-current="link.active ? 'page' : undefined"
                preserve-scroll
                v-html="link.label"
            />
            <span v-else class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md border border-border px-3 text-sm text-muted/60" v-html="link.label" />
        </template>
    </nav>
</template>
