<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import type { User } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { LogOut, Settings, UserX } from 'lucide-vue-next';
import { trackEvent } from '@/lib/tracking'; // Import the tracking helper
import { useInitials } from '@/composables/useInitials';

interface Props {
    user: User;
    tenantSlug?: string | null;
}
const props = withDefaults(defineProps<Props>(), {
    tenantSlug: null, 
});

const page = usePage();
const tenantSlug = page.props.tenantSlug as string | null;
const impersonated = computed(() => page.props.impersonated);

// Function to handle logout events and session time tracking.
const handleLogout = () => {
    try {
        // Retrieve the session start time from localStorage.
        const sessionStart = Number(localStorage.getItem('sessionStart') || '0');
        
        // Only track if we have a valid session start time
        if (sessionStart > 0) {
            // Calculate the session duration (in milliseconds).
            const sessionDuration = Date.now() - sessionStart;
            
            // Send a session end tracking event.
            trackEvent('sessionEnd', {
                sessionDuration, // in milliseconds
                timestamp: new Date().toISOString(),
                userId: page.props.auth?.user?.id,
            });
            
            // Remove the session start time from localStorage.
            localStorage.removeItem('sessionStart');
        }
        
        // Send the logout tracking event.
        trackEvent('userLogout', {
            timestamp: new Date().toISOString(),
            userId: page.props.auth?.user?.id,
        });
    } catch (error) {
        console.error('Error tracking logout event:', error);
        // Continue with logout even if tracking fails
    }
};

const { getInitials } = useInitials();

// Compute whether we should show the avatar image
const showAvatar = computed(() => props.user.avatar && props.user.avatar !== '');
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <Avatar class=" inline-flex text-xs h-8 w-8 overflow-hidden rounded-lg bg-secondary items-center justify-center">
        <AvatarImage v-if="showAvatar" :src="user.avatar" :alt="user.name" />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>
            <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium ">{{ user.name }}</span>
        <span  class="truncate text-xs text-muted-foreground">{{ user.email }}</span>
    </div>
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator v-if="impersonated"/>
    <DropdownMenuGroup v-if="impersonated">
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('impersonate.stop', { tenantSlug })" as="button">
                <UserX class="mr-2 h-4 w-4" />
                Stop Impersonation
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full"
            method="post"
            :href="route('logout')"
            as="button"
            @click="handleLogout"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
