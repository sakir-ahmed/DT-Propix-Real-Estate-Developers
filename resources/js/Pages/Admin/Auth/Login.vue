<script setup>
import { useForm } from '@inertiajs/vue3';
import { Toaster } from 'vue-sonner';
import FormField from '@/components/ui-kit/FormField.vue';
import LanguageSwitch from '@/components/ui-kit/LanguageSwitch.vue';
import ThemeToggle from '@/components/ui-kit/ThemeToggle.vue';
import { useI18n } from '@/lib/i18n';

defineProps({
    demoUsers: { type: Array, default: () => [] },
});

const { t } = useI18n();
const form = useForm({
    login: '',
    password: '',
});
const demo = useForm({
    user_id: null,
});

function submit() {
    form.post('/admin/login');
}

function loginAs(userId) {
    demo.user_id = userId;
    demo.post('/admin/login/demo');
}
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center gap-4 bg-background px-4 py-8">
        <Toaster position="top-right" />
        <form class="w-full max-w-sm space-y-3 rounded-lg border border-border bg-surface p-4" @submit.prevent="submit">
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-semibold">{{ t('auth.login_title') }}</h1>
                <div class="flex gap-2">
                    <LanguageSwitch />
                    <ThemeToggle />
                </div>
            </div>
            <FormField :label="t('auth.login_label')" :error="form.errors.login">
                <input v-model="form.login" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" autocomplete="username" />
            </FormField>
            <FormField :label="t('auth.password')" :error="form.errors.password">
                <input v-model="form.password" type="password" class="h-7 w-full rounded-md border border-border px-2 text-[13px]" autocomplete="current-password" />
            </FormField>
            <button type="submit" class="h-7 rounded-md bg-primary px-2.5 text-[13px] text-primary-foreground" :disabled="form.processing">
                {{ t('auth.submit') }}
            </button>
        </form>

        <section v-if="demoUsers.length" class="w-full max-w-3xl">
            <h2 class="mb-2 text-[13px] font-semibold">{{ t('auth.demo_title') }}</h2>
            <div class="overflow-x-auto rounded-lg border border-border bg-surface">
                <table class="w-full border-collapse text-[13px]">
                    <thead>
                        <tr class="h-8 border-b border-border text-left text-xs font-semibold text-muted">
                            <th class="px-3">{{ t('auth.demo_name') }}</th>
                            <th class="px-3">{{ t('auth.demo_username') }}</th>
                            <th class="px-3">{{ t('auth.demo_email') }}</th>
                            <th class="px-3 text-right">{{ t('auth.demo_login') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in demoUsers" :key="user.id" class="h-8 border-b border-border last:border-0">
                            <td class="px-3">{{ user.name }}</td>
                            <td class="px-3">{{ user.username }}</td>
                            <td class="px-3">{{ user.email }}</td>
                            <td class="px-3 text-right">
                                <button
                                    type="button"
                                    class="h-7 rounded-md bg-primary px-2.5 text-[13px] text-primary-foreground"
                                    :disabled="demo.processing"
                                    @click="loginAs(user.id)"
                                >
                                    {{ t('auth.demo_login') }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
