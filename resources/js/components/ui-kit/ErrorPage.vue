<script setup>
import { useI18n } from '@/lib/i18n';
import AppIcon from './AppIcon.vue';

defineProps({
    code: { type: [String, Number], default: 403 },
});

const { t } = useI18n();
</script>

<template>
    <div class="mx-auto max-w-md py-16 text-center">
        <AppIcon name="lock" />
        <p class="mt-2 text-lg font-semibold">{{ code }}</p>
        <p class="mt-1 text-[13px] text-muted">{{ t('ui.no_permission') }}</p>
    </div>
</template>
