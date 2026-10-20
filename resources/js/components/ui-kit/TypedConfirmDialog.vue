<script setup>
import { ref } from 'vue';
import { DialogContent, DialogDescription, DialogOverlay, DialogPortal, DialogRoot, DialogTitle } from 'reka-ui';
import { useI18n } from '@/lib/i18n';

const props = defineProps({
    phrase: { type: String, default: 'DELETE' },
});

const open = defineModel('open', { type: Boolean, default: false });
const typed = ref('');
const emit = defineEmits(['confirm']);
const { t } = useI18n();

function submit() {
    if (typed.value !== props.phrase) {
        return;
    }

    emit('confirm');
    open.value = false;
    typed.value = '';
}
</script>

<template>
    <DialogRoot v-model:open="open">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 z-40 bg-foreground/40" />
            <DialogContent class="fixed left-1/2 top-1/2 z-50 w-[min(100%,24rem)] -translate-x-1/2 -translate-y-1/2 rounded-xl border border-border bg-surface p-4">
                <DialogTitle class="text-base font-semibold">{{ t('ui.confirm') }}</DialogTitle>
                <DialogDescription class="mt-1 text-[13px] text-muted">{{ t('ui.type_to_confirm', { phrase }) }}</DialogDescription>
                <input v-model="typed" class="mt-3 h-7 w-full rounded-md border border-border px-2 text-[13px]" />
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" class="h-7 rounded-md border border-border px-2.5 text-[13px]" @click="open = false">{{ t('ui.cancel') }}</button>
                    <button type="button" class="h-7 rounded-md bg-danger px-2.5 text-[13px] text-white" :disabled="typed !== phrase" @click="submit">{{ t('ui.confirm') }}</button>
                </div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
