<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import CtaBand from '@/components/website/CtaBand.vue';
import FaqAccordion from '@/components/website/FaqAccordion.vue';
import PageHero from '@/components/website/PageHero.vue';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

defineProps({
    groups: { type: Array, default: () => [] },
    breadcrumbs: { type: Array, default: () => [] },
});

const page = usePage();
const { t } = useI18n();
const contactHref = computed(() => page.props.site?.nav?.find((item) => item.key === 'contact')?.href ?? '/');
</script>

<template>
    <PageHero :title="t('website.faq_title')" :subtitle="t('website.faq_intro')" :eyebrow="t('website.nav_faq')" :breadcrumbs="breadcrumbs" />

    <div class="site-container grid gap-10 py-16 lg:grid-cols-12">
        <nav class="lg:col-span-3" aria-label="FAQ categories">
            <ul class="flex flex-wrap gap-2 lg:sticky lg:top-24 lg:flex-col">
                <li v-for="group in groups" :key="group.key">
                    <a :href="`#faq-${group.key}`" class="inline-flex min-h-11 items-center rounded-md border border-border px-4 text-sm font-semibold hover:border-primary hover:text-primary lg:w-full">{{ group.label }}</a>
                </li>
            </ul>
        </nav>
        <div class="space-y-12 lg:col-span-9">
            <section v-for="group in groups" :id="`faq-${group.key}`" :key="group.key" class="scroll-mt-28">
                <h2 class="text-2xl font-semibold">{{ group.label }}</h2>
                <FaqAccordion class="mt-5" :items="group.items" />
            </section>
            <section class="rounded-xl border border-border bg-background p-6 sm:flex sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold">{{ t('website.still_have_questions') }}</h2>
                    <p class="mt-1 text-[15px] text-muted">{{ t('website.still_have_questions_text') }}</p>
                </div>
                <Link :href="contactHref" class="btn-primary mt-4 sm:mt-0">
                    <AppIcon name="comments" />
                    {{ t('website.contact_us') }}
                </Link>
            </section>
        </div>
    </div>

    <CtaBand />
</template>
