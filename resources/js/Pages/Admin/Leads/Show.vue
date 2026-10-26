<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FormField from '@/components/ui-kit/FormField.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';
import StatusBadge from '@/components/ui-kit/StatusBadge.vue';
import { useI18n } from '@/lib/i18n';

const props = defineProps({
    lead: { type: Object, required: true },
    activities: { type: Array, default: () => [] },
    visits: { type: Array, default: () => [] },
});

const { t } = useI18n();

const stageForm = useForm({ stage: props.lead.stage, lost_reason: '' });
const activityForm = useForm({ type: 'note', note: '', follow_up_at: '' });
const visitForm = useForm({ scheduled_at: '', complete: false, feedback: '' });

function moveStage() {
    stageForm.post(`/admin/leads/${props.lead.id}/stage`, { preserveScroll: true });
}

function logActivity() {
    activityForm.post(`/admin/leads/${props.lead.id}/activity`, { preserveScroll: true, onSuccess: () => activityForm.reset() });
}

function scheduleVisit() {
    visitForm.post(`/admin/leads/${props.lead.id}/visits`, { preserveScroll: true, onSuccess: () => visitForm.reset() });
}
</script>

<template>
    <AdminLayout>
        <PageHeader :title="lead.name" :description="`${lead.mobile} - ${lead.source}`">
            <StatusBadge :label="t(`leads.stages.${lead.stage}`)" tone="info" icon="phone" />
        </PageHeader>

        <div class="grid gap-3 lg:grid-cols-2">
            <section class="rounded-lg border border-border bg-surface p-3">
                <h2 class="mb-2 text-sm font-semibold">{{ t('leads.move_stage') }}</h2>
                <div class="grid grid-cols-2 gap-3">
                    <FormField :label="t('leads.stage')">
                        <select v-model="stageForm.stage" class="h-7 w-full rounded-md border border-border px-2 text-[13px]">
                            <option v-for="s in ['new', 'contacted', 'visit_planned', 'visited', 'negotiation', 'booked', 'lost']" :key="s" :value="s">
                                {{ t(`leads.stages.${s}`) }}
                            </option>
                        </select>
                    </FormField>
                    <FormField :label="t('leads.lost_reason')" :error="stageForm.errors.lost_reason">
                        <input v-model="stageForm.lost_reason" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
                    </FormField>
                </div>
                <button type="button" class="mt-2 h-7 rounded-md bg-primary px-2.5 text-[13px] text-primary-foreground" :disabled="stageForm.processing" @click="moveStage">
                    {{ t('ui.save') }}
                </button>
            </section>

            <section class="rounded-lg border border-border bg-surface p-3">
                <h2 class="mb-2 text-sm font-semibold">{{ t('leads.log_activity') }}</h2>
                <div class="grid grid-cols-2 gap-3">
                    <FormField :label="t('leads.note')">
                        <input v-model="activityForm.note" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
                    </FormField>
                    <FormField :label="t('leads.follow_up_at')">
                        <input v-model="activityForm.follow_up_at" type="datetime-local" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
                    </FormField>
                </div>
                <button type="button" class="mt-2 h-7 rounded-md bg-primary px-2.5 text-[13px] text-primary-foreground" :disabled="activityForm.processing" @click="logActivity">
                    {{ t('leads.log_activity') }}
                </button>
            </section>

            <section class="rounded-lg border border-border bg-surface p-3">
                <h2 class="mb-2 text-sm font-semibold">{{ t('leads.schedule_visit') }}</h2>
                <div class="grid grid-cols-2 gap-3">
                    <FormField :label="t('leads.schedule_visit')">
                        <input v-model="visitForm.scheduled_at" type="datetime-local" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
                    </FormField>
                    <FormField :label="t('leads.note')">
                        <input v-model="visitForm.feedback" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" />
                    </FormField>
                </div>
                <label class="mt-2 flex items-center gap-2 text-xs">
                    <input v-model="visitForm.complete" type="checkbox" class="size-4" />
                    {{ t('leads.complete_visit') }}
                </label>
                <button type="button" class="mt-2 h-7 rounded-md bg-primary px-2.5 text-[13px] text-primary-foreground" :disabled="visitForm.processing" @click="scheduleVisit">
                    {{ t('leads.schedule_visit') }}
                </button>
            </section>

            <section class="rounded-lg border border-border bg-surface p-3">
                <h2 class="mb-2 text-sm font-semibold">{{ t('leads.timeline') }}</h2>
                <ul class="space-y-1 text-[13px]">
                    <li v-for="activity in activities" :key="activity.id" class="border-b border-border pb-1 last:border-0">
                        <span class="font-medium">{{ activity.type }}</span>
                        <span class="ml-2 text-muted">{{ activity.note }}</span>
                        <span class="ml-2 text-xs text-muted">{{ activity.created_at }}</span>
                    </li>
                </ul>
                <h3 class="mb-1 mt-3 text-xs font-semibold text-muted">{{ t('leads.schedule_visit') }}</h3>
                <ul class="space-y-1 text-[13px]">
                    <li v-for="visit in visits" :key="visit.id">
                        {{ visit.scheduled_at }}
                        <StatusBadge v-if="visit.completed" :label="t('leads.visit_completed')" tone="success" icon="circle-check" class="ml-2" />
                    </li>
                </ul>
            </section>
        </div>
    </AdminLayout>
</template>
