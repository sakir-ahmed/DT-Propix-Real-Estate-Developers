<script setup>
import { ref } from 'vue';
import { useI18n } from '@/lib/i18n';
import AppIcon from './AppIcon.vue';

defineProps({
    count: { type: Number, default: 0 },
    items: { type: Array, default: () => [] },
});

const open = ref(false);
const { t } = useI18n();
</script>

<template>
    <div class="relative">
        <button type="button" class="relative inline-flex size-7 items-center justify-center rounded-md border border-border" :aria-label="t('ui.notifications')" @click="open = !open">
            <AppIcon name="bell" />
            <span v-if="count" class="absolute -top-1 -right-1 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-danger px-1 text-[10px] text-white">{{ count }}</span>
        </button>
        <ul v-if="open" class="absolute right-0 z-20 mt-1 w-56 rounded-md border border-border bg-surface p-2 text-[13px]">
            <li v-for="item in items" :key="item">{{ item }}</li>
        </ul>
    </div>
</template>
