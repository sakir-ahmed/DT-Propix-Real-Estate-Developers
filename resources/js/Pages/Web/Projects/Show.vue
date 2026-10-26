<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import Breadcrumbs from '@/components/website/Breadcrumbs.vue';
import BrochureForm from '@/components/website/BrochureForm.vue';
import CtaBand from '@/components/website/CtaBand.vue';
import EnquiryForm from '@/components/website/EnquiryForm.vue';
import ImagePlaceholder from '@/components/website/ImagePlaceholder.vue';
import ProjectCard from '@/components/website/ProjectCard.vue';
import SectionHeading from '@/components/website/SectionHeading.vue';
import { formatBdt, formatMonthYear, localizeDigits } from '@/lib/format';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

const props = defineProps({
    project: { type: Object, required: true },
    related: { type: Array, default: () => [] },
    breadcrumbs: { type: Array, default: () => [] },
});

const page = usePage();
const { t, locale } = useI18n();
const site = computed(() => page.props.site ?? {});

const facts = computed(() => [
    { key: 'type', icon: 'building', label: t('website.fact_type'), value: props.project.type_label },
    { key: 'status', icon: 'clipboard-check', label: t('website.fact_status'), value: props.project.status_label },
    { key: 'location', icon: 'location-dot', label: t('website.fact_location'), value: props.project.location },
    { key: 'land', icon: 'ruler-combined', label: t('website.fact_land_area'), value: props.project.land_area },
    { key: 'floors', icon: 'layer-group', label: t('website.fact_floors'), value: props.project.floors_count ? localizeDigits(props.project.floors_count, locale()) : null },
    { key: 'units', icon: 'door-open', label: t('website.fact_units'), value: props.project.total_units ? localizeDigits(props.project.total_units, locale()) : null },
    { key: 'launch', icon: 'calendar-days', label: t('website.fact_launch'), value: props.project.launch_date ? formatMonthYear(props.project.launch_date, locale()) : null },
    { key: 'handover', icon: 'key', label: t('website.fact_handover'), value: props.project.handover_date ? formatMonthYear(props.project.handover_date, locale()) : null },
].filter((fact) => fact.value));

const mapSrc = computed(() => {
    if (!props.project.map) {
        return null;
    }

    const { lat, lng } = props.project.map;
    const bbox = [lng - 0.012, lat - 0.007, lng + 0.012, lat + 0.007].join('%2C');

    return `https://www.openstreetmap.org/export/embed.html?bbox=${bbox}&layer=mapnik&marker=${lat}%2C${lng}`;
});

const directionsHref = computed(() => (props.project.map ? `https://www.google.com/maps/dir/?api=1&destination=${props.project.map.lat},${props.project.map.lng}` : null));

function facingLabel(value) {
    return value ? value.charAt(0).toUpperCase() + value.slice(1) : '';
}
</script>

