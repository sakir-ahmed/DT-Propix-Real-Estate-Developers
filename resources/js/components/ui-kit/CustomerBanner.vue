<script setup>
import { useI18n } from '@/lib/i18n';
import MoneyText from './MoneyText.vue';
import StatusBadge from './StatusBadge.vue';

defineProps({
    name: { type: String, required: true },
    code: { type: String, required: true },
    project: { type: String, required: true },
    unit: { type: String, required: true },
    status: { type: String, required: true },
    outstanding: { type: [Number, String], default: 0 },
});

const { t } = useI18n();
</script>

<template>
    <aside class="flex flex-wrap items-center gap-x-4 gap-y-1 rounded-lg border border-border bg-surface px-3 py-2 text-[13px]">
        <span class="font-semibold">{{ name }}</span>
        <span class="text-muted">{{ code }}</span>
        <span>{{ project }}</span>
        <span>{{ t('ui.unit') }} {{ unit }}</span>
        <StatusBadge :label="status" tone="info" icon="circle-info" />
        <span class="ml-auto">{{ t('ui.outstanding') }} <MoneyText :amount="outstanding" /></span>
    </aside>
</template>
