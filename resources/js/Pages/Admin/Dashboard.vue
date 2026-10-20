<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import EmptyState from '@/components/ui-kit/EmptyState.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();
</script>

<template>
    <AdminLayout>
        <PageHeader :title="t('ui.dashboard_title')" />
        <EmptyState :title="t('ui.dashboard_empty')" />
    </AdminLayout>
</template>
