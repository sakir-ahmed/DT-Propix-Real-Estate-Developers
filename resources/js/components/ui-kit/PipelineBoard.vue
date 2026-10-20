<script setup>
defineProps({
    columns: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="grid gap-2 md:grid-cols-3">
        <section v-for="column in columns" :key="column.name" class="rounded-lg border border-border bg-surface p-2">
            <h3 class="mb-2 text-xs font-semibold text-muted">{{ column.name }}</h3>
            <article v-for="card in column.cards" :key="card" class="mb-2 rounded-md border border-border px-2 py-1 text-[13px]">
                {{ card }}
            </article>
        </section>
    </div>
</template>
