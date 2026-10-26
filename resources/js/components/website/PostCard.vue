<script setup>
import { Link } from '@inertiajs/vue3';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import ImagePlaceholder from '@/components/website/ImagePlaceholder.vue';
import { formatLongDate, localizeDigits } from '@/lib/format';
import { useI18n } from '@/lib/i18n';

defineProps({
    post: { type: Object, required: true },
});

const { t, locale } = useI18n();
</script>

<template>
    <article class="group flex h-full flex-col overflow-hidden rounded-xl border border-border bg-surface shadow-sm transition-shadow hover:shadow-md">
        <Link :href="post.url" class="block aspect-[16/9] overflow-hidden" tabindex="-1" aria-hidden="true">
            <ImagePlaceholder :src="post.cover_image" :alt="post.title" />
        </Link>
        <div class="flex flex-1 flex-col p-5">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted">
                <span class="rounded bg-background px-2 py-0.5 font-semibold uppercase tracking-wider text-primary">{{ post.category_label }}</span>
                <time v-if="post.published_at" :datetime="post.published_at">{{ formatLongDate(post.published_at, locale()) }}</time>
                <span class="inline-flex items-center gap-1"><AppIcon name="clock" />{{ t('website.min_read', { minutes: localizeDigits(post.reading_minutes, locale()) }) }}</span>
            </div>
            <h3 class="mt-3 text-xl font-semibold leading-snug">
                <Link :href="post.url" class="hover:text-primary">{{ post.title }}</Link>
            </h3>
            <p class="mt-2 line-clamp-3 text-[15px] leading-6 text-muted">{{ post.excerpt }}</p>
            <Link :href="post.url" class="mt-auto inline-flex items-center gap-1 pt-4 text-sm font-semibold text-primary group-hover:underline">
                {{ t('website.read_more') }}
                <AppIcon name="arrow-right" />
            </Link>
        </div>
    </article>
</template>
