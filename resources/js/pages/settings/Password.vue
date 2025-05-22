<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Eye, EyeOff } from 'lucide-vue-next';

// Get tenantSlug from page props if it's not passed as a prop
const page = usePage<SharedData>();

interface Props {
    tenantSlug?: string;
    permissions?: string[];
}

const props = withDefaults(defineProps<Props>(), {
    tenantSlug: undefined,
});


// Make breadcrumbItems reactive with computed property
const breadcrumbItems = computed(() => [
    {
        title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
        href: props.tenantSlug ? route('dashboard', { tenantSlug: props.tenantSlug }) : route('admin.dashboard'),
    },
]);

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

// Add password visibility state
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update', props.tenantSlug ? { tenantSlug: props.tenantSlug } : {}), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors: any) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                if (passwordInput.value instanceof HTMLInputElement) {
                    passwordInput.value.focus();
                }
            }

            if (errors.current_password) {
                form.reset('current_password');
                if (currentPasswordInput.value instanceof HTMLInputElement) {
                    currentPasswordInput.value.focus();
                }
            }
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems" :tenantSlug="props.tenantSlug">
        <Head title="Password settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Update password" description="Ensure your account is using a long, random password to stay secure" />

                <form @submit.prevent="updatePassword" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="current_password">Current password</Label>
                        <div class="relative">
                            <Input
                                id="current_password"
                                ref="currentPasswordInput"
                                v-model="form.current_password"
                                :type="showCurrentPassword ? 'text' : 'password'"
                                class="mt-1 block w-full pr-10"
                                autocomplete="current-password"
                                placeholder="Current password"
                            />
                            <button 
                                type="button" 
                                @click="showCurrentPassword = !showCurrentPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                            >
                                <Eye v-if="!showCurrentPassword" class="h-5 w-5" />
                                <EyeOff v-else class="h-5 w-5" />
                            </button>
                        </div>
                        <InputError :message="form.errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">New password</Label>
                        <div class="relative">
                            <Input
                                id="password"
                                ref="passwordInput"
                                v-model="form.password"
                                :type="showNewPassword ? 'text' : 'password'"
                                class="mt-1 block w-full pr-10"
                                autocomplete="new-password"
                                placeholder="New password"
                            />
                            <button 
                                type="button" 
                                @click="showNewPassword = !showNewPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                            >
                                <Eye v-if="!showNewPassword" class="h-5 w-5" />
                                <EyeOff v-else class="h-5 w-5" />
                            </button>
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirm password</Label>
                        <div class="relative">
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                class="mt-1 block w-full pr-10"
                                autocomplete="new-password"
                                placeholder="Confirm password"
                            />
                            <button 
                                type="button" 
                                @click="showPasswordConfirmation = !showPasswordConfirmation"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                            >
                                <Eye v-if="!showPasswordConfirmation" class="h-5 w-5" />
                                <EyeOff v-else class="h-5 w-5" />
                            </button>
                        </div>
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save password</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
