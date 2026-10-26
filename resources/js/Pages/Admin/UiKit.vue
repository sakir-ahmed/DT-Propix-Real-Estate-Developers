<script setup>
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ActivityTimeline from '@/components/ui-kit/ActivityTimeline.vue';
import ApprovalDialog from '@/components/ui-kit/ApprovalDialog.vue';
import ApprovalTimeline from '@/components/ui-kit/ApprovalTimeline.vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import ChartCard from '@/components/ui-kit/ChartCard.vue';
import Combobox from '@/components/ui-kit/Combobox.vue';
import CommandPalette from '@/components/ui-kit/CommandPalette.vue';
import ConfirmDialog from '@/components/ui-kit/ConfirmDialog.vue';
import CustomerBanner from '@/components/ui-kit/CustomerBanner.vue';
import DataTable from '@/components/ui-kit/DataTable.vue';
import DateRangePicker from '@/components/ui-kit/DateRangePicker.vue';
import DateText from '@/components/ui-kit/DateText.vue';
import DocumentList from '@/components/ui-kit/DocumentList.vue';
import EmptyState from '@/components/ui-kit/EmptyState.vue';
import ErrorPage from '@/components/ui-kit/ErrorPage.vue';
import ErrorState from '@/components/ui-kit/ErrorState.vue';
import FileUpload from '@/components/ui-kit/FileUpload.vue';
import FilterBar from '@/components/ui-kit/FilterBar.vue';
import FormField from '@/components/ui-kit/FormField.vue';
import KpiCard from '@/components/ui-kit/KpiCard.vue';
import LanguageSwitch from '@/components/ui-kit/LanguageSwitch.vue';
import MobileInput from '@/components/ui-kit/MobileInput.vue';
import MoneyInput from '@/components/ui-kit/MoneyInput.vue';
import MoneyText from '@/components/ui-kit/MoneyText.vue';
import MultiSelect from '@/components/ui-kit/MultiSelect.vue';
import NotificationBell from '@/components/ui-kit/NotificationBell.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';
import PipelineBoard from '@/components/ui-kit/PipelineBoard.vue';
import PriceBreakdown from '@/components/ui-kit/PriceBreakdown.vue';
import ProjectPicker from '@/components/ui-kit/ProjectPicker.vue';
import QuantityStepper from '@/components/ui-kit/QuantityStepper.vue';
import ReasonDialog from '@/components/ui-kit/ReasonDialog.vue';
import ScheduleTable from '@/components/ui-kit/ScheduleTable.vue';
import SkeletonRows from '@/components/ui-kit/SkeletonRows.vue';
import SlideOver from '@/components/ui-kit/SlideOver.vue';
import StatusBadge from '@/components/ui-kit/StatusBadge.vue';
import ThemeToggle from '@/components/ui-kit/ThemeToggle.vue';
import TypedConfirmDialog from '@/components/ui-kit/TypedConfirmDialog.vue';
import UnitGrid from '@/components/ui-kit/UnitGrid.vue';
import UnitStatusBadge from '@/components/ui-kit/UnitStatusBadge.vue';
import { useI18n } from '@/lib/i18n';

defineProps({
    components: { type: Array, required: true },
});

const { t } = useI18n();
const query = ref('');
const money = ref('1250.00');
const mobile = ref('01710000001');
const quantity = ref(1);
const project = ref('heights');
const selected = ref(['crm']);
const rangeFrom = ref('2026-10-01');
const rangeTo = ref('2026-10-15');
const fileName = ref('');
const combo = ref('available');
const confirmOpen = ref(false);
const reasonOpen = ref(false);
const approvalOpen = ref(false);
const typedOpen = ref(false);
const slideOpen = ref(false);
const commandOpen = ref(false);

const columns = [
    { key: 'unit', label: 'Unit', type: 'text' },
    { key: 'due', label: 'Due', type: 'date' },
    { key: 'amount', label: 'Amount (৳)', type: 'money' },
    { key: 'actions', label: '', type: 'actions' },
];

const rows = [
    { unit: 'A-101', due: '2026-10-15T08:30:00+06:00', amount: 2500000 },
];
</script>

