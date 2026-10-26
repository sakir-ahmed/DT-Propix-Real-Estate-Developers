<script setup>
import { Link } from '@inertiajs/vue3';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import { useI18n } from '@/lib/i18n';

defineProps({
    items: { type: Array, default: () => [] },
    light: { type: Boolean, default: false },
});

const { t } = useI18n();
</script>

<template>
    <nav v-if="items.length" :aria-label="t('website.breadcrumb')" class="text-sm" :class="light ? 'text-white/70' : 'text-muted'">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li v-for="(item, index) in items" :key="item.href" class="inline-flex items-center gap-1.5">
                <AppIcon v-if="index > 0" name="chevron-right" class="size-3 opacity-60" />
                <Link v-if="index < items.length - 1" :href="item.href" class="hover:underline">{{ item.label }}</Link>
                <span v-else aria-current="page" :class="light ? 'text-white' : 'text-foreground'">{{ item.label }}</span>
            </li>
        </ol>
    </nav>
</template>
