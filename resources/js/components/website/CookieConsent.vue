<script setup>
import { onMounted, ref } from 'vue';
import { useI18n } from '@/lib/i18n';

const props = defineProps({
    analyticsId: { type: String, required: true },
});

const { t } = useI18n();
const visible = ref(false);
const storageKey = 'analytics-consent';

function loadAnalytics() {
    if (typeof document === 'undefined' || document.getElementById('site-analytics')) {
        return;
    }

    const script = document.createElement('script');
    script.id = 'site-analytics';
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(props.analyticsId)}`;
    document.head.appendChild(script);

    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() {
        window.dataLayer.push(arguments);
    };
    window.gtag('js', new Date());
    window.gtag('config', props.analyticsId, { anonymize_ip: true });
}

function decide(granted) {
    localStorage.setItem(storageKey, granted ? 'granted' : 'denied');
    visible.value = false;

    if (granted) {
        loadAnalytics();
    }
}

onMounted(() => {
    const choice = localStorage.getItem(storageKey);

    if (choice === 'granted') {
        loadAnalytics();
    } else if (choice === null) {
        visible.value = true;
    }
});
</script>

<template>
    <div
        v-if="visible"
        role="dialog"
        aria-live="polite"
        :aria-label="t('website.cookie_title')"
        class="fixed inset-x-4 bottom-4 z-50 mx-auto max-w-xl rounded-lg border border-border bg-surface p-4 shadow-lg"
    >
        <p class="font-semibold">{{ t('website.cookie_title') }}</p>
        <p class="mt-1 text-sm text-muted">{{ t('website.cookie_text') }}</p>
        <div class="mt-3 flex flex-wrap gap-2">
            <button type="button" class="btn-primary" @click="decide(true)">{{ t('website.cookie_accept') }}</button>
            <button type="button" class="btn-secondary" @click="decide(false)">{{ t('website.cookie_decline') }}</button>
        </div>
    </div>
</template>
