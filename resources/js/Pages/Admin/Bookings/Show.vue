<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/components/ui-kit/DataTable.vue';
import FormField from '@/components/ui-kit/FormField.vue';
import KpiCard from '@/components/ui-kit/KpiCard.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';
import PriceBreakdown from '@/components/ui-kit/PriceBreakdown.vue';
import StatusBadge from '@/components/ui-kit/StatusBadge.vue';
import { useI18n } from '@/lib/i18n';

const props = defineProps({
    booking: { type: Object, required: true },
    schedule: { type: Array, default: () => [] },
    totals: { type: Object, required: true },
    receipts: { type: Array, default: () => [] },
    canCollect: { type: Boolean, default: false },
});

const { t } = useI18n();

const agreementForm = useForm({});
const collectForm = useForm({
    method: 'cash',
    amount: '',
    reference: '',
});

function signAgreement() {
    agreementForm.post(`/admin/bookings/${props.booking.id}/agreement`, { preserveScroll: true });
}

function collect() {
    collectForm.post(`/admin/bookings/${props.booking.id}/receipts`, {
        preserveScroll: true,
        onSuccess: () => collectForm.reset({ method: collectForm.method }),
    });
}
</script>

<template>
    <AdminLayout>
        <PageHeader :title="booking.booking_no" :description="`${booking.project} / ${booking.unit} - ${booking.customer.name} (${booking.customer.customer_code})`">
            <StatusBadge
                :label="booking.status === 'cancelled' ? t('bookings.cancelled') : booking.agreement_signed ? t('bookings.agreement_signed_label') : t('bookings.confirmed')"
                :tone="booking.status === 'cancelled' ? 'danger' : booking.agreement_signed ? 'success' : 'info'"
                :icon="booking.agreement_signed ? 'stamp' : 'file-signature'"
            />
        </PageHeader>

        <div class="mb-3 grid grid-cols-2 gap-3 md:grid-cols-4">
            <KpiCard :label="t('bookings.net_price')" :value="booking.net_price" />
            <KpiCard :label="t('collections.collected')" :value="totals.collected" />
            <KpiCard :label="t('collections.overdue')" :value="totals.overdue" />
            <KpiCard :label="t('collections.upcoming')" :value="totals.upcoming" />
        </div>

        <div class="grid gap-3 lg:grid-cols-2">
            <section>
                <h2 class="mb-2 text-sm font-semibold">{{ t('bookings.price_breakdown') }}</h2>
                <PriceBreakdown :lines="booking.price_snapshot.lines" />
            </section>

            <section v-if="canCollect && booking.status !== 'cancelled'">
                <h2 class="mb-2 text-sm font-semibold">{{ t('bookings.collect_payment') }}</h2>
                <form class="grid grid-cols-2 gap-3 rounded-lg border border-border bg-surface p-3" @submit.prevent="collect">
                    <FormField :label="t('collections.method')">
                        <select v-model="collectForm.method" class="h-7 w-full rounded-md border border-border px-2 text-[13px]">
                            <option v-for="(label, value) in { cash: t('collections.methods.cash'), cheque: t('collections.methods.cheque'), bank: t('collections.methods.bank'), mobile: t('collections.methods.mobile'), card: t('collections.methods.card') }" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </FormField>
                    <FormField :label="t('collections.amount')" :error="collectForm.errors.amount">
                        <input v-model="collectForm.amount" inputmode="decimal" class="h-7 w-full rounded-md border border-border px-2 text-right text-[13px]" />
                    </FormField>
                    <FormField :label="t('collections.reference')">
                        <input v-model="collectForm.reference" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
                    </FormField>
                    <div class="flex items-end">
                        <button type="submit" class="h-7 rounded-md bg-primary px-2.5 text-[13px] text-primary-foreground" :disabled="collectForm.processing">
                            {{ t('bookings.collect_payment') }}
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <section class="mt-3">
            <div class="flex items-center justify-between">
                <h2 class="mb-2 text-sm font-semibold">{{ t('bookings.schedule') }}</h2>
                <button
                    v-if="!booking.agreement_signed && booking.status !== 'cancelled'"
                    type="button"
                    class="h-7 rounded-md bg-primary px-2.5 text-[13px] text-primary-foreground"
                    :disabled="agreementForm.processing"
                    @click="signAgreement"
                >
                    {{ t('bookings.sign_agreement') }}
                </button>
            </div>
            <DataTable
                :columns="[
                    { key: 'seq', label: 'No', type: 'number' },
                    { key: 'label', label: t('leads.note'), type: 'text' },
                    { key: 'due_date', label: t('ui.due_date'), type: 'date' },
                    { key: 'amount', label: t('collections.amount'), type: 'money' },
                    { key: 'paid', label: t('ui.paid'), type: 'money' },
                    { key: 'due', label: t('ui.due'), type: 'money' },
                ]"
                :rows="schedule"
            />
        </section>

        <section class="mt-3">
            <h2 class="mb-2 text-sm font-semibold">{{ t('bookings.receipts') }}</h2>
            <DataTable
                :columns="[
                    { key: 'receipt_no', label: t('collections.receipt_no'), type: 'text' },
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
