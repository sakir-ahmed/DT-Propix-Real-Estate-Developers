<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import FormField from '@/components/ui-kit/FormField.vue';
import { useI18n } from '@/lib/i18n';

const props = defineProps({
    projectId: { type: Number, required: true },
});

const page = usePage();
const { t, locale } = useI18n();
const downloadUrl = ref('');
const done = ref(false);

const form = useForm({
    project_id: props.projectId,
    name: '',
    mobile: '',
    email: '',
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
});

function submit() {
    form.transform((data) => ({ ...data, submission_token: page.props.site?.submission_token })).post(`/${locale()}/brochure-requests`, {
        preserveScroll: true,
        onSuccess: () => {
            done.value = true;
            downloadUrl.value = page.props.flash?.download_url ?? '';
        },
    });
}
</script>

<template>
    <div class="rounded-xl border border-primary/30 bg-primary/5 p-6">
        <div class="flex items-center gap-3">
            <AppIcon name="file-pdf" class="size-6 text-primary" />
            <h2 class="text-xl font-semibold">{{ t('website.brochure_title') }}</h2>
        </div>
        <p class="mt-2 text-[15px] text-muted">{{ t('website.brochure_subtitle') }}</p>

        <div v-if="done" role="status" class="mt-4 rounded-lg border border-success/30 bg-success/10 p-4">
            <p class="font-semibold">{{ t('website.brochure_ready') }}</p>
            <a v-if="downloadUrl" :href="downloadUrl" class="btn-primary mt-3">
                <AppIcon name="download" />
                {{ t('website.brochure_download_now') }}
            </a>
        </div>

        <form v-else class="mt-4 space-y-3" novalidate @submit.prevent="submit">
            <FormField :label="t('website.form_name')" :error="form.errors.name">
                <input v-model="form.name" type="text" autocomplete="name" required class="site-input" />
            </FormField>
            <FormField :label="t('website.form_mobile')" :error="form.errors.mobile">
                <input v-model="form.mobile" type="tel" inputmode="tel" autocomplete="tel" required class="site-input" :placeholder="t('website.form_mobile_placeholder')" />
            </FormField>
            <FormField :label="`${t('website.form_email')} (${t('website.optional')})`" :error="form.errors.email">
                <input v-model="form.email" type="email" autocomplete="email" class="site-input" />
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
            <button type="submit" class="btn-primary w-full" :disabled="form.processing">
                <AppIcon name="download" />
                {{ form.processing ? t('website.form_sending') : t('website.brochure_button') }}
            </button>
        </form>
    </div>
</template>
