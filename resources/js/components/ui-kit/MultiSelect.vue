<script setup>
const props = defineProps({
    options: { type: Array, default: () => [] },
    label: { type: String, default: '' },
});

const model = defineModel({ type: Array, default: () => [] });

function toggle(value) {
    model.value = model.value.includes(value)
        ? model.value.filter((item) => item !== value)
        : [...model.value, value];
}
</script>

<template>
    <fieldset class="space-y-1">
        <legend v-if="label" class="text-xs font-medium">{{ label }}</legend>
        <label v-for="option in props.options" :key="option.value" class="flex h-7 items-center gap-2 text-[13px]">
            <input type="checkbox" class="size-4" :checked="model.includes(option.value)" @change="toggle(option.value)" />
            {{ option.label }}
        </label>
    </fieldset>
</template>
