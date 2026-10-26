<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import CtaBand from '@/components/website/CtaBand.vue';
import PageHero from '@/components/website/PageHero.vue';
import SectionHeading from '@/components/website/SectionHeading.vue';
import StatsBand from '@/components/website/StatsBand.vue';
import TestimonialCard from '@/components/website/TestimonialCard.vue';
import { localizeDigits } from '@/lib/format';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

defineProps({
    page: { type: Object, default: null },
    milestones: { type: Array, default: () => [] },
    leadership: { type: Array, default: () => [] },
    testimonials: { type: Array, default: () => [] },
    breadcrumbs: { type: Array, default: () => [] },
});

const inertia = usePage();
const { t, locale } = useI18n();
const site = computed(() => inertia.props.site ?? {});
const values = [
    { icon: 'handshake', key: 1 },
    { icon: 'helmet-safety', key: 2 },
    { icon: 'headset', key: 3 },
    { icon: 'seedling', key: 4 },
];
</script>

<template>
    <PageHero :title="page?.title ?? t('website.about_title', { company: site.name })" :subtitle="page?.excerpt ?? ''" :eyebrow="t('website.nav_about')" :breadcrumbs="breadcrumbs" />

    <section class="site-container py-14">
        <StatsBand :stats="site.stats" />
    </section>

    <section v-if="page?.body" class="bg-background py-16">
        <div class="site-container grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <SectionHeading :eyebrow="t('website.nav_about')" :title="t('website.about_story')" tag="h2" />
            </div>
            <div class="prose-site lg:col-span-8" v-html="page.body"></div>
        </div>
    </section>

    <section class="site-container py-16">
        <SectionHeading :title="t('website.about_values_title')" align="center" />
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <article v-for="value in values" :key="value.key" class="rounded-xl border border-border bg-surface p-6">
                <span class="inline-flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <AppIcon :name="value.icon" class="size-5" />
                </span>
                <h3 class="mt-4 text-xl font-semibold">{{ t(`website.about_value_${value.key}_title`) }}</h3>
                <p class="mt-2 text-[15px] leading-6 text-muted">{{ t(`website.about_value_${value.key}_text`) }}</p>
            </article>
        </div>
    </section>

    <section v-if="milestones.length" class="bg-foreground py-16 text-white">
        <div class="site-container">
            <SectionHeading :title="t('website.about_milestones')" align="center" />
            <ol class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <li v-for="(milestone, index) in milestones" :key="index" class="rounded-xl border border-white/15 bg-white/5 p-6">
                    <p class="font-display text-3xl font-bold text-accent">{{ localizeDigits(milestone.year, locale()) }}</p>
                    <p class="mt-3 text-[15px] leading-6 text-white/85">{{ milestone.text }}</p>
                </li>
            </ol>
        </div>
    </section>

    <section v-if="leadership.length" class="site-container py-16">
        <SectionHeading :title="t('website.about_leadership')">
            <Link :href="site.footer?.company?.[1]?.href ?? '/'" class="btn-secondary">
                {{ t('website.meet_the_team') }}
                <AppIcon name="arrow-right" />
            </Link>
        </SectionHeading>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <article v-for="member in leadership" :key="member.id" class="rounded-xl border border-border bg-surface p-6 text-center">
                <img v-if="member.photo" :src="member.photo" :alt="member.name" class="mx-auto size-24 rounded-full object-cover" loading="lazy" width="96" height="96" />
                <span v-else class="mx-auto inline-flex size-24 items-center justify-center rounded-full bg-primary/10 font-display text-2xl font-semibold text-primary" aria-hidden="true">{{ member.name.charAt(0) }}</span>
                <h3 class="mt-4 text-lg font-semibold">{{ member.name }}</h3>
                <p class="text-sm text-primary">{{ member.designation }}</p>
                <p v-if="member.bio" class="mt-3 text-sm leading-6 text-muted">{{ member.bio }}</p>
            </article>
        </div>
    </section>

    <section v-if="testimonials.length" class="bg-background py-16">
        <div class="site-container">
            <SectionHeading :title="t('website.testimonials_section_title')" />
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                <TestimonialCard v-for="item in testimonials" :key="item.id" :testimonial="item" />
            </div>
        </div>
    </section>

    <CtaBand />
</template>
