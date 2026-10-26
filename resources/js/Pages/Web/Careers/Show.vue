<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import AppIcon from '@/components/ui-kit/AppIcon.vue';
import FormField from '@/components/ui-kit/FormField.vue';
import PageHero from '@/components/website/PageHero.vue';
import { formatLongDate, localizeDigits } from '@/lib/format';
import { useI18n } from '@/lib/i18n';

defineOptions({ layout: WebsiteLayout });

const props = defineProps({
    job: { type: Object, required: true },
    breadcrumbs: { type: Array, default: () => [] },
});

const page = usePage();
const { t, locale } = useI18n();
const careersHref = computed(() => page.props.site?.nav?.find((item) => item.key === 'careers')?.href ?? '/');
const done = ref(false);
const successMessage = ref('');

const form = useForm({
    name: '',
    mobile: '',
    email: '',
    cover_letter: '',
    cv: null,
    consent: false,
    company_website: '',
});

function submit() {
    form.post(`/${locale()}/careers/${props.job.slug}/apply`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            done.value = true;
            successMessage.value = page.props.flash?.success ?? t('website.careers_applied');
        },
    });
}

const facts = computed(() => [
    { icon: 'location-dot', label: t('website.fact_location'), value: props.job.location },
    { icon: 'briefcase', label: t('website.employment_type'), value: props.job.employment_type_label },
    { icon: 'user-tie', label: t('website.experience'), value: props.job.experience },
    { icon: 'money-bill-wave', label: t('website.salary'), value: props.job.salary_range },
    { icon: 'users', label: t('website.open_positions'), value: t('website.vacancies', { count: localizeDigits(props.job.vacancies, locale()) }) },
    { icon: 'calendar-days', label: t('website.deadline_label'), value: props.job.deadline ? formatLongDate(props.job.deadline, locale()) : null },
].filter((fact) => fact.value));
</script>

<template>
    <PageHero :title="job.title" :subtitle="job.summary" :eyebrow="job.department_label" :breadcrumbs="breadcrumbs">
        <div class="flex flex-wrap gap-3">
            <a v-if="job.is_open" href="#apply" class="btn-light">
                {{ t('website.apply_now') }}
                <AppIcon name="arrow-right" />
            </a>
            <span v-else class="rounded-md bg-white/15 px-4 py-2.5 text-sm font-semibold">{{ t('website.applications_closed') }}</span>
            <Link :href="careersHref" class="btn-outline-light">
                <AppIcon name="arrow-left" />
                {{ t('website.back_to_careers') }}
            </Link>
        </div>
    </PageHero>

    <div class="site-container grid gap-10 py-16 lg:grid-cols-12">
        <article class="space-y-10 lg:col-span-8">
            <dl class="grid grid-cols-2 gap-4 rounded-xl border border-border bg-background p-5 sm:grid-cols-3">
                <div v-for="fact in facts" :key="fact.label + fact.value" class="flex items-start gap-2">
                    <AppIcon :name="fact.icon" class="mt-1 text-primary" />
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">{{ fact.label }}</dt>
                        <dd class="text-sm font-semibold">{{ fact.value }}</dd>
                    </div>
                </div>
            </dl>

            <section>
                <h2 class="text-2xl font-semibold">{{ t('website.job_description') }}</h2>
                <div class="prose-site mt-4" v-html="job.description"></div>
            </section>

            <section v-if="job.requirements?.length">
                <h2 class="text-2xl font-semibold">{{ t('website.requirements') }}</h2>
                <ul class="mt-4 space-y-2">
                    <li v-for="(item, index) in job.requirements" :key="index" class="flex items-start gap-3 text-[15px] leading-6">
                        <AppIcon name="check" class="mt-1.5 size-3.5 text-success" />
                        <span>{{ item }}</span>
                    </li>
                </ul>
            </section>

            <section v-if="job.benefits?.length">
                <h2 class="text-2xl font-semibold">{{ t('website.benefits') }}</h2>
                <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                    <li v-for="(item, index) in job.benefits" :key="index" class="flex items-start gap-3 rounded-lg border border-border bg-surface p-4 text-[15px]">
                        <AppIcon name="award" class="mt-1 text-primary" />
                        <span>{{ item }}</span>
                    </li>
                </ul>
            </section>
        </article>

        <aside id="apply" class="lg:col-span-4">
            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm lg:sticky lg:top-24">
                <h2 class="text-2xl font-semibold">{{ t('website.apply_title') }}</h2>
                <p class="mt-2 text-[15px] text-muted">{{ t('website.apply_subtitle') }}</p>

                <div v-if="!job.is_open" class="mt-5 rounded-lg border border-warning/40 bg-warning/10 p-4 text-[15px]">{{ t('website.applications_closed') }}</div>

                <div v-else-if="done" role="status" class="mt-5 flex items-start gap-3 rounded-lg border border-success/30 bg-success/10 p-4 text-[15px]">
                    <AppIcon name="circle-check" class="mt-1 size-5 text-success" />
                    <p>{{ successMessage }}</p>
                </div>

                <form v-else class="mt-5 space-y-4" novalidate @submit.prevent="submit">
                    <FormField :label="t('website.form_name')" :error="form.errors.name">
                        <input v-model="form.name" type="text" autocomplete="name" required class="site-input" />
                    </FormField>
                    <FormField :label="t('website.form_mobile')" :error="form.errors.mobile">
                        <input v-model="form.mobile" type="tel" inputmode="tel" autocomplete="tel" required class="site-input" :placeholder="t('website.form_mobile_placeholder')" />
                    </FormField>
                    <FormField :label="`${t('website.form_email')} (${t('website.optional')})`" :error="form.errors.email">
                        <input v-model="form.email" type="email" autocomplete="email" class="site-input" />
                    </FormField>
                    <FormField :label="t('website.careers_cv')" :error="form.errors.cv" :hint="t('website.careers_cv_hint')">
                        <input type="file" accept=".pdf,.doc,.docx" required class="site-input py-2 text-sm" @change="form.cv = $event.target.files[0] ?? null" />
                    </FormField>
                    <FormField :label="`${t('website.careers_cover_letter')} (${t('website.optional')})`" :error="form.errors.cover_letter">
                        <textarea v-model="form.cover_letter" rows="4" class="site-input py-2"></textarea>
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
                        {{ form.processing ? t('website.form_sending') : t('website.submit_application') }}
                    </button>
                </form>
            </div>
        </aside>
    </div>
</template>
