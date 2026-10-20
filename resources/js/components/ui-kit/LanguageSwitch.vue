<script setup>
import { router } from '@inertiajs/vue3';
import { useI18n } from '@/lib/i18n';

const { t, locale } = useI18n();

function setLocale(next) {
    router.get(`/locale/${next}`, {}, { preserveState: true, preserveScroll: true });
}
</script>

<template>
    <div class="inline-flex h-7 overflow-hidden rounded-md border border-border" :aria-label="t('ui.language')">
        <button
            type="button"
            class="px-2 text-[13px]"
            :class="locale() === 'en' ? 'bg-primary text-primary-foreground' : 'bg-surface'"
            @click="setLocale('en')"
        >
            EN
        </button>
        <button
            type="button"
            class="px-2 text-[13px]"
            :class="locale() === 'bn' ? 'bg-primary text-primary-foreground' : 'bg-surface'"
            @click="setLocale('bn')"
        >
            BN
        </button>
    </div>
</template>
