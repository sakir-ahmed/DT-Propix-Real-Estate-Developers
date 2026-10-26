<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import CtaBand from '@/components/website/CtaBand.vue';
import EnquiryForm from '@/components/website/EnquiryForm.vue';
import FaqAccordion from '@/components/website/FaqAccordion.vue';
import PostCard from '@/components/website/PostCard.vue';
import ProjectCard from '@/components/website/ProjectCard.vue';
import SectionHeading from '@/components/website/SectionHeading.vue';
import StatsBand from '@/components/website/StatsBand.vue';
import TestimonialCard from '@/components/website/TestimonialCard.vue';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

const props = defineProps({
    banners: { type: Array, default: () => [] },
    featured: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    testimonials: { type: Array, default: () => [] },
    posts: { type: Array, default: () => [] },
    faqs: { type: Array, default: () => [] },
    locations: { type: Array, default: () => [] },
});

const page = usePage();
const { t } = useI18n();
const site = computed(() => page.props.site ?? {});
const nav = (key) => site.value.nav?.find((item) => item.key === key)?.href ?? '/';

const slide = ref(0);
const banner = computed(() => props.banners[slide.value] ?? null);

const reasons = [
    { icon: 'money-bill-wave', key: 1 },
    { icon: 'scale-balanced', key: 2 },
    { icon: 'calendar-days', key: 3 },
    { icon: 'helmet-safety', key: 4 },
    { icon: 'percent', key: 5 },
    { icon: 'headset', key: 6 },
];

const steps = [
    { icon: 'comments', key: 1 },
    { icon: 'map-location-dot', key: 2 },
    { icon: 'clipboard-check', key: 3 },
    { icon: 'file-signature', key: 4 },
    { icon: 'cash-register', key: 5 },
    { icon: 'key', key: 6 },
];
</script>

