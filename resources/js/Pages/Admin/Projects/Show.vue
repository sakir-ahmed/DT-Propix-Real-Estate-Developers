<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/components/ui-kit/DataTable.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';
import StatusBadge from '@/components/ui-kit/StatusBadge.vue';
import { useI18n } from '@/lib/i18n';

const props = defineProps({
    project: { type: Object, required: true },
    units: { type: Array, default: () => [] },
    canViewPrice: { type: Boolean, default: false },
});

const { t } = useI18n();

const statusMeta = {
    available: { tone: 'success', icon: 'circle-check' },
    on_hold: { tone: 'warning', icon: 'clock' },
    booked: { tone: 'info', icon: 'file-signature' },
    sold: { tone: 'muted', icon: 'stamp' },
    handed_over: { tone: 'muted', icon: 'door-open' },
    landowner_share: { tone: 'info', icon: 'handshake' },
    blocked: { tone: 'danger', icon: 'lock' },
};

const holdForm = useForm({});
const releaseForm = useForm({});

function hold(unitId) {
    holdForm.post(`/admin/units/${unitId}/hold`, { preserveScroll: true });
}

function release(unitId) {
    releaseForm.post(`/admin/units/${unitId}/release`, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout>
        <PageHeader :title="project.name" :description="[t(`projects.types.${project.type}`), project.location].filter(Boolean).join(' - ')" />

        <DataTable
            :columns="[
                { key: 'number', label: 'No', type: 'text' },
                { key: 'size', label: 'Size (sqft)', type: 'number' },
                { key: 'facing', label: 'Facing', type: 'text' },
                { key: 'bedrooms', label: 'Bed', type: 'number' },
                ...(canViewPrice ? [{ key: 'price', label: `${t('ui.total')} (BDT)`, type: 'money' }] : []),
                { key: 'status', label: t('projects.status'), type: 'status' },
                { key: 'actions', label: '', type: 'actions' },
            ]"
            :rows="units"
        >
            <template #cell-status="{ row }">
                <StatusBadge :label="t(`units.${row.status}`)" :tone="statusMeta[row.status]?.tone ?? 'muted'" :icon="statusMeta[row.status]?.icon ?? 'circle-info'" />
            </template>
            <template #cell-actions="{ row }">
                <button v-if="row.status === 'available'" type="button" class="h-6 rounded-md border border-border px-2 text-xs" @click="hold(row.id)">
                    {{ t('units.hold') }}
                </button>
                <button v-else-if="row.status === 'on_hold'" type="button" class="h-6 rounded-md border border-border px-2 text-xs" @click="release(row.id)">
                    {{ t('units.release') }}
                </button>
            </template>
        </DataTable>
    </AdminLayout>
</template>
