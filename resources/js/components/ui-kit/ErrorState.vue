<script setup>
import { useI18n } from '@/lib/i18n';
import AppIcon from './AppIcon.vue';

defineEmits(['retry']);

const { t } = useI18n();
</script>

<template>
    <div class="rounded-lg border border-danger/30 bg-surface px-4 py-6 text-center" role="alert">
        <AppIcon name="triangle-exclamation" />
        <p class="mt-2 text-sm font-semibold">{{ t('ui.error') }}</p>
        <button type="button" class="mt-3 h-7 rounded-md border border-border px-2.5 text-[13px]" @click="$emit('retry')">
            {{ t('ui.retry') }}
        </button>
    </div>
</template>
