<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import Breadcrumbs from '@/components/website/Breadcrumbs.vue';
import CtaBand from '@/components/website/CtaBand.vue';
import ImagePlaceholder from '@/components/website/ImagePlaceholder.vue';
import PostCard from '@/components/website/PostCard.vue';
import SectionHeading from '@/components/website/SectionHeading.vue';
import { formatLongDate, localizeDigits } from '@/lib/format';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

defineProps({
    post: { type: Object, required: true },
    related: { type: Array, default: () => [] },
    breadcrumbs: { type: Array, default: () => [] },
});

const page = usePage();
const { t, locale } = useI18n();
const copied = ref(false);

const shareUrl = computed(() => page.props.seo?.canonical ?? '');
const encodedUrl = computed(() => encodeURIComponent(shareUrl.value));
const newsHref = computed(() => page.props.site?.nav?.find((item) => item.key === 'news')?.href ?? '/');

async function copyLink() {
    if (typeof navigator === 'undefined' || !navigator.clipboard) {
        return;
    }

    await navigator.clipboard.writeText(shareUrl.value);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
}
</script>

<template>
    <article>
        <header class="bg-background">
            <div class="site-container max-w-4xl py-12">
                <Breadcrumbs :items="breadcrumbs" />
                <div class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-muted">
                    <span class="rounded bg-primary/10 px-2 py-0.5 font-semibold uppercase tracking-wider text-primary">{{ post.category_label }}</span>
                    <time v-if="post.published_at" :datetime="post.published_at">{{ formatLongDate(post.published_at, locale()) }}</time>
                    <span class="inline-flex items-center gap-1"><AppIcon name="clock" />{{ t('website.min_read', { minutes: localizeDigits(post.reading_minutes, locale()) }) }}</span>
                    <span v-if="post.author">{{ t('website.by_author', { author: post.author }) }}</span>
                </div>
                <h1 class="mt-4 text-3xl font-semibold tracking-tight sm:text-5xl">{{ post.title }}</h1>
                <p class="mt-4 text-lg leading-8 text-muted">{{ post.excerpt }}</p>
            </div>
        </header>

        <div class="site-container max-w-4xl">
            <figure class="-mt-6 aspect-[21/9] overflow-hidden rounded-xl border border-border shadow-sm">
                <ImagePlaceholder :src="post.cover_image" :alt="post.title" sizes="(min-width: 1024px) 896px, 100vw" eager />
            </figure>
        </div>

        <div class="site-container grid max-w-4xl gap-10 py-12 lg:grid-cols-12">
            <div class="prose-site lg:col-span-9" v-html="post.body"></div>
            <aside class="lg:col-span-3">
                <div class="lg:sticky lg:top-24">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">{{ t('website.share') }}</p>
                    <div class="mt-3 flex flex-wrap gap-2 lg:flex-col">
                        <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`" rel="noopener" target="_blank" class="btn-secondary min-h-10 justify-start px-3 text-sm">
                            <AppIcon name="share-nodes" />{{ t('website.share_facebook') }}
                        </a>
                        <a :href="`https://wa.me/?text=${encodedUrl}`" rel="noopener" target="_blank" class="btn-secondary min-h-10 justify-start px-3 text-sm">
                            <AppIcon name="comments" />{{ t('website.share_whatsapp') }}
                        </a>
                        <a :href="`https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`" rel="noopener" target="_blank" class="btn-secondary min-h-10 justify-start px-3 text-sm">
                            <AppIcon name="briefcase" />{{ t('website.share_linkedin') }}
                        </a>
                        <button type="button" class="btn-secondary min-h-10 justify-start px-3 text-sm" @click="copyLink">
                            <AppIcon name="link" />{{ copied ? t('website.link_copied') : t('website.copy_link') }}
                        </button>
                    </div>
                    <div v-if="post.tags?.length" class="mt-8">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted">{{ t('website.tags') }}</p>
                        <ul class="mt-3 flex flex-wrap gap-2">
                            <li v-for="tag in post.tags" :key="tag" class="rounded-full border border-border px-3 py-1 text-xs">{{ tag }}</li>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </article>

    <section v-if="related.length" class="bg-background py-16">
        <div class="site-container">
            <SectionHeading :title="t('website.related_posts')">
                <Link :href="newsHref" class="btn-secondary">
                    {{ t('website.back_to_news') }}
                    <AppIcon name="arrow-right" />
                </Link>
            </SectionHeading>
            <div class="mt-8 grid gap-6 md:grid-cols-3">
                <PostCard v-for="item in related" :key="item.id" :post="item" />
            </div>
        </div>
    </section>

    <CtaBand />
</template>
