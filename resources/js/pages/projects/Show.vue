<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Clock, Pencil, User } from 'lucide-vue-next';
import { ref } from 'vue';
import ProjectFormDialog from '@/components/projects/ProjectFormDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/date';
import { index } from '@/routes/projects';
import type { Project, ProjectStatusOption } from '@/types/project';

defineProps<{
    project: Project;
    statuses: ProjectStatusOption[];
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

const isFormDialogOpen = ref(false);

function getStatusLabel(status: Project['status']) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function getStatusVariant(status: Project['status']) {
    if (status === 'completed') {
        return 'default';
    }

    if (status === 'archived') {
        return 'outline';
    }

    return 'secondary';
}
</script>
<template>
    <Head :title="project.name" />

    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <Button variant="ghost" size="sm" as-child>
                <Link :href="index().url">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Projects
                </Link>
            </Button>
            <Button
                variant="outline"
                size="sm"
                @click="isFormDialogOpen = true"
            >
                <Pencil class="mr-2 h-4 w-4" />
                Edit Project
            </Button>
        </div>
        <div
            class="flex flex-col gap-4 rounded-xl border p-6 md:flex-row md:items-start md:justify-between"
        >
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold">{{ project.name }}</h1>

                    <Badge :variant="getStatusVariant(project.status)">{{
                        getStatusLabel(project.status)
                    }}</Badge>
                </div>

                <p
                    v-if="project.description"
                    class="max-w-2xl text-sm text-muted-foreground"
                >
                    {{ project.description || 'No description yet.' }}
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
                <p class="mt-2 font-medium">
                    {{ formatDate(project.due_date) }}
                </p>
            </div>
            <div class="rounded-xl border p-5">
                <p class="text-sm text-muted-foreground">Created</p>
                <p class="mt-2 font-medium">
                    {{ formatDate(project.created_at) }}
                </p>
            </div>
        </div>
    </div>

    <ProjectFormDialog
        v-model:open="isFormDialogOpen"
        :project="project"
        :statuses="statuses"
    />
</template>
