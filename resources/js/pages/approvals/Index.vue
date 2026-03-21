<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { BadgeCheck, Clock3, UserCheck } from 'lucide-vue-next';
import { update } from '@/actions/App/Http/Controllers/UserApprovalController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type ApprovalUser = {
    id: number;
    name: string;
    email: string;
    is_approved: boolean;
    created_at: string | null;
    email_verified_at: string | null;
};

defineProps<{
    users: ApprovalUser[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Approvals',
        href: '/approvals',
    },
];

const formatDate = (value: string | null): string => {
    if (value === null) {
        return 'Not available';
    }

    return new Intl.DateTimeFormat('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const toggleApproval = (user: ApprovalUser): void => {
    router.put(
        update.url(user.id),
        {
            is_approved: !user.is_approved,
        },
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <Head title="User Approvals" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <section class="overflow-hidden rounded-xl border bg-background">
                <div class="border-b px-4 py-4 md:px-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div class="space-y-1.5">
                            <div class="inline-flex items-center gap-2 text-xs font-medium text-primary">
                                <UserCheck class="size-4" />
                                End user approvals
                            </div>
                            <div>
                                <h1 class="text-lg font-semibold tracking-tight">
                                    Review pending accounts
                                </h1>
                                <p class="text-xs text-muted-foreground sm:text-sm">
                                    Only users with the EndUser role are listed here.
                                </p>
                            </div>
                        </div>

                        <Badge variant="secondary" class="w-fit px-3 py-1 text-xs">
                            {{ users.length }} end user{{ users.length === 1 ? '' : 's' }}
                        </Badge>
                    </div>
                </div>

                <div v-if="users.length === 0" class="px-4 py-10 text-center md:px-5">
                    <p class="text-sm font-medium">No end users found.</p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        New signups with the EndUser role will appear here.
                    </p>
                </div>

                <div v-else class="divide-y">
                    <div
                        v-for="user in users"
                        :key="user.id"
                        class="flex flex-col gap-4 px-4 py-4 md:px-5 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div class="min-w-0 space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-sm font-semibold">{{ user.name }}</h2>
                                <Badge
                                    :variant="user.is_approved ? 'default' : 'secondary'"
                                    class="gap-1"
                                >
                                    <BadgeCheck v-if="user.is_approved" class="size-3.5" />
                                    <Clock3 v-else class="size-3.5" />
                                    {{ user.is_approved ? 'Approved' : 'Pending' }}
                                </Badge>
                            </div>

                            <div class="space-y-1 text-sm text-muted-foreground">
                                <p>{{ user.email }}</p>
                                <p>Signed up: {{ formatDate(user.created_at) }}</p>
                                <p>
                                    Email verified:
                                    {{ user.email_verified_at ? formatDate(user.email_verified_at) : 'No' }}
                                </p>
                            </div>
                        </div>

                        <Button
                            type="button"
                            size="sm"
                            :variant="user.is_approved ? 'outline' : 'default'"
                            class="self-start lg:self-center"
                            @click="toggleApproval(user)"
                        >
                            {{ user.is_approved ? 'Set to pending' : 'Approve user' }}
                        </Button>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
