<script setup>
import { useI18n } from '@/lib/i18n';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
    status: { type: String, required: true },
});

const { t } = useI18n();

const map = {
    available: { tone: 'success', icon: 'circle-check' },
    hold: { tone: 'warning', icon: 'clock' },
    booked: { tone: 'info', icon: 'lock' },
    sold: { tone: 'muted', icon: 'circle-check' },
    landowner: { tone: 'info', icon: 'users' },
    blocked: { tone: 'danger', icon: 'circle-xmark' },
};
</script>

<template>
    <StatusBadge
        :label="t(`ui.${props.status}`)"
        :tone="map[props.status]?.tone ?? 'muted'"
        :icon="map[props.status]?.icon ?? 'circle-info'"
    />
</template>
