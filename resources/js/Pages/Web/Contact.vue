<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import EnquiryForm from '@/components/website/EnquiryForm.vue';
import PageHero from '@/components/website/PageHero.vue';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

defineProps({
    branches: { type: Array, default: () => [] },
    breadcrumbs: { type: Array, default: () => [] },
});

const page = usePage();
const { t } = useI18n();
const site = computed(() => page.props.site ?? {});

const mapSrc = computed(() => {
    const lat = Number(site.value.map?.lat);
    const lng = Number(site.value.map?.lng);

    if (!lat || !lng) {
        return null;
    }

    const bbox = [lng - 0.012, lat - 0.007, lng + 0.012, lat + 0.007].join('%2C');

    return `https://www.openstreetmap.org/export/embed.html?bbox=${bbox}&layer=mapnik&marker=${lat}%2C${lng}`;
});
</script>

<template>
    <PageHero :title="t('website.contact_title')" :subtitle="t('website.contact_intro')" :eyebrow="t('website.nav_contact')" :breadcrumbs="breadcrumbs" />

    <div class="site-container grid gap-10 py-16 lg:grid-cols-12">
        <section class="space-y-8 lg:col-span-5">
            <div>
                <h2 class="text-2xl font-semibold">{{ t('website.contact_details') }}</h2>
                <ul class="mt-5 space-y-4">
                    <li v-if="site.mobile_tel" class="flex items-start gap-4">
                        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"><AppIcon name="phone" class="size-5" /></span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted">{{ t('website.call_us') }}</p>
                            <a :href="`tel:${site.mobile_tel}`" class="text-lg font-semibold hover:text-primary">{{ site.mobile }}</a>
                            <p v-if="site.office_hours" class="text-sm text-muted">{{ site.office_hours }}</p>
                        </div>
                    </li>
                    <li v-if="site.whatsapp" class="flex items-start gap-4">
                        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-lg bg-success/10 text-success"><AppIcon name="comments" class="size-5" /></span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted">WhatsApp</p>
                            <a :href="site.whatsapp" rel="noopener" target="_blank" class="text-lg font-semibold hover:text-primary">{{ t('website.whatsapp_us') }}</a>
                        </div>
                    </li>
                    <li v-if="site.email" class="flex items-start gap-4">
                        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"><AppIcon name="envelope" class="size-5" /></span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted">{{ t('website.email_us') }}</p>
                            <a :href="`mailto:${site.email}`" class="text-lg font-semibold hover:text-primary">{{ site.email }}</a>
                        </div>
                    </li>
                    <li v-if="site.address" class="flex items-start gap-4">
                        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"><AppIcon name="location-dot" class="size-5" /></span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted">{{ t('website.visit_us') }}</p>
                            <p class="text-[15px] leading-6">{{ site.address }}</p>
                        </div>
                    </li>
                </ul>
            </div>

            <div v-if="branches.length">
                <h2 class="text-2xl font-semibold">{{ t('website.our_branches') }}</h2>
                <ul class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    <li v-for="branch in branches" :key="branch.id" class="rounded-xl border border-border bg-surface p-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary">{{ branch.is_head_office ? t('website.head_office') : t('website.branch') }}</p>
                        <h3 class="mt-1 text-lg font-semibold">{{ branch.name }}</h3>
                        <p v-if="branch.address" class="mt-1 text-[15px] leading-6 text-muted">{{ branch.address }}</p>
                    </li>
                </ul>
            </div>
        </section>

        <section class="lg:col-span-7">
            <EnquiryForm kind="contact" show-kind :title="t('website.contact_form_title')" />
        </section>
    </div>

    <section v-if="mapSrc" class="site-container pb-16">
        <h2 class="text-2xl font-semibold">{{ t('website.map_title') }}</h2>
        <div class="mt-5 overflow-hidden rounded-xl border border-border">
            <iframe :src="mapSrc" :title="t('website.map_title')" loading="lazy" class="h-96 w-full"></iframe>
        </div>
        <p class="mt-2 text-xs text-muted">{{ t('website.map_note') }}</p>
    </section>
</template>
