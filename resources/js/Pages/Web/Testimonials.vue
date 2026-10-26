<script setup>
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import CtaBand from '@/components/website/CtaBand.vue';
import PageHero from '@/components/website/PageHero.vue';
import TestimonialCard from '@/components/website/TestimonialCard.vue';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

defineProps({
    testimonials: { type: Array, default: () => [] },
    breadcrumbs: { type: Array, default: () => [] },
});

const { t } = useI18n();
</script>

<template>
    <PageHero :title="t('website.testimonials_title')" :subtitle="t('website.testimonials_intro')" :eyebrow="t('website.nav_testimonials')" :breadcrumbs="breadcrumbs" />

    <section class="site-container py-16">
        <div v-if="testimonials.length" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <TestimonialCard v-for="item in testimonials" :key="item.id" :testimonial="item" />
        </div>
        <p v-else class="text-center text-[15px] text-muted">{{ t('website.no_testimonials') }}</p>
    </section>

    <CtaBand />
</template>