<template>
    <section class="relative bg-foreground text-white">
        <div class="absolute inset-0 overflow-hidden">
            <ImagePlaceholder :src="project.cover_image" :alt="project.name" sizes="100vw" eager />
            <div class="absolute inset-0 bg-gradient-to-t from-foreground via-foreground/70 to-foreground/30" aria-hidden="true"></div>
        </div>
        <div class="site-container relative flex min-h-[420px] flex-col justify-end py-10 sm:min-h-[520px]">
            <Breadcrumbs :items="breadcrumbs" light />
            <div class="mt-6 flex flex-wrap gap-2">
                <span class="rounded-md bg-accent px-2.5 py-1 text-xs font-semibold uppercase tracking-wider text-accent-foreground">{{ project.status_label }}</span>
                <span class="rounded-md bg-white/15 px-2.5 py-1 text-xs font-semibold uppercase tracking-wider">{{ project.type_label }}</span>
            </div>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight sm:text-5xl">{{ project.name }}</h1>
            <p v-if="project.tagline" class="mt-3 max-w-2xl text-lg text-white/85">{{ project.tagline }}</p>
            <p v-if="project.address || project.location" class="mt-3 inline-flex items-center gap-2 text-white/80">
                <AppIcon name="location-dot" />
                {{ project.address ?? project.location }}
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#enquiry" class="btn-light">
                    <AppIcon name="comments" />
                    {{ t('website.enquire_now') }}
                </a>
                <a v-if="project.has_brochure" href="#brochure" class="btn-outline-light">
                    <AppIcon name="file-pdf" />
                    {{ t('website.download_brochure') }}
                </a>
                <a v-if="site.mobile_tel" :href="`tel:${site.mobile_tel}`" class="btn-outline-light">
                    <AppIcon name="phone" />
                    {{ site.mobile }}
                </a>
            </div>
        </div>
    </section>

    <section class="border-b border-border bg-surface">
        <dl class="site-container grid grid-cols-2 gap-x-6 gap-y-5 py-6 sm:grid-cols-4 lg:grid-cols-8">
            <div v-for="fact in facts" :key="fact.key" class="flex items-start gap-2">
                <AppIcon :name="fact.icon" class="mt-1 text-primary" />
                <div>
                    <dt class="text-xs uppercase tracking-wider text-muted">{{ fact.label }}</dt>
                    <dd class="text-sm font-semibold">{{ fact.value }}</dd>
                </div>
            </div>
        </dl>
    </section>

    <div class="site-container grid gap-10 py-12 lg:grid-cols-12 lg:py-16">
        <div class="space-y-14 lg:col-span-8">
            <section v-if="project.description" aria-labelledby="overview">
                <h2 id="overview" class="text-2xl font-semibold sm:text-3xl">{{ t('website.overview') }}</h2>
                <div class="prose-site mt-4 whitespace-pre-line">{{ project.description }}</div>
                <div v-if="project.starting_price" class="mt-6 inline-flex flex-col rounded-lg border border-border bg-background px-5 py-3">
                    <span class="text-xs uppercase tracking-wider text-muted">{{ t('website.starting_from') }}</span>
                    <span class="text-2xl font-semibold">{{ formatBdt(project.starting_price, locale()) }}</span>
                </div>
            </section>

            <section v-if="project.highlights?.length" aria-labelledby="highlights">
                <h2 id="highlights" class="text-2xl font-semibold sm:text-3xl">{{ t('website.highlights') }}</h2>
                <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                    <li v-for="(highlight, index) in project.highlights" :key="index" class="flex items-start gap-3 rounded-lg border border-border bg-surface p-4 text-[15px]">
                        <AppIcon name="circle-check" class="mt-1 size-4 text-success" />
                        <span>{{ highlight }}</span>
                    </li>
                </ul>
            </section>

            <section v-if="project.specifications?.length" aria-labelledby="specifications">
                <h2 id="specifications" class="text-2xl font-semibold sm:text-3xl">{{ t('website.specifications') }}</h2>
                <div class="mt-5 overflow-hidden rounded-xl border border-border">
                    <table class="w-full text-[15px]">
                        <tbody>
                            <tr v-for="(spec, index) in project.specifications" :key="index" class="border-b border-border last:border-0 odd:bg-background">
                                <th scope="row" class="w-1/3 px-4 py-3 text-left font-semibold">{{ spec.label }}</th>
                                <td class="px-4 py-3 text-foreground/85">{{ spec.value }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section v-if="project.amenities?.length" aria-labelledby="amenities">
                <h2 id="amenities" class="text-2xl font-semibold sm:text-3xl">{{ t('website.amenities') }}</h2>
                <ul class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <li v-for="(amenity, index) in project.amenities" :key="index" class="flex items-center gap-3 rounded-lg border border-border bg-surface p-4 text-[15px]">
                        <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <AppIcon :name="amenity.icon ?? 'check'" />
                        </span>
                        <span>{{ amenity.label }}</span>
                    </li>
                </ul>
            </section>

            <section v-if="project.floor_plans?.length" aria-labelledby="floor-plans">
                <h2 id="floor-plans" class="text-2xl font-semibold sm:text-3xl">{{ t('website.floor_plans') }}</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <figure v-for="(plan, index) in project.floor_plans" :key="index" class="overflow-hidden rounded-xl border border-border bg-surface">
                        <img :src="plan.path" :alt="plan.alt" loading="lazy" decoding="async" class="aspect-[4/3] w-full object-contain bg-background" />
                        <figcaption v-if="plan.caption" class="px-4 py-3 text-sm text-muted">{{ plan.caption }}</figcaption>
                    </figure>
                </div>
            </section>

            <section v-if="project.gallery?.length" aria-labelledby="gallery">
                <h2 id="gallery" class="text-2xl font-semibold sm:text-3xl">{{ t('website.gallery') }}</h2>
                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <figure v-for="(image, index) in project.gallery" :key="index" class="aspect-[4/3] overflow-hidden rounded-lg">
                        <img :src="image.path" :alt="image.alt" loading="lazy" decoding="async" class="h-full w-full object-cover" />
                    </figure>
                </div>
            </section>

            <section v-if="project.video_url" aria-labelledby="video">
                <h2 id="video" class="text-2xl font-semibold sm:text-3xl">{{ t('website.video') }}</h2>
                <div class="mt-5 aspect-video overflow-hidden rounded-xl border border-border bg-foreground">
                    <iframe :src="project.video_url" :title="t('website.video')" loading="lazy" allow="accelerometer; encrypted-media; picture-in-picture" allowfullscreen class="h-full w-full"></iframe>
                </div>
            </section>

            <section v-if="mapSrc || project.nearby?.length" aria-labelledby="location">
                <h2 id="location" class="text-2xl font-semibold sm:text-3xl">{{ t('website.location_title') }}</h2>
                <p v-if="project.address" class="mt-2 inline-flex items-center gap-2 text-[15px] text-muted">
                    <AppIcon name="location-dot" />
                    {{ project.address }}
                </p>
                <div v-if="mapSrc" class="mt-5 overflow-hidden rounded-xl border border-border">
                    <iframe :src="mapSrc" :title="t('website.map_title')" loading="lazy" class="h-80 w-full"></iframe>
                </div>
                <p v-if="mapSrc" class="mt-2 text-xs text-muted">{{ t('website.map_note') }}</p>
                <a v-if="directionsHref" :href="directionsHref" rel="noopener" target="_blank" class="btn-secondary mt-4">
                    <AppIcon name="map-location-dot" />
                    {{ t('website.get_directions') }}
                </a>
                <div v-if="project.nearby?.length" class="mt-8">
                    <h3 class="text-xl font-semibold">{{ t('website.nearby') }}</h3>
                    <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                        <li v-for="(place, index) in project.nearby" :key="index" class="flex items-center justify-between gap-3 rounded-lg border border-border bg-surface px-4 py-3 text-[15px]">
                            <span class="inline-flex items-center gap-3">
                                <AppIcon :name="place.icon ?? 'location-dot'" class="text-primary" />
                                {{ place.label }}
                            </span>
                            <span v-if="place.distance" class="text-sm text-muted">{{ place.distance }}</span>
                        </li>
                    </ul>
                </div>
            </section>

            <section aria-labelledby="availability">
                <h2 id="availability" class="text-2xl font-semibold sm:text-3xl">{{ t('website.availability') }}</h2>
                <p class="mt-2 text-[15px] text-muted">{{ t('website.availability_note') }}</p>
                <p v-if="project.show_sold_units" class="text-[15px] text-muted">{{ t('website.availability_sold_note') }}</p>
                <div v-if="project.units?.length" class="mt-5 overflow-x-auto rounded-xl border border-border">
                    <table class="w-full min-w-[720px] text-[15px]">
                        <thead class="bg-background text-left text-xs font-semibold uppercase tracking-wider text-muted">
                            <tr>
                                <th class="px-4 py-3">{{ t('website.unit') }}</th>
                                <th class="px-4 py-3">{{ t('website.block') }}</th>
                                <th class="px-4 py-3">{{ t('website.unit_type') }}</th>
                                <th class="px-4 py-3 text-right">{{ t('website.size') }}</th>
                                <th class="px-4 py-3 text-center">{{ t('website.bed_bath') }}</th>
                                <th class="px-4 py-3">{{ t('website.facing') }}</th>
                                <th class="px-4 py-3 text-center">{{ t('website.parking') }}</th>
                                <th class="px-4 py-3 text-right">{{ t('website.price') }}</th>
                                <th class="px-4 py-3">{{ t('website.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="unit in project.units" :key="unit.id" class="border-t border-border" :class="unit.status === 'sold' ? 'text-muted' : ''">
                                <td class="px-4 py-3 font-semibold">{{ unit.number }}</td>
                                <td class="px-4 py-3">{{ unit.block ?? '-' }}</td>
                                <td class="px-4 py-3 capitalize">{{ unit.type }}</td>
                                <td class="px-4 py-3 text-right">{{ t('website.sqft_single', { size: localizeDigits(unit.size_sqft, locale()) }) }}</td>
                                <td class="px-4 py-3 text-center">{{ localizeDigits(unit.bedrooms ?? '-', locale()) }} / {{ localizeDigits(unit.bathrooms ?? '-', locale()) }}</td>
                                <td class="px-4 py-3">{{ facingLabel(unit.facing) }}</td>
                                <td class="px-4 py-3 text-center">{{ localizeDigits(unit.parking_count, locale()) }}</td>
                                <td class="px-4 py-3 text-right font-semibold">
                                    <template v-if="unit.price">{{ formatBdt(unit.price, locale()) }}</template>
                                    <template v-else-if="unit.status === 'available'">{{ t('website.on_request') }}</template>
                                    <template v-else>-</template>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-semibold uppercase tracking-wider" :class="unit.status === 'available' ? 'bg-success/15 text-success' : 'bg-border text-muted'">
                                        <span class="font-mono">{{ unit.status === 'available' ? 'A' : 'S' }}</span>
                                        {{ unit.status === 'available' ? t('website.available') : t('website.sold') }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="mt-5 rounded-xl border border-border bg-background px-6 py-10 text-center text-[15px] text-muted">
                    {{ project.total_units > 0 ? t('website.sold_out') : t('website.price_on_request') }}
                </div>
                <div class="mt-6 rounded-xl border border-border bg-background p-5">
                    <h3 class="inline-flex items-center gap-2 text-lg font-semibold"><AppIcon name="percent" class="text-primary" />{{ t('website.payment_plan_title') }}</h3>
                    <p class="mt-2 text-[15px] leading-6 text-muted">{{ t('website.payment_plan_text') }}</p>
                </div>
            </section>
        </div>

        <aside class="space-y-6 lg:col-span-4">
            <div id="enquiry" class="lg:sticky lg:top-24 space-y-6">
                <EnquiryForm :project-id="project.id" :show-project="false" :title="t('website.enquire_about', { name: project.name })" />
                <div id="brochure">
                    <BrochureForm v-if="project.has_brochure" :project-id="project.id" />
                </div>
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
                    <Link :href="`${site.nav?.find((item) => item.key === 'contact')?.href ?? '/'}?kind=site_visit`" class="btn-secondary">
                        <AppIcon name="calendar-days" />
                        {{ t('website.request_site_visit') }}
                    </Link>
                    <Link :href="`${site.nav?.find((item) => item.key === 'contact')?.href ?? '/'}?kind=callback`" class="btn-secondary">
                        <AppIcon name="phone" />
                        {{ t('website.request_callback') }}
                    </Link>
                </div>
            </div>
        </aside>
    </div>

    <section v-if="related.length" class="bg-background py-16">
        <div class="site-container">
            <SectionHeading :title="t('website.related_projects')" />
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <ProjectCard v-for="item in related" :key="item.id" :project="item" />
            </div>
        </div>
    </section>

    <CtaBand />
</template>
