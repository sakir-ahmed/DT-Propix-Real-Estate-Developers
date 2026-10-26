<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/components/ui-kit/KpiCard.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';
import MoneyText from '@/components/ui-kit/MoneyText.vue';
import { useI18n } from '@/lib/i18n';

defineProps({
    funnel: { type: Object, required: true },
    period: { type: Object, default: () => ({}) },
    todayFollowUps: { type: Number, default: 0 },
    overdueBookings: { type: Array, default: () => [] },
});

const { t } = useI18n();
</script>

<template>
    <AdminLayout>
        <PageHeader :title="t('ui.dashboard_title')" />

        <section class="mb-3">
            <h2 class="mb-2 text-sm font-semibold text-muted">{{ t('ui.funnel') }}</h2>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
                <KpiCard :label="t('ui.funnel_leads')" :value="String(funnel.leads)" />
                <KpiCard :label="t('ui.funnel_visits')" :value="String(funnel.site_visits)" />
                <KpiCard :label="t('ui.funnel_bookings')" :value="String(funnel.bookings)" />
                <KpiCard :label="t('ui.funnel_sales')" :value="funnel.sales" />
                <KpiCard :label="t('ui.funnel_collection')" :value="funnel.collection" />
                <KpiCard :label="t('ui.funnel_outstanding')" :value="funnel.outstanding" />
            </div>
        </section>

        <div class="grid gap-3 lg:grid-cols-2">
            <section class="rounded-lg border border-border bg-surface p-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold">{{ t('ui.today_follow_ups') }}</h2>
                    <Link href="/admin/leads" class="text-xs text-accent">{{ t('ui.view_all') }}</Link>
                </div>
                <p class="mt-2 text-2xl font-bold tabular-nums">{{ todayFollowUps }}</p>
            </section>

            <section class="rounded-lg border border-border bg-surface p-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold">{{ t('ui.overdue_bookings') }}</h2>
                    <Link href="/admin/collections" class="text-xs text-accent">{{ t('ui.view_all') }}</Link>
                </div>
                <table v-if="overdueBookings.length" class="mt-2 w-full text-[13px]">
                    <tbody>
                        <tr v-for="row in overdueBookings" :key="row.id" class="border-b border-border last:border-0">
                            <td class="py-1.5">
                                <Link :href="`/admin/bookings/${row.id}`" class="hover:underline">{{ row.booking_no }}</Link>
                                <span class="ml-2 text-muted">{{ row.customer }} / {{ row.unit }}</span>
                            </td>
                            <td class="py-1.5 text-right text-danger"><MoneyText :amount="row.overdue" /></td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="mt-2 text-[13px] text-muted">{{ t('ui.empty') }}</p>
            </section>
        </div>
    </AdminLayout>
</template>
