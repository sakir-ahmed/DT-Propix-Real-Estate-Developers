<script setup>
import { onMounted } from 'vue';
import { useI18n } from '@/lib/i18n';
import { useTheme } from '@/composables/useTheme';
import AppIcon from './AppIcon.vue';

const { t } = useI18n();
const { dark, init, toggle } = useTheme();

onMounted(init);
</script>

<template>
    <button
        type="button"
        class="inline-flex size-7 items-center justify-center rounded-md border border-border bg-surface"
        :aria-label="dark ? t('ui.theme_light') : t('ui.theme_dark')"
        @click="toggle"
    >
        <AppIcon :name="dark ? 'sun' : 'moon'" />
    </button>
</template>
