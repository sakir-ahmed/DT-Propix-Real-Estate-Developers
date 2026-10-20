<script setup>
import { Link } from '@inertiajs/vue3';
import PlatformLayout from '@/Layouts/PlatformLayout.vue';
import DataTable from '@/components/ui-kit/DataTable.vue';
import EmptyState from '@/components/ui-kit/EmptyState.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';
import { useI18n } from '@/lib/i18n';

defineProps({
    tenants: { type: Array, default: () => [] },
});

const { t } = useI18n();

const columns = [
    { key: 'name', label: 'Name', type: 'text' },
    { key: 'slug', label: 'Slug', type: 'text' },
    { key: 'plan', label: 'Plan', type: 'text' },
    { key: 'status', label: 'Status', type: 'status' },
    { key: 'message_credits', label: 'Credits', type: 'number' },
];
</script>

<template>
    <PlatformLayout>
        <PageHeader :title="t('platform.tenants')">
            <Link href="/platform/tenants/create" class="inline-flex h-7 items-center rounded-md bg-primary px-2.5 text-[13px] text-primary-foreground">
                {{ t('platform.create_tenant') }}
            </Link>
        </PageHeader>
        <EmptyState v-if="tenants.length === 0" :title="t('platform.empty')" />
        <DataTable v-else :columns="columns" :rows="tenants" />
    </PlatformLayout>
</template>
