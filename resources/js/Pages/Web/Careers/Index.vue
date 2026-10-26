<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import CtaBand from '@/components/website/CtaBand.vue';
import PageHero from '@/components/website/PageHero.vue';
import SectionHeading from '@/components/website/SectionHeading.vue';
import { formatLongDate, localizeDigits } from '@/lib/format';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

defineProps({
    jobs: { type: Array, default: () => [] },
    breadcrumbs: { type: Array, default: () => [] },
});

const page = usePage();
const { t, locale } = useI18n();
const site = computed(() => page.props.site ?? {});
const reasons = [
    { icon: 'building', key: 1 },
    { icon: 'chart-line', key: 2 },
    { icon: 'hand-holding-dollar', key: 3 },
    { icon: 'handshake', key: 4 },
];
</script>

<template>
    <PageHero :title="t('website.careers_title')" :subtitle="t('website.careers_intro')" :eyebrow="t('website.nav_careers')" :breadcrumbs="breadcrumbs" />

    <section class="site-container py-16">
        <SectionHeading :title="t('website.careers_why_title')" align="center" />
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <article v-for="reason in reasons" :key="reason.key" class="rounded-xl border border-border bg-surface p-6">
                <span class="inline-flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <AppIcon :name="reason.icon" class="size-5" />
                </span>
                <h3 class="mt-4 text-lg font-semibold">{{ t(`website.careers_why_${reason.key}_title`) }}</h3>
                <p class="mt-2 text-[15px] leading-6 text-muted">{{ t(`website.careers_why_${reason.key}_text`) }}</p>
            </article>
        </div>
    </section>

    <section class="bg-background py-16">
        <div class="site-container">
            <SectionHeading :title="t('website.open_positions')" />
            <ul v-if="jobs.length" class="mt-8 space-y-4">
                <li v-for="job in jobs" :key="job.id">
                    <Link :href="job.url" class="group flex flex-col gap-4 rounded-xl border border-border bg-surface p-6 transition-colors hover:border-primary sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary">{{ job.department_label }}</p>
                            <h3 class="mt-1 text-xl font-semibold group-hover:text-primary">{{ job.title }}</h3>
                            <p class="mt-2 line-clamp-2 text-[15px] text-muted">{{ job.summary }}</p>
                            <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-muted">
                                <li class="inline-flex items-center gap-1.5"><AppIcon name="location-dot" />{{ job.location }}</li>
                                <li class="inline-flex items-center gap-1.5"><AppIcon name="briefcase" />{{ job.employment_type_label }}</li>
                                <li v-if="job.experience" class="inline-flex items-center gap-1.5"><AppIcon name="user-tie" />{{ job.experience }}</li>
                                <li v-if="job.deadline" class="inline-flex items-center gap-1.5"><AppIcon name="calendar-days" />{{ t('website.deadline', { date: formatLongDate(job.deadline, locale()) }) }}</li>
                                <li v-if="job.vacancies" class="inline-flex items-center gap-1.5"><AppIcon name="users" />{{ t('website.vacancies', { count: localizeDigits(job.vacancies, locale()) }) }}</li>
                            </ul>
                        </div>
                        <span class="btn-primary shrink-0">
                            {{ t('website.apply_now') }}
                            <AppIcon name="arrow-right" />
                        </span>
                    </Link>
                </li>
            </ul>
            <div v-else class="mt-8 rounded-xl border border-border bg-surface px-6 py-16 text-center">
                <AppIcon name="briefcase" class="mx-auto size-8 text-muted" />
                <h3 class="mt-4 text-xl font-semibold">{{ t('website.no_jobs') }}</h3>
                <p class="mt-2 text-[15px] text-muted">{{ t('website.no_jobs_hint') }}</p>
                <a v-if="site.email" :href="`mailto:${site.email}`" class="btn-secondary mt-6">
                    <AppIcon name="envelope" />
                    {{ site.email }}
                </a>
            </div>
        </div>
    </section>

    <CtaBand />
</template>
