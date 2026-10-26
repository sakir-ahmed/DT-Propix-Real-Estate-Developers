<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/components/ui-kit/DataTable.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';
import StatusBadge from '@/components/ui-kit/StatusBadge.vue';
import { useI18n } from '@/lib/i18n';

defineProps({
    bookings: { type: Array, default: () => [] },
    receipts: { type: Array, default: () => [] },
});

const { t } = useI18n();
</script>

<template>
    <AdminLayout>
        <PageHeader :title="t('collections.title')" />

        <section class="mb-3">
            <h2 class="mb-2 text-sm font-semibold">{{ t('collections.desk') }}</h2>
            <DataTable
                :columns="[
                    { key: 'booking_no', label: t('bookings.number'), type: 'text' },
                    { key: 'customer', label: t('bookings.customer'), type: 'text' },
                    { key: 'unit', label: t('bookings.unit'), type: 'text' },
                    { key: 'net_price', label: t('bookings.net_price'), type: 'money' },
                    { key: 'collected', label: t('collections.collected'), type: 'money' },
                    { key: 'overdue', label: t('collections.overdue'), type: 'money' },
                    { key: 'upcoming', label: t('collections.upcoming'), type: 'money' },
                ]"
                :rows="bookings.map((row) => ({ ...row, collected: row.totals.collected, overdue: row.totals.overdue, upcoming: row.totals.upcoming }))"
            >
                <template #cell-booking_no="{ row }">
                    <Link :href="`/admin/bookings/${row.id}`" class="hover:underline">{{ row.booking_no }}</Link>
                </template>
                <template #cell-overdue="{ row }">
                    <span :class="Number(row.overdue) > 0 ? 'text-danger' : ''">{{ row.overdue }}</span>
                </template>
            </DataTable>
        </section>

        <section>
            <h2 class="mb-2 text-sm font-semibold">{{ t('bookings.receipts') }}</h2>
            <DataTable
                :columns="[
                    { key: 'receipt_no', label: t('collections.receipt_no'), type: 'text' },
                    { key: 'customer', label: t('bookings.customer'), type: 'text' },
                    { key: 'amount', label: t('collections.amount'), type: 'money' },
                    { key: 'method', label: t('collections.method'), type: 'text' },
                    { key: 'status', label: t('bookings.status'), type: 'status' },
                    { key: 'received_at', label: t('collections.received_at'), type: 'date' },
                ]"
                :rows="receipts"
            >
                <template #cell-method="{ row }">{{ t(`collections.methods.${row.method}`) }}</template>
                <template #cell-status="{ row }">
                    <StatusBadge
                        :label="t(`collections.${row.status}`)"
                        :tone="row.status === 'posted' ? 'success' : row.status === 'provisional' ? 'warning' : 'danger'"
                        icon="cash-register"
                    />
                </template>
            </DataTable>
        </section>
    </AdminLayout>
</template>
