<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import ConfirmDialog from '@/components/ui-kit/ConfirmDialog.vue';
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

function logout() {
    router.post('/admin/logout');
}
</script>

<template>
    <div class="min-h-screen bg-background text-foreground" :class="page.props.locale === 'bn' ? 'leading-relaxed' : ''">
        <Toaster position="top-right" />
        <ConfirmDialog />
        <div class="flex min-h-screen">
            <aside class="hidden w-[220px] shrink-0 border-r border-border bg-surface md:block">
                <div class="flex h-12 items-center gap-2 px-3 text-sm font-semibold">
                    <img :src="'/images/brand/logo-mark.svg'" alt="" class="h-8 w-8" />
                    {{ t('ui.app_name') }}
                </div>
                <nav class="px-2">
                    <Link
                        v-for="item in page.props.menu"
                        :key="item.key"
                        :href="item.route"
                        class="flex h-8 items-center gap-2 rounded-md px-2 text-[13px]"
                    >
                        <AppIcon :name="item.icon" />
                        {{ item.label }}
                    </Link>
                </nav>
            </aside>
            <div class="min-w-0 flex-1">
                <header class="flex h-12 items-center justify-end border-b border-border px-4">
                    <div class="flex items-center gap-2">
                        <LanguageSwitch />
                        <ThemeToggle />
                        <button type="button" class="h-7 rounded-md px-2.5 text-[13px]" @click="logout">{{ t('auth.logout') }}</button>
                    </div>
                </header>
                <main class="p-4">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
