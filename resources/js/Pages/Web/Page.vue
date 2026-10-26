<script setup>
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import CtaBand from '@/components/website/CtaBand.vue';
import PageHero from '@/components/website/PageHero.vue';
import { formatLongDate } from '@/lib/format';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

defineProps({
    page: { type: Object, required: true },
    breadcrumbs: { type: Array, default: () => [] },
});

const { t, locale } = useI18n();
</script>

<template>
    <PageHero :title="page.title" :subtitle="page.excerpt ?? ''" :breadcrumbs="breadcrumbs" />

    <article class="site-container max-w-3xl py-16">
        <div class="prose-site" v-html="page.body"></div>
        <p v-if="page.updated_at" class="mt-10 border-t border-border pt-4 text-sm text-muted">
            {{ t('website.last_updated', { date: formatLongDate(page.updated_at, locale()) }) }}
        </p>
    </article>

    <CtaBand />
</template>
