<script setup>
import { computed, ref } from 'vue';
import { DialogContent, DialogOverlay, DialogPortal, DialogRoot, DialogTitle } from 'reka-ui';
import { useI18n } from '@/lib/i18n';

const props = defineProps({
    commands: { type: Array, default: () => [] },
});

const open = defineModel('open', { type: Boolean, default: false });
const query = ref('');
const { t } = useI18n();

const matches = computed(() => props.commands.filter((command) => command.toLowerCase().includes(query.value.toLowerCase())));
</script>

<template>
    <div>
        <button type="button" class="h-7 rounded-md border border-border px-2.5 text-[13px]" @click="open = true">
            {{ t('ui.command_palette') }}
        </button>
        <DialogRoot v-model:open="open">
            <DialogPortal>
                <DialogOverlay class="fixed inset-0 z-40 bg-foreground/40" />
                <DialogContent class="fixed left-1/2 top-24 z-50 w-[min(100%,28rem)] -translate-x-1/2 rounded-xl border border-border bg-surface p-3">
                    <DialogTitle class="sr-only">{{ t('ui.command_palette') }}</DialogTitle>
                    <input v-model="query" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" :placeholder="t('ui.search')" />
                    <ul class="mt-2">
                        <li v-for="command in matches" :key="command" class="rounded px-2 py-1 text-[13px]">{{ command }}</li>
                    </ul>
                </DialogContent>
            </DialogPortal>
        </DialogRoot>
    </div>
</template>
