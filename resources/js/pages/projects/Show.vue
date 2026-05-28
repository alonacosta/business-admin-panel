<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Clock,
    CheckCircle2,
    Circle,
    Pencil,
    User,
    Edit,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import ProjectFormDialog from '@/components/projects/ProjectFormDialog.vue';
import TaskFormDialog from '@/components/projects/TaskFormDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/date';
import { index } from '@/routes/projects';
import { destroy, update } from '@/routes/projects/tasks';
import type {
    Project,
    ProjectStatusOption,
    Task,
    TaskStatusOption,
} from '@/types/project';

const props = defineProps<{
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
const selectedTask = ref<Task | null>(null);
const selectedTaskStatus = ref<Task['status'] | 'all'>('all');

const taskCounts = computed(() => {
    const tasks = props.project.tasks ?? [];

    return {
        total: tasks.length,
        todo: tasks.filter((task) => task.status === 'todo').length,
        inProgress: tasks.filter((task) => task.status === 'in_progress').length,
        completed: tasks.filter((task) => task.status === 'completed').length,
    };
});

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

const filteredTasks = computed(() => {
    if (selectedTaskStatus.value === 'all') {
        return props.project.tasks ?? [];
    }

    return (props.project.tasks ?? []).filter(
        (task) => task.status === selectedTaskStatus.value,
    );
});

function openCreateTaskDialog() {
    selectedTask.value = null;
    isTaskFormDialogOpen.value = true;
}

function openEditTaskDialog(task: Task) {
    selectedTask.value = task;
    isTaskFormDialogOpen.value = true;
}

function deleteTask(task: Task) {
    router.delete(
        destroy({
            project: props.project.id,
            task: task.id,
        }).url,
        {
            preserveScroll: true,
            onSuccess: () => toast.success('Task deleted successfully'),
        },
    );
}

function toggleTaskCompleted(task: Task) {
    router.put(
        update({
            project: props.project.id,
            task: task.id,
        }).url,
        {
            title: task.title,
            description: task.description ?? '',
            status: task.status === 'completed' ? 'todo' : 'completed',
            due_date: task.due_date,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                toast.success(
                    task.status === 'completed'
                        ? 'Task marked as todo'
                        : 'Task completed',
                );
            },
            onError: () => {
                toast.error('Failed to update task status');
            },
        },
    );
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

                <p class="max-w-2xl text-sm text-muted-foreground">
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
                <Button
                    variant="outline"
                    size="sm"
                    @click="openCreateTaskDialog"
                    >Add task
                </Button>
            </div>
            <div class="mt-6 flex flex-wrap gap-2">
                <Button
                    size="sm"
                    :variant="
                        selectedTaskStatus === 'all' ? 'default' : 'outline'
                    "
                    @click="selectedTaskStatus = 'all'"
                    >All ({{ taskCounts.total }})</Button
                >
                <Button
                    v-for="status in taskStatuses"
                    :key="status.value"
                    size="sm"
                    :variant="
                        selectedTaskStatus === status.value
                            ? 'default'
                            : 'outline'
                    "
                    @click="selectedTaskStatus = status.value"
                    >{{ status.label }}
                    ({{ status.value === 'todo'
                    ? taskCounts.todo
                        : status.value === 'in_progress'
                    ? taskCounts.inProgress
                            : taskCounts.completed
                    }})
                </Button
                >
            </div>
            <div v-if="filteredTasks?.length" class="mt-6 space-y-3">
                <div
                    v-for="task in filteredTasks"
                    :key="task.id"
                    class="flex items-start justify-between gap-2 rounded-lg border p-4"
                >
                    <div class="flex min-w-0 flex-1 items-start gap-2">
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="h-7 w-7 shrink-0 rounded-full"
                            @click="toggleTaskCompleted(task)"
                        >
                            <CheckCircle2
                                v-if="task.status === 'completed'"
                                class="h-5 w-5 text-green-600"
                            />
                            <Circle
                                v-else
                                class="h-5 w-5 text-muted-foreground"
                            />
                        </Button>

                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="min-w-0 flex-1 space-y-1">
                                    <h3 class="font-medium">
                                        {{ task.title }}
                                    </h3>
                                    <Badge
                                        :variant="
                                            getTaskStatusVariant(task.status)
                                        "
                                    >
                                        {{ getTaskStatusLabel(task.status) }}
                                    </Badge>
                                </div>
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

                    <div class="flex items-center gap-1">
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8"
                            @click="openEditTaskDialog(task)"
                        >
                            <Edit class="h-4 w-4" />
                        </Button>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 text-destructive hover:text-destructive/90"
                            @click="deleteTask(task)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
            <div
                v-else
                class="mt-6 rounded-lg border border-dashed p-8 text-center"
            >
                <p class="text-sm font-medium">No task yet</p>
                <p class="mt-1 text-sm text-muted-foreground">
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
        :task="selectedTask"
        :task-statuses="taskStatuses"
    />
</template>
