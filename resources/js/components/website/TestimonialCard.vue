<script setup>
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import { localizeDigits } from '@/lib/format';
import { useI18n } from '@/lib/i18n';

defineProps({
    testimonial: { type: Object, required: true },
});

const { t, locale } = useI18n();
</script>

<template>
    <figure class="flex h-full flex-col rounded-xl border border-border bg-surface p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <AppIcon name="quote-left" class="size-6 text-accent" />
            <span class="inline-flex items-center gap-0.5 text-warning" :aria-label="t('website.rating_label', { rating: localizeDigits(testimonial.rating, locale()) })">
                <AppIcon v-for="star in testimonial.rating" :key="star" name="star" />
            </span>
        </div>
        <blockquote class="mt-4 flex-1 text-[15px] leading-7 text-foreground/90">
            <p>{{ testimonial.quote }}</p>
        </blockquote>
        <figcaption class="mt-5 flex items-center gap-3 border-t border-border pt-4">
            <img v-if="testimonial.photo" :src="testimonial.photo" :alt="testimonial.name" class="size-11 rounded-full object-cover" loading="lazy" width="44" height="44" />
            <span v-else class="inline-flex size-11 items-center justify-center rounded-full bg-primary/10 text-base font-semibold text-primary" aria-hidden="true">
                {{ testimonial.name.charAt(0) }}
            </span>
            <div>
                <p class="font-semibold">{{ testimonial.name }}</p>
                <p class="text-sm text-muted">
                    <template v-if="testimonial.project">{{ t('website.customer_of', { project: testimonial.project }) }}</template>
                    <template v-else>{{ testimonial.designation }}</template>
                </p>
            </div>
        </figcaption>
    </figure>
</template>
