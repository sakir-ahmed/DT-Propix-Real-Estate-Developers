<script setup>
import MoneyText from './MoneyText.vue';

defineProps({
    lines: { type: Array, default: () => [] },
});
</script>

<template>
    <dl class="rounded-lg border border-border bg-surface text-[13px]">
        <div v-for="line in lines" :key="line.label" class="flex items-center justify-between border-b border-border px-3 py-1.5 last:border-0">
            <dt>{{ line.label }}</dt>
            <dd class="text-right"><MoneyText :amount="line.amount" /></dd>
        </div>
    </dl>
</template>
