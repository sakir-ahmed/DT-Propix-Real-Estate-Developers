<script setup>
import DataTable from './DataTable.vue';

defineProps({
    rows: { type: Array, default: () => [] },
});

const columns = [
    { key: 'no', label: 'No', type: 'text' },
    { key: 'due', label: 'Due', type: 'date' },
    { key: 'amount', label: 'Amount', type: 'money' },
];
</script>

<template>
    <DataTable :columns="columns" :rows="rows" />
</template>
