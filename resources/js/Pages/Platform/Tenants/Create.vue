<script setup>
import { useForm } from '@inertiajs/vue3';
import FormField from '@/components/ui-kit/FormField.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';
import PlatformLayout from '@/Layouts/PlatformLayout.vue';
import { useI18n } from '@/lib/i18n';

const props = defineProps({
    plans: { type: Array, default: () => [] },
});

const { t } = useI18n();

const form = useForm({
    name: '',
    slug: '',
    plan: props.plans[0]?.slug ?? 'starter',
    trial_ends_at: '',
    message_credits: 0,
    owner_name: '',
    owner_username: '',
    owner_email: '',
    owner_mobile: '',
    owner_password: '',
});

function submit() {
    form.post('/platform/tenants');
}
</script>

<template>
    <PlatformLayout>
        <PageHeader :title="t('platform.create_tenant')" />
        <form class="grid max-w-2xl grid-cols-1 gap-x-4 gap-y-3 md:grid-cols-2" @submit.prevent="submit">
            <FormField :label="t('platform.name')" :error="form.errors.name">
                <input v-model="form.name" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
            </FormField>
            <FormField :label="t('platform.slug')" :error="form.errors.slug">
                <input v-model="form.slug" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
            </FormField>
            <FormField :label="t('platform.plan')" :error="form.errors.plan">
                <select v-model="form.plan" class="h-7 w-full rounded-md border border-border bg-surface px-2 text-[13px]">
                    <option v-for="plan in plans" :key="plan.slug" :value="plan.slug">{{ plan.name }}</option>
                </select>
            </FormField>
            <FormField :label="t('platform.trial_ends_at')" :error="form.errors.trial_ends_at">
                <input v-model="form.trial_ends_at" type="date" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
            </FormField>
            <FormField :label="t('platform.message_credits')" :error="form.errors.message_credits">
                <input v-model="form.message_credits" type="number" min="0" class="h-7 w-full rounded-md border border-border px-2 text-right text-[13px]" />
            </FormField>
            <FormField :label="t('platform.owner_name')" :error="form.errors.owner_name">
                <input v-model="form.owner_name" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
            </FormField>
            <FormField :label="t('platform.owner_username')" :error="form.errors.owner_username">
                <input v-model="form.owner_username" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
            </FormField>
            <FormField :label="t('platform.owner_email')" :error="form.errors.owner_email">
                <input v-model="form.owner_email" type="email" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
            </FormField>
            <FormField :label="t('platform.owner_mobile')" :error="form.errors.owner_mobile">
                <input v-model="form.owner_mobile" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
            </FormField>
            <FormField :label="t('platform.owner_password')" :error="form.errors.owner_password">
                <input v-model="form.owner_password" type="password" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
            </FormField>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="h-7 rounded-md bg-accent px-2.5 text-[13px] text-accent-foreground" :disabled="form.processing">
                    {{ t('platform.save') }}
                </button>
            </div>
        </form>
    </PlatformLayout>
</template>
