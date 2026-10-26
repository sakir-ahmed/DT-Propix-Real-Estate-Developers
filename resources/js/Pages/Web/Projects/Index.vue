<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import CtaBand from '@/components/website/CtaBand.vue';
import PageHero from '@/components/website/PageHero.vue';
import Pagination from '@/components/website/Pagination.vue';
import ProjectCard from '@/components/website/ProjectCard.vue';
import { localizeDigits } from '@/lib/format';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

const props = defineProps({
    projects: { type: Object, required: true },
    locations: { type: Array, default: () => [] },
    status_counts: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
    budgets: { type: Array, default: () => [] },
    types: { type: Array, default: () => [] },
    statuses: { type: Array, default: () => [] },
    breadcrumbs: { type: Array, default: () => [] },
});

const page = usePage();
const { t, locale } = useI18n();
const site = computed(() => page.props.site ?? {});
const projectsHref = computed(() => site.value.nav?.find((item) => item.key === 'projects')?.href ?? '/');

const form = reactive({
    status: props.filters.status ?? '',
    type: props.filters.type ?? '',
    location: props.filters.location ?? '',
    budget: props.filters.budget ?? '',
});

const hasFilters = computed(() => Object.values(form).some((value) => value !== ''));
const total = computed(() => props.projects.total ?? 0);

function apply() {
    const query = Object.fromEntries(Object.entries(form).filter(([, value]) => value !== ''));
    router.get(projectsHref.value, query, { preserveScroll: true, preserveState: true });
}

function clear() {
    form.status = '';
    form.type = '';
    form.location = '';
    form.budget = '';
    router.get(projectsHref.value, {}, { preserveScroll: true });
}

function statusHref(status) {
    return status ? `${projectsHref.value}?status=${status}` : projectsHref.value;
}
</script>

<template>
    <PageHero :title="t('website.projects_title')" :subtitle="t('website.projects_intro_text')" :eyebrow="t('website.projects_intro_title')" :breadcrumbs="breadcrumbs">
        <div class="flex flex-wrap gap-2">
            <Link :href="statusHref('')" class="rounded-full border px-4 py-2 text-sm font-semibold" :class="!filters.status ? 'border-accent bg-accent text-accent-foreground' : 'border-white/30 hover:bg-white/10'">
                {{ t('website.all') }}
            </Link>
            <Link
                v-for="status in statuses"
                :key="status"
                :href="statusHref(status)"
                class="rounded-full border px-4 py-2 text-sm font-semibold"
                :class="filters.status === status ? 'border-accent bg-accent text-accent-foreground' : 'border-white/30 hover:bg-white/10'"
            >
                {{ t(`website.status_${status}`) }}
                <span v-if="status_counts[status]" class="opacity-75">({{ localizeDigits(status_counts[status], locale()) }})</span>
            </Link>
        </div>
    </PageHero>

    <section class="site-container py-10 sm:py-14">
        <form class="grid gap-3 rounded-xl border border-border bg-background p-4 sm:grid-cols-2 lg:grid-cols-5" @submit.prevent="apply">
            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ t('website.filter_status') }}</span>
                <select v-model="form.status" class="site-input mt-1">
                    <option value="">{{ t('website.all') }}</option>
                    <option v-for="status in statuses" :key="status" :value="status">{{ t(`website.status_${status}`) }}</option>
                </select>
            </label>
            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ t('website.filter_type') }}</span>
                <select v-model="form.type" class="site-input mt-1">
                    <option value="">{{ t('website.all') }}</option>
                    <option v-for="type in types" :key="type" :value="type">{{ t(`website.type_${type}`) }}</option>
                </select>
            </label>
            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ t('website.filter_location') }}</span>
                <select v-model="form.location" class="site-input mt-1">
                    <option value="">{{ t('website.all') }}</option>
                    <option v-for="location in locations" :key="location" :value="location">{{ location }}</option>
                </select>
            </label>
            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ t('website.filter_budget') }}</span>
                <select v-model="form.budget" class="site-input mt-1">
                    <option value="">{{ t('website.all') }}</option>
                    <option v-for="budget in budgets" :key="budget" :value="budget">{{ t(`website.budget_${budget}`) }}</option>
                </select>
            </label>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1">
                    <AppIcon name="filter" />
                    {{ t('website.apply_filters') }}
                </button>
                <button v-if="hasFilters" type="button" class="btn-secondary" :aria-label="t('website.clear_filters')" @click="clear">
                    <AppIcon name="xmark" />
                </button>
            </div>
        </form>

        <div class="mt-8 flex items-center justify-between">
            <p class="text-sm font-semibold text-muted" aria-live="polite">
                {{ total === 1 ? t('website.results_count_one') : t('website.results_count', { count: localizeDigits(total, locale()) }) }}
            </p>
        </div>

        <div v-if="projects.data?.length" class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <ProjectCard v-for="project in projects.data" :key="project.id" :project="project" />
        </div>
        <div v-else class="mt-6 rounded-xl border border-border bg-surface px-6 py-16 text-center">
            <AppIcon name="building" class="mx-auto size-8 text-muted" />
            <h2 class="mt-4 text-xl font-semibold">{{ t('website.no_projects') }}</h2>
            <p class="mt-2 text-[15px] text-muted">{{ t('website.no_projects_hint') }}</p>
            <button type="button" class="btn-secondary mt-6" @click="clear">{{ t('website.clear_filters') }}</button>
        </div>

        <div class="mt-10">
            <Pagination :links="projects.links ?? []" />
        </div>
    </section>

    <section class="bg-background py-14">
        <div class="site-container prose-site max-w-3xl">
            <h2>{{ t('website.projects_intro_title') }}</h2>
            <p>{{ t('website.projects_intro_text') }}</p>
            <p>{{ t('website.payment_plan_text') }}</p>
            <p>{{ t('website.availability_note') }}</p>
        </div>
    </section>

    <CtaBand />
</template>
