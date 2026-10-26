<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/components/ui-kit/DataTable.vue';
import FormField from '@/components/ui-kit/FormField.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';
import { useI18n } from '@/lib/i18n';

defineProps({
    customers: { type: Array, default: () => [] },
});

const { t } = useI18n();

const form = useForm({
    name: '',
    mobile: '',
    email: '',
    nid: '',
    address: '',
});

function submit() {
    form.post('/admin/customers', { preserveScroll: true, onSuccess: () => form.reset() });
}
</script>

<template>
    <AdminLayout>
        <PageHeader :title="t('customers.title')" />

        <form class="mb-3 grid gap-3 rounded-lg border border-border bg-surface p-3 md:grid-cols-5" @submit.prevent="submit">
            <FormField :label="t('customers.name')" :error="form.errors.name">
                <input v-model="form.name" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
            </FormField>
            <FormField :label="t('customers.mobile')" :error="form.errors.mobile">
                <input v-model="form.mobile" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" inputmode="tel" />
            </FormField>
            <FormField :label="t('customers.email')" :error="form.errors.email">
                <input v-model="form.email" type="email" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
            </FormField>
            <FormField :label="t('customers.nid')" :error="form.errors.nid">
                <input v-model="form.nid" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
            </FormField>
            <div class="flex items-end">
                <button type="submit" class="h-7 rounded-md bg-primary px-2.5 text-[13px] text-primary-foreground" :disabled="form.processing">
                    {{ t('customers.add') }}
                </button>
            </div>
        </form>

        <DataTable
            :columns="[
                { key: 'customer_code', label: t('customers.code'), type: 'text' },
                { key: 'name', label: t('customers.name'), type: 'text' },
                { key: 'mobile', label: t('customers.mobile'), type: 'text' },
                { key: 'email', label: t('customers.email'), type: 'text' },
                { key: 'bookings_count', label: t('customers.bookings'), type: 'number' },
            ]"
            :rows="customers"
        />
    </AdminLayout>
</template>
