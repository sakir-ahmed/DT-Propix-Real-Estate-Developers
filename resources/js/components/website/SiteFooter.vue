<script setup>
import { Link } from '@inertiajs/vue3';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import { useI18n } from '@/lib/i18n';
import { localizeDigits } from '@/lib/format';

defineProps({
    site: { type: Object, required: true },
});

const { t, locale } = useI18n();
</script>

<template>
    <footer class="bg-foreground text-white">
        <div class="site-container grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <div class="flex items-center gap-3">
                    <img :src="site.mark" alt="" class="h-10 w-10 rounded bg-white p-1" width="40" height="40" />
                    <span class="font-display text-xl font-semibold">{{ site.name }}</span>
                </div>
                <p class="mt-4 max-w-md text-[15px] leading-7 text-white/75">{{ t('website.footer_about') }}</p>
                <ul class="mt-6 space-y-3 text-[15px]">
                    <li v-if="site.address" class="flex gap-3">
                        <AppIcon name="location-dot" class="mt-1.5 text-accent" />
                        <span class="text-white/85">{{ site.address }}</span>
                    </li>
                    <li v-if="site.mobile_tel" class="flex gap-3">
                        <AppIcon name="phone" class="mt-1.5 text-accent" />
                        <a :href="`tel:${site.mobile_tel}`" class="text-white/85 hover:text-white">{{ site.mobile }}</a>
                    </li>
                    <li v-if="site.email" class="flex gap-3">
                        <AppIcon name="envelope" class="mt-1.5 text-accent" />
                        <a :href="`mailto:${site.email}`" class="text-white/85 hover:text-white">{{ site.email }}</a>
                    </li>
                    <li v-if="site.office_hours" class="flex gap-3">
                        <AppIcon name="clock" class="mt-1.5 text-accent" />
                        <span class="text-white/85">{{ site.office_hours }}</span>
                    </li>
                </ul>
                <div v-if="site.social?.facebook || site.social?.youtube || site.social?.linkedin" class="mt-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-white/60">{{ t('website.follow_us') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a v-if="site.social.facebook" :href="site.social.facebook" rel="noopener" target="_blank" class="rounded-md border border-white/20 px-3 py-2 text-sm hover:bg-white/10">Facebook</a>
                        <a v-if="site.social.youtube" :href="site.social.youtube" rel="noopener" target="_blank" class="rounded-md border border-white/20 px-3 py-2 text-sm hover:bg-white/10">YouTube</a>
                        <a v-if="site.social.linkedin" :href="site.social.linkedin" rel="noopener" target="_blank" class="rounded-md border border-white/20 px-3 py-2 text-sm hover:bg-white/10">LinkedIn</a>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="font-display text-sm font-semibold uppercase tracking-wider text-white/60">{{ t('website.footer_company') }}</h2>
                <ul class="mt-4 space-y-2.5 text-[15px]">
                    <li v-for="link in site.footer?.company" :key="link.href">
                        <Link :href="link.href" class="text-white/85 hover:text-white">{{ link.label }}</Link>
                    </li>
                </ul>
            </div>

            <div>
                <h2 class="font-display text-sm font-semibold uppercase tracking-wider text-white/60">{{ t('website.footer_explore') }}</h2>
                <ul class="mt-4 space-y-2.5 text-[15px]">
                    <li v-for="link in site.footer?.explore" :key="link.href">
                        <Link :href="link.href" class="text-white/85 hover:text-white">{{ link.label }}</Link>
                    </li>
                </ul>
            </div>

            <div>
                <h2 v-if="site.footer?.projects?.length" class="font-display text-sm font-semibold uppercase tracking-wider text-white/60">{{ t('website.footer_projects') }}</h2>
                <ul v-if="site.footer?.projects?.length" class="mt-4 space-y-2.5 text-[15px]">
                    <li v-for="link in site.footer.projects" :key="link.href">
                        <Link :href="link.href" class="text-white/85 hover:text-white">{{ link.label }}</Link>
                    </li>
                </ul>
                <h2 class="font-display mt-8 text-sm font-semibold uppercase tracking-wider text-white/60">{{ t('website.footer_legal') }}</h2>
                <ul class="mt-4 space-y-2.5 text-[15px]">
                    <li v-for="link in site.footer?.legal" :key="link.href">
                        <Link :href="link.href" class="text-white/85 hover:text-white">{{ link.label }}</Link>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="site-container flex flex-col gap-2 py-5 text-sm text-white/60 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ localizeDigits(site.year, locale()) }} {{ site.name }}. {{ t('website.footer_rights') }}</p>
                <p>
                    {{ t('website.footer_powered_by') }}
                    <a :href="site.powered_by?.url" rel="noopener" target="_blank" class="font-semibold text-white/85 hover:text-white">DT Propix</a>
                    <span aria-hidden="true">&middot;</span>
                    <a :href="site.powered_by?.url" rel="noopener" target="_blank" class="hover:text-white">{{ site.powered_by?.name }}</a>
                </p>
            </div>
        </div>
    </footer>
</template>
