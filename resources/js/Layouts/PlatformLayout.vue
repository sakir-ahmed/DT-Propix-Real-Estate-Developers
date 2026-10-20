<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import LanguageSwitch from '@/components/ui-kit/LanguageSwitch.vue';
import ThemeToggle from '@/components/ui-kit/ThemeToggle.vue';
import { useI18n } from '@/lib/i18n';

const page = usePage();
const { t } = useI18n();

watch(
    () => page.props.flash?.success,
    (message) => {
        if (message) {
            toast.success(message);
        }
    },
);
</script>

<template>
    <div class="min-h-screen bg-background" :class="page.props.locale === 'bn' ? 'leading-relaxed' : ''">
        <Toaster position="top-right" />
        <header class="flex h-12 items-center justify-between border-b border-border px-4">
            <Link href="/platform/tenants" class="flex items-center gap-2 text-sm font-semibold">
                <img :src="'/images/brand/logo-mark.svg'" alt="" class="h-8 w-8" />
                {{ t('nav.platform') }}
            </Link>
            <div class="flex items-center gap-2">
                <LanguageSwitch />
                <ThemeToggle />
                <button type="button" class="h-7 rounded-md px-2.5 text-[13px]" @click="router.post('/admin/logout')">{{ t('auth.logout') }}</button>
            </div>
        </header>
        <main class="mx-auto max-w-4xl p-4">
            <slot />
        </main>
    </div>
</template>
