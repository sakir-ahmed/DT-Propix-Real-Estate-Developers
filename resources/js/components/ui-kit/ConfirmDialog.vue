<script setup>
import { computed } from 'vue';
import { DialogContent, DialogDescription, DialogOverlay, DialogPortal, DialogRoot, DialogTitle } from 'reka-ui';
import { confirmState, useConfirm } from '@/composables/useConfirm';
import { useI18n } from '@/lib/i18n';
import AppIcon from './AppIcon.vue';

const props = defineProps({
    open: { type: Boolean, default: undefined },
    title: { type: String, default: '' },
    message: { type: String, default: '' },
});

const emit = defineEmits(['update:open', 'confirm']);
const { t } = useI18n();
const { settle } = useConfirm();

const isOpen = computed({
    get() {
        return props.open ?? confirmState.open;
    },
    set(value) {
        if (props.open === undefined) {
            confirmState.open = value;

            return;
        }

        emit('update:open', value);
    },
});

const heading = computed(() => props.title || confirmState.title);
const body = computed(() => props.message || confirmState.message);

function accept() {
    if (props.open === undefined) {
        settle(true);
    }

    emit('confirm');
    isOpen.value = false;
}

function dismiss() {
    if (props.open === undefined) {
        settle(false);
    }

    isOpen.value = false;
}
</script>

<template>
    <DialogRoot v-model:open="isOpen">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 z-40 bg-foreground/40" />
            <DialogContent class="fixed left-1/2 top-1/2 z-50 w-[min(100%,24rem)] -translate-x-1/2 -translate-y-1/2 rounded-xl border border-border bg-surface p-4">
                <DialogTitle class="text-base font-semibold">{{ heading }}</DialogTitle>
                <DialogDescription class="mt-1 text-[13px] text-muted">{{ body }}</DialogDescription>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" class="h-7 rounded-md border border-border px-2.5 text-[13px]" @click="dismiss">
                        {{ t('ui.cancel') }}
                    </button>
                    <button type="button" class="inline-flex h-7 items-center gap-1.5 rounded-md bg-danger px-2.5 text-[13px] text-white" @click="accept">
                        <AppIcon name="trash" />
                        {{ t('ui.confirm') }}
                    </button>
                </div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
