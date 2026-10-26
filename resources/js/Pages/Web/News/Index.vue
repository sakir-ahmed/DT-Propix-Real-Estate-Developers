<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import CtaBand from '@/components/website/CtaBand.vue';
import PageHero from '@/components/website/PageHero.vue';
import Pagination from '@/components/website/Pagination.vue';
import PostCard from '@/components/website/PostCard.vue';
import { localizeDigits } from '@/lib/format';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

defineProps({
    posts: { type: Object, required: true },
    category: { type: String, default: null },
    categories: { type: Array, default: () => [] },
    category_counts: { type: Object, default: () => ({}) },
    breadcrumbs: { type: Array, default: () => [] },
});

const page = usePage();
const { t, locale } = useI18n();
const newsHref = computed(() => page.props.site?.nav?.find((item) => item.key === 'news')?.href ?? '/');
</script>

<template>
    <PageHero :title="t('website.news_title')" :subtitle="t('website.news_intro')" :eyebrow="t('website.nav_news')" :breadcrumbs="breadcrumbs" />

    <section class="site-container py-12">
        <nav class="flex flex-wrap gap-2" :aria-label="t('website.all_categories')">
            <Link :href="newsHref" class="rounded-full border px-4 py-2 text-sm font-semibold" :class="!category ? 'border-primary bg-primary text-primary-foreground' : 'border-border hover:border-primary hover:text-primary'">
                {{ t('website.all_categories') }}
            </Link>
            <Link
                v-for="item in categories"
                :key="item"
                :href="`${newsHref}?category=${item}`"
                class="rounded-full border px-4 py-2 text-sm font-semibold"
                :class="category === item ? 'border-primary bg-primary text-primary-foreground' : 'border-border hover:border-primary hover:text-primary'"
            >
                {{ t(`website.post_category_${item}`) }}
                <span v-if="category_counts[item]" class="opacity-70">({{ localizeDigits(category_counts[item], locale()) }})</span>
            </Link>
        </nav>

        <div v-if="posts.data?.length" class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <PostCard v-for="post in posts.data" :key="post.id" :post="post" />
        </div>
        <div v-else class="mt-8 rounded-xl border border-border bg-surface px-6 py-16 text-center">
            <AppIcon name="circle-info" class="mx-auto size-8 text-muted" />
            <h2 class="mt-4 text-xl font-semibold">{{ t('website.no_posts') }}</h2>
            <p class="mt-2 text-[15px] text-muted">{{ t('website.no_posts_hint') }}</p>
        </div>

        <div class="mt-10">
            <Pagination :links="posts.links ?? []" />
        </div>
    </section>

    <CtaBand />
</template>
