<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/components/ui-kit/DataTable.vue';
import FormField from '@/components/ui-kit/FormField.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';
import StatusBadge from '@/components/ui-kit/StatusBadge.vue';
import { useI18n } from '@/lib/i18n';

const props = defineProps({
    leads: { type: Array, default: () => [] },
    stage: { type: String, default: 'all' },
    stages: { type: Array, default: () => [] },
});

const { t } = useI18n();

const form = useForm({
    name: '',
    mobile: '',
    source: 'manual',
    note: '',
});

function submit() {
    form.post('/admin/leads', { preserveScroll: true, onSuccess: () => form.reset() });
}
</script>

<template>
    <AdminLayout>
        <PageHeader :title="t('leads.title')" />

        <form class="mb-3 grid gap-3 rounded-lg border border-border bg-surface p-3 md:grid-cols-4" @submit.prevent="submit">
            <FormField :label="t('leads.name')" :error="form.errors.name">
                <input v-model="form.name" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
            </FormField>
            <FormField :label="t('leads.mobile')" :error="form.errors.mobile">
                <input v-model="form.mobile" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" inputmode="tel" />
            </FormField>
            <FormField :label="t('leads.source')">
                <select v-model="form.source" class="h-7 w-full rounded-md border border-border px-2 text-[13px]">
                    <option value="manual">manual</option>
                    <option value="phone">phone</option>
                    <option value="walk-in">walk-in</option>
                    <option value="website">website</option>
                    <option value="broker">broker</option>
                </select>
            </FormField>
            <div class="flex items-end">
                <button type="submit" class="h-7 rounded-md bg-primary px-2.5 text-[13px] text-primary-foreground" :disabled="form.processing">
                    {{ t('leads.add') }}
                </button>
            </div>
        </form>

        <div class="mb-3 flex flex-wrap gap-1">
            <Link
                href="/admin/leads"
                class="h-6 rounded-md px-2 text-xs leading-6"
                :class="stage === 'all' ? 'bg-accent/15 text-accent' : 'border border-border'"
            >
                {{ t('ui.view_all') }}
            </Link>
            <Link
                v-for="s in stages"
                :key="s"
                :href="`/admin/leads?stage=${s}`"
                class="h-6 rounded-md px-2 text-xs leading-6"
                :class="stage === s ? 'bg-accent/15 text-accent' : 'border border-border'"
            >
                {{ t(`leads.stages.${s}`) }}
            </Link>
        </div>

        <DataTable
            :columns="[
                { key: 'name', label: t('leads.name'), type: 'text' },
                { key: 'mobile', label: t('leads.mobile'), type: 'text' },
                { key: 'stage', label: t('leads.stage'), type: 'status' },
                { key: 'source', label: t('leads.source'), type: 'text' },
                { key: 'project', label: t('leads.project'), type: 'text' },
                { key: 'assignee', label: t('leads.assignee'), type: 'text' },
                { key: 'next_follow_up', label: t('leads.next_follow_up'), type: 'date' },
            ]"
            :rows="leads"
        >
            <template #cell-name="{ row }">
                <Link :href="`/admin/leads/${row.id}`" class="hover:underline">{{ row.name }}</Link>
            </template>
            <template #cell-stage="{ row }">
                <StatusBadge :label="t(`leads.stages.${row.stage}`)" :tone="row.stage === 'lost' ? 'danger' : row.stage === 'booked' ? 'success' : 'info'" icon="phone" />
            </template>
        </DataTable>
    </AdminLayout>
</template>
