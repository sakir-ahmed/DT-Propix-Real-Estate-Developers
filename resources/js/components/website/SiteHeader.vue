<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import { useI18n } from '@/lib/i18n';

defineProps({
    site: { type: Object, required: true },
});

const { t } = useI18n();
const page = usePage();
const open = ref(false);

watch(() => page.url, () => {
    open.value = false;
});
</script>

<template>
    <header class="sticky top-0 z-40 border-b border-border bg-surface/95 backdrop-blur">
        <div class="hidden border-b border-border bg-background text-sm text-muted md:block">
            <div class="site-container flex h-9 items-center justify-between gap-4">
                <div class="flex items-center gap-5">
                    <a v-if="site.mobile_tel" :href="`tel:${site.mobile_tel}`" class="inline-flex items-center gap-1.5 hover:text-primary">
                        <AppIcon name="phone" />
                        <span>{{ site.mobile }}</span>
                    </a>
                    <a v-if="site.email" :href="`mailto:${site.email}`" class="inline-flex items-center gap-1.5 hover:text-primary">
                        <AppIcon name="envelope" />
                        <span>{{ site.email }}</span>
                    </a>
                    <span v-if="site.office_hours" class="inline-flex items-center gap-1.5">
                        <AppIcon name="clock" />
                        <span>{{ site.office_hours }}</span>
                    </span>
                </div>
                <nav :aria-label="t('website.language')" class="flex items-center gap-1">
                    <Link
                        v-for="(href, code) in site.alternates"
                        :key="code"
                        :href="href"
                        :hreflang="code"
                        class="rounded px-2 py-0.5 text-xs font-semibold uppercase tracking-wider"
                        :class="site.locale === code ? 'bg-primary text-primary-foreground' : 'hover:text-primary'"
                    >
                        {{ code === 'bn' ? 'বাং' : 'EN' }}
                    </Link>
                </nav>
            </div>
        </div>

        <div class="site-container flex h-16 items-center justify-between gap-4 lg:h-[72px]">
            <Link :href="site.nav?.[0]?.href ?? '/'" class="flex items-center gap-3" :aria-label="site.name">
                <img :src="site.mark" alt="" class="h-10 w-10" width="40" height="40" />
                <span class="font-display text-lg font-semibold leading-tight sm:text-xl">{{ site.name }}</span>
            </Link>

            <nav class="hidden items-center gap-1 lg:flex" :aria-label="t('website.menu')">
                <Link
                    v-for="item in site.nav"
                    :key="item.key"
                    :href="item.href"
                    class="rounded-md px-3 py-2 text-[15px] font-medium transition-colors hover:bg-background hover:text-primary"
                    :class="item.active ? 'text-primary' : 'text-foreground'"
                    :aria-current="item.active ? 'page' : undefined"
                >
                    {{ item.label }}
                </Link>
            </nav>

            <div class="flex items-center gap-2">
                <a v-if="site.mobile_tel" :href="`tel:${site.mobile_tel}`" class="btn-secondary hidden sm:inline-flex lg:hidden xl:inline-flex">
                    <AppIcon name="phone" />
                    {{ t('website.call_now') }}
                </a>
                <Link :href="`${site.nav?.[6]?.href ?? '/'}?kind=site_visit`" class="btn-primary hidden sm:inline-flex">
                    {{ t('website.book_visit') }}
                </Link>
                <button
                    type="button"
                    class="inline-flex size-11 items-center justify-center rounded-md border border-border lg:hidden"
                    :aria-label="open ? t('website.close_menu') : t('website.menu')"
                    :aria-expanded="open"
                    aria-controls="mobile-menu"
                    @click="open = !open"
                >
                    <AppIcon :name="open ? 'xmark' : 'bars'" class="size-5" />
                </button>
            </div>
        </div>

        <div v-show="open" id="mobile-menu" class="border-t border-border bg-surface lg:hidden">
            <nav class="site-container flex flex-col py-3" :aria-label="t('website.menu')">
                <Link
                    v-for="item in site.nav"
                    :key="item.key"
                    :href="item.href"
                    class="flex min-h-11 items-center rounded-md px-3 text-base font-medium"
                    :class="item.active ? 'bg-background text-primary' : ''"
                >
                    {{ item.label }}
                </Link>
                <div class="mt-3 flex flex-wrap items-center gap-3 border-t border-border pt-3">
                    <Link
                        v-for="(href, code) in site.alternates"
                        :key="code"
                        :href="href"
                        class="rounded-md border border-border px-3 py-2 text-sm font-semibold"
                        :class="site.locale === code ? 'bg-primary text-primary-foreground' : ''"
                    >
                        {{ code === 'bn' ? 'বাংলা' : 'English' }}
                    </Link>
                    <a v-if="site.mobile_tel" :href="`tel:${site.mobile_tel}`" class="btn-primary ml-auto">
                        <AppIcon name="phone" />
                        {{ site.mobile }}
                    </a>
                </div>
            </nav>
        </div>
    </header>
</template>