<template>
    <AdminLayout>
        <PageHeader :title="t('ui.ui_kit_title')" :description="t('ui.ui_kit_intro')">
            <LanguageSwitch />
            <ThemeToggle />
            <NotificationBell :count="2" :items="['Approval waiting', 'Hold expiring']" />
        </PageHeader>

        <ul class="mb-4 flex flex-wrap gap-2">
            <li v-for="name in components" :key="name">
                <StatusBadge :label="name" tone="info" icon="circle-info" />
            </li>
        </ul>

        <div class="space-y-4">
            <section data-component="AppIcon" class="flex gap-2">
                <AppIcon name="plus" label="Add" />
                <AppIcon name="trash" label="Delete" />
                <AppIcon name="chart-line" />
            </section>

            <CustomerBanner name="Rahim Uddin" code="C-1001" project="Dhaka Heights" unit="A-101" status="Booked" :outstanding="250000" />

            <UnitGrid :units="[{ code: 'A-101', status: 'available' }, { code: 'A-102', status: 'hold' }, { code: 'A-103', status: 'booked' }, { code: 'A-104', status: 'sold' }]" />
            <UnitStatusBadge status="landowner" />
            <UnitStatusBadge status="blocked" />

            <PipelineBoard :columns="[{ name: 'New', cards: ['Walk-in'] }, { name: 'Visited', cards: ['Site tour'] }, { name: 'Booked', cards: ['A-101'] }]" />

            <ScheduleTable :rows="[{ no: '1', due: '2026-11-01T10:00:00+06:00', amount: 500000 }]" />
            <PriceBreakdown :lines="[{ label: 'Base', amount: 5000000 }, { label: 'Offer', amount: -100000 }]" />
            <ActivityTimeline :items="[{ title: 'Lead created', at: '2026-10-15T14:30:00+06:00' }]" />
            <ApprovalTimeline :steps="[{ label: 'Discount', status: 'Pending' }, { label: 'Manager', status: 'Approved' }]" />
            <DocumentList :documents="[{ name: 'Allotment letter', visibility: 'Internal' }]" />
            <ProjectPicker v-model="project" label="Project" :projects="[{ value: 'heights', label: 'Dhaka Heights' }]" />

            <div class="flex flex-wrap gap-2">
                <button type="button" class="h-7 rounded-md border border-border px-2.5 text-[13px]" @click="confirmOpen = true">ConfirmDialog</button>
                <button type="button" class="h-7 rounded-md border border-border px-2.5 text-[13px]" @click="reasonOpen = true">ReasonDialog</button>
                <button type="button" class="h-7 rounded-md border border-border px-2.5 text-[13px]" @click="approvalOpen = true">ApprovalDialog</button>
                <button type="button" class="h-7 rounded-md border border-border px-2.5 text-[13px]" @click="typedOpen = true">TypedConfirmDialog</button>
                <button type="button" class="h-7 rounded-md border border-border px-2.5 text-[13px]" @click="slideOpen = true">SlideOver</button>
            </div>

            <ConfirmDialog v-model:open="confirmOpen" title="Delete draft" message="This cannot be undone." />
            <ReasonDialog v-model:open="reasonOpen" />
            <ApprovalDialog v-model:open="approvalOpen" />
            <TypedConfirmDialog v-model:open="typedOpen" phrase="DELETE" />
            <SlideOver v-model:open="slideOpen" title="Unit A-101">
                <p>Sample detail.</p>
            </SlideOver>

            <FormField label="Note">
                <input class="h-7 w-full max-w-sm rounded-md border border-border px-2 text-[13px]" />
            </FormField>
            <div class="grid max-w-xl gap-3 md:grid-cols-2">
                <MoneyInput v-model="money" label="Amount" />
                <MobileInput v-model="mobile" label="Mobile" />
                <QuantityStepper v-model="quantity" />
                <Combobox v-model="combo" label="Status" :options="[{ value: 'available', label: 'Available' }]" />
                <MultiSelect v-model="selected" label="Modules" :options="[{ value: 'crm', label: 'CRM' }, { value: 'units', label: 'Units' }]" />
                <DateRangePicker v-model:from="rangeFrom" v-model:to="rangeTo" label="Dates" />
                <FileUpload v-model="fileName" />
            </div>

            <FilterBar v-model:query="query" />
            <DataTable :columns="columns" :rows="rows" />
            <div class="grid gap-3 md:grid-cols-3">
                <KpiCard label="Leads" value="128" trend="12%" direction="up" />
                <ChartCard title="Collection" />
                <div class="space-y-2">
                    <MoneyText :amount="1250" />
                    <DateText value="2026-10-15T14:30:00+06:00" />
                </div>
            </div>
            <EmptyState :title="t('ui.empty')" />
            <ErrorState />
            <SkeletonRows :rows="2" />
            <CommandPalette v-model:open="commandOpen" :commands="['New lead', 'Open units']" />
            <ErrorPage :code="403" />
        </div>
    </AdminLayout>
</template>
