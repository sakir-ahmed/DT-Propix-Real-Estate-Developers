<script setup>
import DateText from './DateText.vue';
import AppIcon from './AppIcon.vue';

defineProps({
    items: { type: Array, default: () => [] },
});
</script>

<template>
    <ol class="space-y-2">
        <li v-for="item in items" :key="item.title" class="flex gap-2 text-[13px]">
            <AppIcon name="circle-check" />
            <div>
                <p class="font-medium">{{ item.title }}</p>
                <DateText :value="item.at" />
            </div>
        </li>
    </ol>
</template>
