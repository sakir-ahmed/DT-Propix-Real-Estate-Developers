<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/components/ui-kit/PageHeader.vue';

defineProps({
    branch: { type: Object, required: true },
});
</script>

<template>
    <AdminLayout>
        <PageHeader :title="branch.name" :description="branch.code" />
    </AdminLayout>
</template>
