<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import FormField from '@/components/ui-kit/FormField.vue';
import { useI18n } from '@/lib/i18n';

const props = defineProps({
    kind: { type: String, default: 'enquiry' },
    projectId: { type: [Number, String], default: null },
    showKind: { type: Boolean, default: false },
    showProject: { type: Boolean, default: true },
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});

const page = usePage();
const { t, locale } = useI18n();

const kinds = ['enquiry', 'callback', 'site_visit'];
const projects = computed(() => page.props.site?.project_options ?? []);
const done = ref(false);
const successMessage = ref('');

const form = useForm({
    kind: props.kind,
    name: '',
    mobile: '',
    email: '',
    project_id: props.projectId ? Number(props.projectId) : '',
    message: '',
    preferred_date: '',
    consent: false,
    company_website: '',
    page: '',
    utm_source: '',
    utm_medium: '',
    utm_campaign: '',
});

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    form.page = window.location.pathname;
    form.utm_source = params.get('utm_source') ?? '';
    form.utm_medium = params.get('utm_medium') ?? '';
    form.utm_campaign = params.get('utm_campaign') ?? '';

    if (props.showKind && kinds.includes(params.get('kind') ?? '')) {
        form.kind = params.get('kind');
    }
});

const errorCount = computed(() => Object.keys(form.errors).length);
const today = computed(() => new Date().toISOString().slice(0, 10));

function submit() {
    form.transform((data) => ({
        ...data,
        project_id: data.project_id === '' ? null : data.project_id,
        submission_token: page.props.site?.submission_token,
    })).post(`/${locale()}/enquiries`, {
        preserveScroll: true,
        onSuccess: () => {
            done.value = true;
            successMessage.value = page.props.flash?.success ?? t(`website.form_success_${form.kind}`);
            form.reset('name', 'mobile', 'email', 'message', 'preferred_date', 'consent');
        },
    });
}
</script>

<template>
    <div class="rounded-xl border border-border bg-surface p-6 shadow-sm sm:p-8">
        <div v-if="title || subtitle" class="mb-6">
            <h2 v-if="title" class="text-2xl font-semibold">{{ title }}</h2>
            <p v-if="subtitle" class="mt-2 text-[15px] leading-6 text-muted">{{ subtitle }}</p>
        </div>

        <div v-if="done" role="status" class="flex items-start gap-3 rounded-lg border border-success/30 bg-success/10 p-4 text-[15px]">
            <AppIcon name="circle-check" class="mt-1 size-5 text-success" />
            <p>{{ successMessage }}</p>
        </div>

        <form v-else class="space-y-4" novalidate @submit.prevent="submit">
            <p v-if="errorCount >= 3" role="alert" class="rounded-md border border-danger/30 bg-danger/10 px-3 py-2 text-sm text-danger">
                {{ t('website.form_error_summary', { count: errorCount }) }}
            </p>

            <FormField v-if="showKind" :label="t('website.form_kind')" :error="form.errors.kind">
                <select v-model="form.kind" class="site-input">
                    <option v-for="option in kinds" :key="option" :value="option">{{ t(`website.form_kind_${option}`) }}</option>
                </select>
            </FormField>

            <div class="grid gap-4 sm:grid-cols-2">
                <FormField :label="t('website.form_name')" :error="form.errors.name">
                    <input v-model="form.name" type="text" autocomplete="name" required class="site-input" :placeholder="t('website.form_name_placeholder')" />
                </FormField>
                <FormField :label="t('website.form_mobile')" :error="form.errors.mobile">
                    <input v-model="form.mobile" type="tel" inputmode="tel" autocomplete="tel" required class="site-input" :placeholder="t('website.form_mobile_placeholder')" />
                </FormField>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <FormField :label="`${t('website.form_email')} (${t('website.optional')})`" :error="form.errors.email">
                    <input v-model="form.email" type="email" autocomplete="email" class="site-input" :placeholder="t('website.form_email_placeholder')" />
                </FormField>
                <FormField v-if="showProject" :label="t('website.form_project')" :error="form.errors.project_id">
                    <select v-model="form.project_id" class="site-input">
                        <option value="">{{ t('website.form_project_any') }}</option>
                        <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                    </select>
                </FormField>
                <FormField v-if="form.kind === 'site_visit'" :label="t('website.form_preferred_date')" :error="form.errors.preferred_date">
                    <input v-model="form.preferred_date" type="date" :min="today" class="site-input" />
                </FormField>
            </div>

            <FormField :label="`${t('website.form_message')} (${t('website.optional')})`" :error="form.errors.message">
                <textarea v-model="form.message" rows="4" class="site-input py-2" :placeholder="t('website.form_message_placeholder')"></textarea>
            </FormField>

            <div class="hidden" aria-hidden="true">
                <label>
                    Company website
                    <input v-model="form.company_website" type="text" tabindex="-1" autocomplete="off" />
                </label>
            </div>

            <label class="flex items-start gap-3 text-sm">
                <input v-model="form.consent" type="checkbox" class="mt-1 size-4 shrink-0 rounded border-border" />
                <span>{{ t('website.form_consent') }}</span>
            </label>
            <p v-if="form.errors.consent" class="text-xs text-danger">{{ form.errors.consent }}</p>

            <button type="submit" class="btn-primary w-full sm:w-auto" :disabled="form.processing">
                <span v-if="form.processing">{{ t('website.form_sending') }}</span>
                <span v-else>{{ t('website.form_submit') }}</span>
                <AppIcon v-if="!form.processing" name="arrow-right" />
            </button>
        </form>
    </div>
</template>
