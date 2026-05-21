<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { formatDate } from '@/lib/date';
import type { Project } from '@/types/project';

defineProps<{
    project: Project;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Projects',
                href: '/projects',
            },
            {
                title: 'Project',
                href: '#',
            },
        ],
    },
});

function getStatusLabel(status: Project['status']) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}
</script>
<template>
    <Head :title="project.name" />

    <div class="space-y-6 p-6">
        <div
            class="flex flex-col gap-4 rounded-xl border p-6 md:flex-row md:items-start md:justify-between"
        >
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold">{{ project.name }}</h1>

                    <Badge variant="secondary">{{
                        getStatusLabel(project.status)
                    }}</Badge>
                </div>

                <p
                    v-if="project.description"
                    class="max-w-2xl text-sm text-muted-foreground"
                >
                    {{ project.description }}
                </p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border p-5">
                <p class="text-sm text-muted-foreground">Owner</p>
                <p class="mt-2 font-medium">{{ project.owner?.name ?? '-' }}</p>
                <p class="text-sm text-muted-foreground">
                    {{ project.owner?.email ?? '-' }}
                </p>
            </div>
            <div class="rounded-xl border p-5">
                <p class="text-sm text-muted-foreground">Due Date</p>
                <p class="font-medium mt-2">
                    {{ formatDate(project.due_date) }}
                </p>
            </div>
            <div class="rounded-xl border p-5">
                <p class="text-sm text-muted-foreground">Created</p>
                <p class="font-medium mt-2">
                    {{ formatDate(project.created_at) }}
                </p>
            </div>
        </div>
    </div>
</template>
