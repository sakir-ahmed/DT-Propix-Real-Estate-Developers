<script setup>
import AppIcon from './AppIcon.vue';

const model = defineModel({ type: Number, default: 1 });

function change(delta) {
    model.value = Math.max(0, Number(model.value) + delta);
}
</script>

<template>
    <div class="inline-flex h-7 items-center overflow-hidden rounded-md border border-border">
        <button type="button" class="inline-flex size-7 items-center justify-center" aria-label="Decrease" @click="change(-1)">
            <AppIcon name="arrow-down" />
        </button>
        <span class="min-w-8 text-center text-[13px] tabular-nums">{{ model }}</span>
        <button type="button" class="inline-flex size-7 items-center justify-center" aria-label="Increase" @click="change(1)">
            <AppIcon name="arrow-up" />
        </button>
    </div>
</template>
