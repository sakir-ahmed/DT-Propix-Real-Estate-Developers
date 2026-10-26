<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/components/ui-kit/DataTable.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';
import StatusBadge from '@/components/ui-kit/StatusBadge.vue';
import { useI18n } from '@/lib/i18n';

defineProps({
    bookings: { type: Array, default: () => [] },
});

const { t } = useI18n();
</script>

<template>
    <AdminLayout>
        <PageHeader :title="t('bookings.title')">
            <Link href="/admin/bookings/create" class="h-7 rounded-md bg-primary px-2.5 text-[13px] leading-7 text-primary-foreground">
                {{ t('bookings.new') }}
            </Link>
        </PageHeader>

        <DataTable
            :columns="[
                { key: 'booking_no', label: t('bookings.number'), type: 'text' },
                { key: 'customer', label: t('bookings.customer'), type: 'text' },
                { key: 'project', label: t('leads.project'), type: 'text' },
                { key: 'unit', label: t('bookings.unit'), type: 'text' },
                { key: 'net_price', label: t('bookings.net_price'), type: 'money' },
                { key: 'status', label: t('bookings.status'), type: 'status' },
                { key: 'booked_at', label: t('bookings.booked_at'), type: 'date' },
            ]"
            :rows="bookings"
        >
            <template #cell-booking_no="{ row }">
                <Link :href="`/admin/bookings/${row.id}`" class="hover:underline">{{ row.booking_no }}</Link>
            </template>
            <template #cell-status="{ row }">
                <StatusBadge
                    :label="row.status === 'cancelled' ? t('bookings.cancelled') : row.agreement_signed ? t('bookings.agreement_signed_label') : t('bookings.confirmed')"
                    :tone="row.status === 'cancelled' ? 'danger' : row.agreement_signed ? 'success' : 'info'"
                    :icon="row.agreement_signed ? 'stamp' : 'file-signature'"
                />
            </template>
        </DataTable>
    </AdminLayout>
</template>
