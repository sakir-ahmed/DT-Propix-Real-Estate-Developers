<script setup>
import { computed } from 'vue';
import { useI18n } from '@/lib/i18n';
import MoneyText from './MoneyText.vue';
import DateText from './DateText.vue';
import AppIcon from './AppIcon.vue';

const props = defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    error: { type: Boolean, default: false },
});

defineEmits(['retry']);

const { t } = useI18n();

const align = {
    text: 'text-left',
    status: 'text-left',
    date: 'text-left',
    number: 'text-right',
    money: 'text-right',
    actions: 'text-right',
};

const empty = computed(() => !props.loading && !props.error && props.rows.length === 0);
</script>

<template>
    <div class="overflow-x-auto rounded-lg border border-border bg-surface">
        <table class="w-full border-collapse text-[13px]">
            <thead>
                <tr class="h-8 border-b border-border text-xs font-semibold text-muted">
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        class="px-3"
                        :class="align[column.type] ?? 'text-left'"
                    >
                        {{ column.label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="loading">
                    <td :colspan="columns.length" class="px-3 py-4 text-muted">{{ t('ui.loading') }}</td>
                </tr>
                <tr v-else-if="error">
                    <td :colspan="columns.length" class="px-3 py-4">
                        <button type="button" class="h-7 rounded-md border border-border px-2.5" @click="$emit('retry')">{{ t('ui.retry') }}</button>
                    </td>
                </tr>
                <tr v-else-if="empty">
                    <td :colspan="columns.length" class="px-3 py-6 text-center text-muted">{{ t('ui.empty') }}</td>
                </tr>
                <tr v-for="(row, index) in rows" v-else :key="index" class="h-8 border-b border-border last:border-0">
                    <td
                        v-for="column in columns"
                        :key="column.key"
                        class="px-3 align-middle"
                        :class="align[column.type] ?? 'text-left'"
                    >
                        <slot :name="`cell-${column.key}`" :row="row" :index="index">
                            <MoneyText v-if="column.type === 'money'" :amount="row[column.key]" />
                            <DateText v-else-if="column.type === 'date'" :value="row[column.key]" />
                            <span v-else-if="column.type === 'actions'" class="inline-flex">
                                <button type="button" class="inline-flex size-7 items-center justify-center" :aria-label="t('ui.search')">
                                    <AppIcon name="ellipsis" />
                                </button>
                            </span>
                            <span v-else class="whitespace-nowrap">{{ row[column.key] }}</span>
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
