<script setup>
import { Link } from '@inertiajs/vue3';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import ImagePlaceholder from '@/components/website/ImagePlaceholder.vue';
import { formatBdt, formatMonthYear, localizeDigits } from '@/lib/format';
import { useI18n } from '@/lib/i18n';

defineProps({
    project: { type: Object, required: true },
});

const { t, locale } = useI18n();

const statusClasses = {
    ongoing: 'bg-success text-white',
    upcoming: 'bg-warning text-foreground',
    completed: 'bg-primary text-primary-foreground',
};

function bedrooms(project) {
    if (!project.bedrooms) {
        return null;
    }

    return project.bedrooms.min === project.bedrooms.max
        ? t('website.bedrooms_single', { count: localizeDigits(project.bedrooms.min, locale()) })
        : t('website.bedrooms_range', { min: localizeDigits(project.bedrooms.min, locale()), max: localizeDigits(project.bedrooms.max, locale()) });
}

function sizes(project) {
    if (!project.sizes) {
        return null;
    }

    return project.sizes.min === project.sizes.max
        ? t('website.sqft_single', { size: localizeDigits(project.sizes.min, locale()) })
        : t('website.sqft_range', { min: localizeDigits(project.sizes.min, locale()), max: localizeDigits(project.sizes.max, locale()) });
}
</script>

<template>
    <article class="group flex h-full flex-col overflow-hidden rounded-xl border border-border bg-surface shadow-sm transition-shadow hover:shadow-md">
        <Link :href="project.url" class="relative block aspect-[4/3] overflow-hidden" tabindex="-1" aria-hidden="true">
            <ImagePlaceholder :src="project.cover_image" :alt="project.name" />
            <div class="absolute left-3 top-3 flex gap-2">
                <span class="rounded-md px-2 py-1 text-xs font-semibold uppercase tracking-wider" :class="statusClasses[project.status] ?? 'bg-muted text-white'">
                    {{ project.status_label }}
                </span>
                <span v-if="project.is_featured" class="rounded-md bg-accent px-2 py-1 text-xs font-semibold uppercase tracking-wider text-accent-foreground">
                    {{ t('website.featured_badge') }}
                </span>
            </div>
        </Link>
        <div class="flex flex-1 flex-col p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-primary">{{ project.type_label }}</p>
            <h3 class="mt-1 text-xl font-semibold leading-snug">
                <Link :href="project.url" class="hover:text-primary">{{ project.name }}</Link>
            </h3>
            <p v-if="project.location" class="mt-1.5 inline-flex items-center gap-1.5 text-sm text-muted">
                <AppIcon name="location-dot" />
                {{ project.location }}
            </p>
            <p v-if="project.tagline" class="mt-3 line-clamp-2 text-[15px] leading-6 text-muted">{{ project.tagline }}</p>

            <ul class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-sm text-foreground/80">
                <li v-if="bedrooms(project)" class="inline-flex items-center gap-1.5"><AppIcon name="bed" />{{ bedrooms(project) }}</li>
                <li v-if="sizes(project)" class="inline-flex items-center gap-1.5"><AppIcon name="ruler-combined" />{{ sizes(project) }}</li>
                <li v-if="project.handover_date && project.status !== 'completed'" class="inline-flex items-center gap-1.5">
                    <AppIcon name="calendar-days" />{{ t('website.handover', { date: formatMonthYear(project.handover_date, locale()) }) }}
                </li>
            </ul>

            <div class="mt-auto flex items-end justify-between gap-3 border-t border-border pt-4">
                <div>
                    <template v-if="project.starting_price">
                        <p class="text-xs text-muted">{{ t('website.starting_from') }}</p>
                        <p class="text-lg font-semibold">{{ formatBdt(project.starting_price, locale()) }}</p>
                    </template>
                    <p v-else-if="project.available_units === 0 && project.total_units > 0" class="text-sm font-semibold text-muted">{{ t('website.sold_out') }}</p>
                    <p v-else class="text-sm font-semibold text-muted">{{ t('website.price_on_request') }}</p>
                </div>
                <div class="text-right">
                    <p v-if="project.available_units > 0" class="text-xs font-semibold text-success">
                        {{ t('website.available_units', { count: localizeDigits(project.available_units, locale()) }) }}
                    </p>
                    <Link :href="project.url" class="mt-1 inline-flex items-center gap-1 text-sm font-semibold text-primary group-hover:underline">
                        {{ t('website.view_project') }}
                        <AppIcon name="arrow-right" />
                    </Link>
                </div>
            </div>
        </div>
    </article>
</template>
