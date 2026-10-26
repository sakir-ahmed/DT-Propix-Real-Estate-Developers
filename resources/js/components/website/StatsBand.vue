<script setup>
import { computed } from 'vue';
import { formatNumber } from '@/lib/format';
import { useI18n } from '@/lib/i18n';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    dark: { type: Boolean, default: false },
});

const { t, locale } = useI18n();

const items = computed(() => [
    { key: 'years', label: t('website.stats_years'), value: props.stats.years, suffix: '+' },
    { key: 'projects', label: t('website.stats_projects'), value: props.stats.projects },
    { key: 'completed', label: t('website.stats_completed'), value: props.stats.completed },
    { key: 'ongoing', label: t('website.stats_ongoing'), value: props.stats.ongoing },
    { key: 'units', label: t('website.stats_units'), value: props.stats.units_delivered },
    { key: 'families', label: t('website.stats_families'), value: props.stats.families },
].filter((item) => Number(item.value) > 0));
</script>

<template>
    <dl v-if="items.length" class="grid grid-cols-2 gap-6 sm:grid-cols-3" :class="items.length > 4 ? 'lg:grid-cols-6' : 'lg:grid-cols-4'">
        <div v-for="item in items" :key="item.key" class="text-center">
            <dd class="font-display text-3xl font-bold tracking-tight sm:text-4xl" :class="dark ? 'text-white' : 'text-primary'">
                {{ formatNumber(item.value, locale()) }}{{ item.suffix ?? '' }}
            </dd>
            <dt class="mt-1 text-sm" :class="dark ? 'text-white/75' : 'text-muted'">{{ item.label }}</dt>
        </div>
    </dl>
</template>
