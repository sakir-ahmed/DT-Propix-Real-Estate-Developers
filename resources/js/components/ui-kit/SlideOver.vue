<script setup>
import { DialogContent, DialogOverlay, DialogPortal, DialogRoot, DialogTitle } from 'reka-ui';
import { useI18n } from '@/lib/i18n';
import AppIcon from './AppIcon.vue';

defineProps({
    title: { type: String, required: true },
});

const open = defineModel('open', { type: Boolean, default: false });
const { t } = useI18n();
</script>

<template>
    <DialogRoot v-model:open="open">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 z-40 bg-foreground/40" />
            <DialogContent class="fixed inset-y-0 right-0 z-50 flex w-[min(100%,28rem)] flex-col border-l border-border bg-surface p-4">
                <header class="flex h-12 items-center justify-between">
                    <DialogTitle class="text-base font-semibold">{{ title }}</DialogTitle>
                    <button type="button" class="inline-flex size-7 items-center justify-center rounded-md" :aria-label="t('ui.close')" @click="open = false">
                        <AppIcon name="xmark" />
                    </button>
                </header>
                <div class="flex-1 overflow-auto text-[13px]">
                    <slot />
                </div>
                <footer class="flex justify-end gap-2 border-t border-border pt-3">
                    <slot name="footer" />
                </footer>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
