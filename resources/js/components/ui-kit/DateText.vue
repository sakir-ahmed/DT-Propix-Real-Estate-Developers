<script setup>
import { computed } from 'vue';
import { formatDate } from '@/lib/format';
import { useI18n } from '@/lib/i18n';

const props = defineProps({
    value: { type: [String, Date], required: true },
});

const { locale } = useI18n();
const text = computed(() => formatDate(props.value, locale()));
</script>

<template>
    <span class="whitespace-nowrap">{{ text }}</span>
</template>
