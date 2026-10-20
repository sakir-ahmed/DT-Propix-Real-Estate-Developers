<script setup>
import { computed } from 'vue';
import { formatMoney } from '@/lib/format';
import { useI18n } from '@/lib/i18n';

const props = defineProps({
    amount: { type: [Number, String], required: true },
});

const { locale } = useI18n();
const text = computed(() => formatMoney(props.amount, locale()));
</script>

<template>
    <span class="tabular-nums">{{ text }}</span>
</template>
