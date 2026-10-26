<script setup>
import { Link } from '@inertiajs/vue3';
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import CtaBand from '@/components/website/CtaBand.vue';
import EnquiryForm from '@/components/website/EnquiryForm.vue';
import PageHero from '@/components/website/PageHero.vue';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

defineProps({
    service: { type: Object, required: true },
    others: { type: Array, default: () => [] },
    breadcrumbs: { type: Array, default: () => [] },
});

const { t } = useI18n();
</script>

<template>
    <PageHero :title="service.title" :subtitle="service.summary" :eyebrow="t('website.nav_services')" :breadcrumbs="breadcrumbs" />

    <div class="site-container grid gap-10 py-16 lg:grid-cols-12">
        <article class="lg:col-span-8">
            <div class="prose-site" v-html="service.body"></div>

            <section v-if="service.features?.length" class="mt-10">
                <h2 class="text-2xl font-semibold">{{ t('website.service_features') }}</h2>
                <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                    <li v-for="(feature, index) in service.features" :key="index" class="flex items-start gap-3 rounded-lg border border-border bg-surface p-4 text-[15px]">
                        <AppIcon name="circle-check" class="mt-1 size-4 text-success" />
                        <span>{{ feature }}</span>
                    </li>
                </ul>
            </section>
        </article>

        <aside class="space-y-6 lg:col-span-4">
            <EnquiryForm :show-project="false" :title="t('website.talk_to_us')" :subtitle="t('website.service_cta_text')" />
            <nav v-if="others.length" class="rounded-xl border border-border bg-surface p-5" :aria-label="t('website.other_services')">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-muted">{{ t('website.other_services') }}</h2>
                <ul class="mt-3 divide-y divide-border">
                    <li v-for="other in others" :key="other.id">
                        <Link :href="other.url" class="flex min-h-11 items-center gap-3 py-2 text-[15px] font-medium hover:text-primary">
                            <AppIcon :name="other.icon" class="text-primary" />
                            {{ other.title }}
                        </Link>
                    </li>
                </ul>
            </nav>
        </aside>
    </div>

    <CtaBand :title="t('website.service_cta_title')" :text="t('website.service_cta_text')" />
</template>
