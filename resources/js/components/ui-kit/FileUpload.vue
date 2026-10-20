<script setup>
import { useI18n } from '@/lib/i18n';
import AppIcon from './AppIcon.vue';

const model = defineModel({ type: String, default: '' });
const { t } = useI18n();

function onChange(event) {
    const file = event.target.files?.[0];
    model.value = file ? file.name : '';
}
</script>

<template>
    <label class="flex h-7 cursor-pointer items-center gap-2 rounded-md border border-dashed border-border px-2 text-[13px]">
        <AppIcon name="download" />
        <span class="truncate">{{ model || t('ui.search') }}</span>
        <input type="file" class="sr-only" @change="onChange" />
    </label>
</template>
