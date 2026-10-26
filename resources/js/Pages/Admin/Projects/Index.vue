<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/components/ui-kit/DataTable.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';
import StatusBadge from '@/components/ui-kit/StatusBadge.vue';
import { useI18n } from '@/lib/i18n';

defineProps({
    projects: { type: Array, default: () => [] },
});

const { t } = useI18n();

const statusTone = { ongoing: 'info', upcoming: 'warning', completed: 'success' };
</script>

<template>
    <AdminLayout>
        <PageHeader :title="t('projects.title')" />

        <DataTable
            :columns="[
                { key: 'name', label: t('projects.name'), type: 'text' },
                { key: 'type', label: t('projects.type'), type: 'text' },
                { key: 'status', label: t('projects.status'), type: 'status' },
                { key: 'location', label: t('projects.location'), type: 'text' },
                { key: 'units', label: t('projects.units'), type: 'number' },
                { key: 'available', label: t('projects.available'), type: 'number' },
            ]"
            :rows="projects"
        >
            <template #cell-name="{ row }">
                <Link :href="`/admin/projects/${row.id}`" class="hover:underline">{{ row.name }}</Link>
            </template>
            <template #cell-type="{ row }">{{ t(`projects.types.${row.type}`) }}</template>
            <template #cell-status="{ row }">
                <StatusBadge :label="t(`projects.statuses.${row.status}`)" :tone="statusTone[row.status] ?? 'muted'" icon="building" />
            </template>
        </DataTable>
    </AdminLayout>
</template>