<template>
    <section class="relative overflow-hidden bg-foreground text-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(56,168,255,0.4),transparent_55%)]" aria-hidden="true"></div>
        <svg class="absolute inset-x-0 bottom-0 w-full text-white/10" viewBox="0 0 1200 160" preserveAspectRatio="none" aria-hidden="true">
            <path fill="currentColor" d="M0 160V90h60V40h40v50h60V20h70v70h50V60h90V30h60v60h70V15h80v75h60V55h70v35h80V45h60v45h70V25h60v65h50V70h70v90z" />
        </svg>
        <div class="site-container relative grid gap-10 py-16 sm:py-24 lg:grid-cols-12 lg:items-center">
            <div class="lg:col-span-7">
                <p class="text-sm font-semibold uppercase tracking-wider text-accent">{{ t('website.hero_eyebrow') }}</p>
                <h1 class="mt-4 text-4xl font-semibold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                    {{ banner?.title ?? t('website.hero_title') }}
                </h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-white/80">
                    {{ banner?.subtitle ?? t('website.hero_subtitle') }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link :href="banner?.cta_url ?? nav('projects')" class="btn-light">
                        {{ banner?.cta_label ?? t('website.hero_cta_projects') }}
                        <AppIcon name="arrow-right" />
                    </Link>
                    <Link :href="`${nav('contact')}?kind=site_visit`" class="btn-outline-light">
                        <AppIcon name="calendar-days" />
                        {{ t('website.hero_cta_visit') }}
                    </Link>
                </div>
                <div v-if="banners.length > 1" class="mt-6 flex gap-2" role="tablist">
                    <button
                        v-for="(item, index) in banners"
                        :key="item.id"
                        type="button"
                        role="tab"
                        class="size-3 rounded-full"
                        :class="index === slide ? 'bg-accent' : 'bg-white/40'"
                        :aria-selected="index === slide"
                        :aria-label="item.title"
                        @click="slide = index"
                    ></button>
                </div>
            </div>
            <div class="lg:col-span-5">
                <ul class="space-y-4 rounded-2xl border border-white/15 bg-white/5 p-6 backdrop-blur">
                    <li v-for="point in [1, 2, 3]" :key="point" class="flex items-start gap-3 text-[15px] leading-6">
                        <span class="mt-0.5 inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-accent text-accent-foreground">
                            <AppIcon name="check" class="size-3" />
                        </span>
                        <span>{{ t(`website.hero_point_${point}`) }}</span>
                    </li>
                    <li class="border-t border-white/15 pt-4">
                        <StatsBand :stats="site.stats" dark />
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <section v-if="featured.length" class="site-container py-16 sm:py-20">
        <SectionHeading :eyebrow="t('website.nav_projects')" :title="t('website.featured_title')" :subtitle="t('website.featured_subtitle')">
            <Link :href="nav('projects')" class="btn-secondary">
                {{ t('website.view_all_projects') }}
                <AppIcon name="arrow-right" />
            </Link>
        </SectionHeading>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <ProjectCard v-for="project in featured" :key="project.id" :project="project" />
        </div>
    </section>

    <section class="bg-background py-16 sm:py-20">
        <div class="site-container">
            <SectionHeading :title="t('website.why_title')" :subtitle="t('website.why_subtitle')" align="center" />
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <article v-for="reason in reasons" :key="reason.key" class="rounded-xl border border-border bg-surface p-6">
                    <span class="inline-flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <AppIcon :name="reason.icon" class="size-5" />
                    </span>
                    <h3 class="mt-4 text-xl font-semibold">{{ t(`website.why_${reason.key}_title`) }}</h3>
                    <p class="mt-2 text-[15px] leading-6 text-muted">{{ t(`website.why_${reason.key}_text`) }}</p>
                </article>
            </div>
        </div>
    </section>

    <section v-if="services.length" class="site-container py-16 sm:py-20">
        <SectionHeading :eyebrow="t('website.nav_services')" :title="t('website.services_section_title')" :subtitle="t('website.services_section_subtitle')">
            <Link :href="nav('services')" class="btn-secondary">
                {{ t('website.view_all_services') }}
                <AppIcon name="arrow-right" />
            </Link>
        </SectionHeading>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link v-for="service in services" :key="service.id" :href="service.url" class="group rounded-xl border border-border bg-surface p-6 transition-colors hover:border-primary">
                <span class="inline-flex size-12 items-center justify-center rounded-lg bg-accent/15 text-primary">
                    <AppIcon :name="service.icon" class="size-5" />
                </span>
                <h3 class="mt-4 text-xl font-semibold group-hover:text-primary">{{ service.title }}</h3>
                <p class="mt-2 text-[15px] leading-6 text-muted">{{ service.summary }}</p>
                <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary">
                    {{ t('website.learn_more') }}
                    <AppIcon name="arrow-right" />
                </span>
            </Link>
        </div>
    </section>

    <section class="bg-foreground py-16 text-white sm:py-20">
        <div class="site-container">
            <SectionHeading :title="t('website.process_title')" :subtitle="t('website.process_subtitle')" align="center" />
            <ol class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <li v-for="step in steps" :key="step.key" class="relative rounded-xl border border-white/15 bg-white/5 p-6">
                    <span class="absolute -top-4 left-6 inline-flex size-8 items-center justify-center rounded-full bg-accent font-display text-sm font-bold text-accent-foreground">{{ step.key }}</span>
                    <AppIcon :name="step.icon" class="size-6 text-accent" />
                    <h3 class="mt-4 text-xl font-semibold">{{ t(`website.process_${step.key}_title`) }}</h3>
                    <p class="mt-2 text-[15px] leading-6 text-white/75">{{ t(`website.process_${step.key}_text`) }}</p>
                </li>
            </ol>
        </div>
    </section>

    <section v-if="testimonials.length" class="site-container py-16 sm:py-20">
        <SectionHeading :eyebrow="t('website.nav_testimonials')" :title="t('website.testimonials_section_title')" :subtitle="t('website.testimonials_section_subtitle')">
            <Link :href="site.footer?.company?.[2]?.href ?? '/'" class="btn-secondary">
                {{ t('website.view_all_testimonials') }}
                <AppIcon name="arrow-right" />
            </Link>
        </SectionHeading>
        <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <TestimonialCard v-for="item in testimonials" :key="item.id" :testimonial="item" />
        </div>
    </section>

    <section v-if="posts.length" class="bg-background py-16 sm:py-20">
        <div class="site-container">
            <SectionHeading :eyebrow="t('website.nav_news')" :title="t('website.news_section_title')" :subtitle="t('website.news_section_subtitle')">
                <Link :href="nav('news')" class="btn-secondary">
                    {{ t('website.view_all_news') }}
                    <AppIcon name="arrow-right" />
                </Link>
            </SectionHeading>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                <PostCard v-for="post in posts" :key="post.id" :post="post" />
            </div>
        </div>
    </section>

    <section class="site-container grid gap-10 py-16 sm:py-20 lg:grid-cols-12">
        <div class="lg:col-span-5">
            <SectionHeading :eyebrow="t('website.nav_faq')" :title="t('website.faq_section_title')" :subtitle="t('website.faq_section_subtitle')" />
            <Link :href="site.footer?.explore?.[6]?.href ?? '/'" class="btn-secondary mt-6">
                {{ t('website.view_all_faq') }}
                <AppIcon name="arrow-right" />
            </Link>
            <div v-if="locations.length" class="mt-12">
                <h3 class="text-2xl font-semibold">{{ t('website.locations_title') }}</h3>
                <p class="mt-2 text-[15px] text-muted">{{ t('website.locations_subtitle') }}</p>
                <ul class="mt-4 flex flex-wrap gap-2">
                    <li v-for="location in locations" :key="location">
                        <Link :href="`${nav('projects')}?location=${encodeURIComponent(location)}`" class="inline-flex min-h-11 items-center gap-1.5 rounded-full border border-border px-4 text-sm font-medium hover:border-primary hover:text-primary">
                            <AppIcon name="location-dot" />
                            {{ location }}
                        </Link>
                    </li>
                </ul>
            </div>
        </div>
        <div class="lg:col-span-7">
            <FaqAccordion :items="faqs" />
        </div>
    </section>

    <section class="bg-background py-16 sm:py-20">
        <div class="site-container grid gap-10 lg:grid-cols-12 lg:items-start">
            <div class="lg:col-span-5">
                <SectionHeading :eyebrow="t('website.nav_contact')" :title="t('website.enquiry_title')" :subtitle="t('website.enquiry_subtitle')" />
                <ul class="mt-8 space-y-4 text-[15px]">
                    <li v-if="site.mobile_tel" class="flex items-center gap-3">
                        <span class="inline-flex size-11 items-center justify-center rounded-lg bg-primary/10 text-primary"><AppIcon name="phone" /></span>
                        <a :href="`tel:${site.mobile_tel}`" class="font-semibold hover:text-primary">{{ site.mobile }}</a>
                    </li>
                    <li v-if="site.email" class="flex items-center gap-3">
                        <span class="inline-flex size-11 items-center justify-center rounded-lg bg-primary/10 text-primary"><AppIcon name="envelope" /></span>
                        <a :href="`mailto:${site.email}`" class="font-semibold hover:text-primary">{{ site.email }}</a>
                    </li>
                    <li v-if="site.address" class="flex items-start gap-3">
                        <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"><AppIcon name="location-dot" /></span>
                        <span class="pt-2.5">{{ site.address }}</span>
                    </li>
                </ul>
            </div>
            <div class="lg:col-span-7">
                <EnquiryForm show-kind />
            </div>
        </div>
    </section>

    <CtaBand />
</template>
