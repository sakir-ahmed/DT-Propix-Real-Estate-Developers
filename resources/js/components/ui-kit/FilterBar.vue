<script setup>
import { useI18n } from '@/lib/i18n';
import AppIcon from './AppIcon.vue';

const query = defineModel('query', { type: String, default: '' });
const { t } = useI18n();
</script>

<template>
    <div class="mb-3 flex flex-wrap items-center gap-2">
        <label class="relative">
            <AppIcon name="magnifying-glass" class="pointer-events-none absolute left-2 top-1.5" />
            <input v-model="query" type="search" class="h-7 rounded-md border border-border bg-surface pr-2 pl-7 text-[13px]" :placeholder="t('ui.search')" />
        </label>
        <slot />
    </div>
</template>
