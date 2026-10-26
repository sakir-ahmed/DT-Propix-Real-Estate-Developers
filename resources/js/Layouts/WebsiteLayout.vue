<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import CookieConsent from '@/components/website/CookieConsent.vue';
import SiteFooter from '@/components/website/SiteFooter.vue';
import SiteHeader from '@/components/website/SiteHeader.vue';
import { useI18n } from '@/lib/i18n';

const page = usePage();
const { t } = useI18n();

const site = computed(() => page.props.site ?? {});
const seo = computed(() => page.props.seo ?? {});
</script>

<template>
    <div class="flex min-h-screen flex-col bg-surface text-foreground" :class="site.locale === 'bn' ? 'leading-relaxed' : ''">
        <Head :title="seo.title" />
        <a
            href="#main"
            class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-primary focus:px-4 focus:py-2 focus:text-primary-foreground"
        >
            {{ t('website.skip_to_content') }}
        </a>
        <SiteHeader :site="site" />
        <main id="main" class="flex-1">
            <slot />
        </main>
        <SiteFooter :site="site" />
        <CookieConsent v-if="site.analytics_id" :analytics-id="site.analytics_id" />
    </div>
</template>
