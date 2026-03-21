<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
</script>

<template>
    <AuthLayout
        title="Account pending approval"
        description="Your account has been created, but an administrator still needs to approve it before you can use the app."
    >
        <Head title="Pending approval" />

        <div class="space-y-6 text-center">
            <div class="rounded-lg border bg-muted/40 p-4 text-sm text-muted-foreground">
                EndUser accounts are disabled by default after signup. Once an administrator approves your account, you can access dashboard, expenses, accounts, and cutoff pages.
            </div>

            <Button as-child class="w-full">
                <Link :href="logout()" as="button">
                    Log out
                </Link>
            </Button>
        </div>
    </AuthLayout>
</template>
