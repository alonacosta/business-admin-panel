<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Clock, Pencil, User } from 'lucide-vue-next';
import { ref } from 'vue';
import ProjectFormDialog from '@/components/projects/ProjectFormDialog.vue';
import TaskFormDialog from '@/components/projects/TaskFormDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/date';
import { index } from '@/routes/projects';
import type {
    Project,
    ProjectStatusOption,
    Task,
    TaskStatusOption,
} from '@/types/project';

defineProps<{
    project: Project;
    statuses: ProjectStatusOption[];
    taskStatuses: TaskStatusOption[];
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
const isTaskFormDialogOpen = ref(false);

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

function getTaskStatusLabel(status: Task['status']) {
    return status
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function getTaskStatusVariant(status: Task['status']) {
    if (status === 'completed') {
        return 'default';
    }

    if (status === 'in_progress') {
        return 'secondary';
    }

    return 'outline';
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
                    class="max-w-2xl text-sm text-muted-foreground"
                >
                    {{ project.description || 'No description yet.' }}
                </p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border p-5">
                <div
                    class="flex items-center gap-2 text-sm text-muted-foreground"
                >
                    <User class="h-4 w-4" />
                    <span>Owner</span>
                </div>
                <p class="mt-2 font-medium">{{ project.owner?.name ?? '-' }}</p>
                <p class="text-sm text-muted-foreground">
                    {{ project.owner?.email ?? '-' }}
                </p>
            </div>
            <div class="rounded-xl border p-5">
                <div
                    class="flex items-center gap-2 text-sm text-muted-foreground"
                >
                    <Calendar class="h-4 w-4" />
                    <span>Due Date</span>
                </div>
                <p class="mt-2 font-medium">
                    {{ formatDate(project.due_date) }}
                </p>
            </div>
            <div class="rounded-xl border p-5">
                <div
                    class="flex items-center gap-2 text-sm text-muted-foreground"
                >
                    <Clock class="h-4 w-4" />
                    <span>Created</span>
                </div>
                <p class="mt-2 font-medium">
                    {{ formatDate(project.created_at) }}
                </p>
            </div>
        </div>
        <div class="rounded-xl border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Project tasks</h2>
                    <p class="text-sm text-muted-foreground">
                        Tasks for this project will appear here.
                    </p>
                </div>
                <Button variant="outline" size="sm" @click="isTaskFormDialogOpen = true">Add task </Button>
            </div>
            <div v-if="project.tasks?.length" class="mt-6 space-y-3">
                <div
                    v-for="task in project.tasks"
                    :key="task.id"
                    class="flex items-center justify-between rounded-lg border p-4"
                >
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-medium">
                                {{ task.title }}
                            </h3>
                            <Badge :variant="getTaskStatusVariant(task.status)">
                                {{ getTaskStatusLabel(task.status) }}
                            </Badge>
                        </div>
                        <p
                            v-if="task.description"
                            class="text-sm text-muted-foreground"
                        >
                            {{ task.description }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            Due: {{ formatDate(task.due_date) }}
                        </p>
                    </div>
                </div>
            </div>
            <div
                v-else
                class="mt-6 rounded-lg border border-dashed p-8 text-center"
            >
                <p class="font-medium text-sm">No task yet</p>
                <p class="text-muted-foreground mt-1 text-sm">
                    Create tasks later to break this project into smaller steps.
                </p>
            </div>
        </div>
    </div>

    <ProjectFormDialog
        v-model:open="isFormDialogOpen"
        :project="project"
        :statuses="statuses"
    />

    <TaskFormDialog
        v-model:open="isTaskFormDialogOpen"
        :project="project"
        :task-statuses="taskStatuses"
    />
</template>
