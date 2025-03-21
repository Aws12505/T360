<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import type { User } from '@/types';
import { Link, usePage} from '@inertiajs/vue3';
import { computed } from 'vue';
import { LogOut, Settings,UserX } from 'lucide-vue-next';

interface Props {
    user: User;
}

defineProps<Props>();
    const page = usePage();
    const impersonated = computed(() => page.props.impersonated);
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('profile.edit')" as="button">
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator v-if="impersonated"/>
    <DropdownMenuGroup v-if="impersonated">
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('impersonate.stop')" as="button">
                <UserX class="mr-2 h-4 w-4" />
                Stop Impersonation
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link class="block w-full" method="post" :href="route('logout')" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
