<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import { useI18n } from '@/lib/i18n';

defineProps({
    title: { type: String, default: '' },
    text: { type: String, default: '' },
});

const page = usePage();
const { t } = useI18n();
const site = computed(() => page.props.site ?? {});
const contactHref = computed(() => site.value.nav?.find((item) => item.key === 'contact')?.href ?? '/');
</script>

<template>
    <section class="relative overflow-hidden bg-primary text-primary-foreground">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.18),transparent_55%)]" aria-hidden="true"></div>
        <div class="site-container relative flex flex-col gap-6 py-14 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl">{{ title || t('website.cta_title') }}</h2>
                <p class="mt-3 text-base leading-7 text-white/85 sm:text-lg">{{ text || t('website.cta_subtitle') }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <Link :href="contactHref" class="btn-light">
                    <AppIcon name="comments" />
                    {{ t('website.cta_button') }}
                </Link>
                <a v-if="site.mobile_tel" :href="`tel:${site.mobile_tel}`" class="btn-outline-light">
                    <AppIcon name="phone" />
                    {{ site.mobile }}
                </a>
            </div>
        </div>
    </section>
</template>
