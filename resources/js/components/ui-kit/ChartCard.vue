<script setup>
import { Line } from 'vue-chartjs';
import { CategoryScale, Chart as ChartJS, LinearScale, LineElement, PointElement } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement);

defineProps({
    title: { type: String, required: true },
    labels: { type: Array, default: () => ['Jan', 'Feb', 'Mar'] },
    values: { type: Array, default: () => [4, 8, 6] },
});
</script>

<template>
    <article class="rounded-lg border border-border bg-surface p-3">
        <h2 class="mb-2 text-sm font-semibold">{{ title }}</h2>
        <Line
            :data="{
                labels,
                datasets: [{ data: values, borderColor: '#38a8ff', tension: 0.3, pointRadius: 0 }],
            }"
            :options="{ plugins: { legend: { display: false } }, maintainAspectRatio: false }"
            style="height: 120px"
        />
    </article>
</template>
