<script setup>
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import CtaBand from '@/components/website/CtaBand.vue';
import PageHero from '@/components/website/PageHero.vue';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

defineProps({
    groups: { type: Array, default: () => [] },
    breadcrumbs: { type: Array, default: () => [] },
});

const { t } = useI18n();
</script>

<template>
    <PageHero :title="t('website.team_title')" :subtitle="t('website.team_intro')" :eyebrow="t('website.nav_team')" :breadcrumbs="breadcrumbs" />

    <section v-for="group in groups" :key="group.key" class="site-container py-12">
        <h2 class="text-2xl font-semibold sm:text-3xl">{{ group.label }}</h2>
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <article v-for="member in group.members" :key="member.id" class="rounded-xl border border-border bg-surface p-6 text-center">
                <img v-if="member.photo" :src="member.photo" :alt="member.name" class="mx-auto size-24 rounded-full object-cover" loading="lazy" width="96" height="96" />
                <span v-else class="mx-auto inline-flex size-24 items-center justify-center rounded-full bg-primary/10 font-display text-2xl font-semibold text-primary" aria-hidden="true">{{ member.name.charAt(0) }}</span>
                <h3 class="mt-4 text-lg font-semibold">{{ member.name }}</h3>
                <p class="text-sm text-primary">{{ member.designation }}</p>
                <p v-if="member.bio" class="mt-3 text-sm leading-6 text-muted">{{ member.bio }}</p>
                <div class="mt-4 flex justify-center gap-2">
                    <a v-if="member.email" :href="`mailto:${member.email}`" class="inline-flex size-11 items-center justify-center rounded-md border border-border hover:border-primary hover:text-primary" :aria-label="`${t('website.email_us')}: ${member.name}`"><AppIcon name="envelope" /></a>
                    <a v-if="member.phone" :href="`tel:${member.phone}`" class="inline-flex size-11 items-center justify-center rounded-md border border-border hover:border-primary hover:text-primary" :aria-label="`${t('website.call_us')}: ${member.name}`"><AppIcon name="phone" /></a>
                    <a v-if="member.linkedin_url" :href="member.linkedin_url" rel="noopener" target="_blank" class="inline-flex size-11 items-center justify-center rounded-md border border-border hover:border-primary hover:text-primary" :aria-label="`LinkedIn: ${member.name}`"><AppIcon name="arrow-up-right-from-square" /></a>
                </div>
            </article>
        </div>
    </section>

    <section v-if="!groups.length" class="site-container py-16 text-center text-[15px] text-muted">
        {{ t('website.no_team') }}
    </section>

    <CtaBand />
</template>
