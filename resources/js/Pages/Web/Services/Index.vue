<script setup>
import { Link } from '@inertiajs/vue3';
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import CtaBand from '@/components/website/CtaBand.vue';
import PageHero from '@/components/website/PageHero.vue';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

defineProps({
    services: { type: Array, default: () => [] },
    breadcrumbs: { type: Array, default: () => [] },
});

const { t } = useI18n();
</script>

<template>
    <PageHero :title="t('website.services_title')" :subtitle="t('website.services_intro')" :eyebrow="t('website.nav_services')" :breadcrumbs="breadcrumbs" />

    <section class="site-container py-16">
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <Link v-for="service in services" :key="service.id" :href="service.url" class="group flex flex-col rounded-xl border border-border bg-surface p-6 transition-colors hover:border-primary">
                <span class="inline-flex size-12 items-center justify-center rounded-lg bg-accent/15 text-primary">
                    <AppIcon :name="service.icon" class="size-5" />
                </span>
                <h2 class="mt-4 text-xl font-semibold group-hover:text-primary">{{ service.title }}</h2>
                <p class="mt-2 flex-1 text-[15px] leading-6 text-muted">{{ service.summary }}</p>
                <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary">
                    {{ t('website.learn_more') }}
                    <AppIcon name="arrow-right" />
                </span>
            </Link>
        </div>
    </section>

    <CtaBand :title="t('website.service_cta_title')" :text="t('website.service_cta_text')" />
</template>
