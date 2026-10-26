<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FormField from '@/components/ui-kit/FormField.vue';
import MoneyText from '@/components/ui-kit/MoneyText.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';
import { useI18n } from '@/lib/i18n';

const props = defineProps({
    customers: { type: Array, default: () => [] },
    selectedCustomerId: { type: Number, default: null },
    projects: { type: Array, default: () => [] },
    templates: { type: Array, default: () => [] },
    canDiscount: { type: Boolean, default: false },
    canViewPrice: { type: Boolean, default: false },
});

const { t } = useI18n();

const form = useForm({
    customer_id: props.selectedCustomerId ?? '',
    project_id: '',
    unit_id: '',
    template_id: props.templates[0]?.id ?? '',
    discount_amount: '',
    discount_reason: '',
});

const units = ref([]);

watch(
    () => form.project_id,
    async (projectId) => {
        units.value = [];
        form.unit_id = '';
        if (!projectId) return;
        const response = await fetch(`/admin/units-for-project?project_id=${projectId}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (response.ok) {
            units.value = await response.json();
        }
    },
);

const selectedUnit = computed(() => units.value.find((unit) => unit.id === Number(form.unit_id)));

function submit() {
    form.post('/admin/bookings');
}
</script>

<template>
    <AdminLayout>
        <PageHeader :title="t('bookings.new')" />

        <form class="grid max-w-2xl gap-3 rounded-lg border border-border bg-surface p-3" @submit.prevent="submit">
            <FormField :label="t('bookings.select_customer')" :error="form.errors.customer_id">
                <select v-model="form.customer_id" class="h-7 w-full rounded-md border border-border px-2 text-[13px]">
                    <option value="" disabled>{{ t('bookings.choose') }}</option>
                    <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.label }}</option>
                </select>
            </FormField>

            <div class="grid grid-cols-2 gap-x-4">
                <FormField :label="t('bookings.select_project')" :error="form.errors.project_id">
                    <select v-model="form.project_id" class="h-7 w-full rounded-md border border-border px-2 text-[13px]">
                        <option value="" disabled>{{ t('bookings.choose') }}</option>
                        <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                    </select>
                </FormField>

                <FormField :label="t('bookings.select_unit')" :error="form.errors.unit_id ?? form.errors.unit">
                    <select v-model="form.unit_id" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" :disabled="!units.length">
                        <option value="" disabled>{{ t('bookings.choose') }}</option>
                        <option v-for="unit in units" :key="unit.id" :value="unit.id">
                            {{ unit.number }} ({{ unit.size_sqft }} sqft)
                        </option>
                    </select>
                </FormField>
            </div>

            <p v-if="selectedUnit && canViewPrice && selectedUnit.price" class="text-[13px] text-muted">
                {{ t('ui.total') }}: <MoneyText :amount="selectedUnit.price" />
            </p>

            <FormField :label="t('bookings.template')">
                <select v-model="form.template_id" class="h-7 w-full rounded-md border border-border px-2 text-[13px]">
                    <option v-for="template in templates" :key="template.id" :value="template.id">{{ template.name }}</option>
                </select>
            </FormField>

            <div v-if="canDiscount" class="grid grid-cols-2 gap-x-4">
                <FormField :label="t('bookings.discount')" :error="form.errors.discount_amount">
                    <input v-model="form.discount_amount" inputmode="decimal" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
                </FormField>
                <FormField :label="t('bookings.discount_reason')" :error="form.errors.discount_reason">
                    <input v-model="form.discount_reason" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
                </FormField>
            </div>

            <p v-if="form.errors.schedule" class="text-xs text-danger">{{ form.errors.schedule }}</p>

            <div>
                <button type="submit" class="h-7 rounded-md bg-primary px-2.5 text-[13px] text-primary-foreground" :disabled="form.processing">
                    {{ t('bookings.save_booking') }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
