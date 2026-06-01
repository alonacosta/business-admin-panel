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
import { VueDraggable } from 'vue-draggable-plus';
import { toast } from 'vue-sonner';
import ProjectFormDialog from '@/components/projects/ProjectFormDialog.vue';
import TaskFormDialog from '@/components/projects/TaskFormDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import {
    Select,
    SelectItem,
    SelectTrigger,
    SelectValue,
    SelectContent,
} from '@/components/ui/select';
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
const selectedTaskSort = ref<'status' | 'due_date' | 'newest' | 'oldest'>(
    'status',
);
const viewMode = ref<'list' | 'board'>('list');

const taskCounts = computed(() => {
    const tasks = props.project.tasks ?? [];

    return {
        total: tasks.length,
        todo: tasks.filter((task) => task.status === 'todo').length,
        inProgress: tasks.filter((task) => task.status === 'in_progress')
            .length,
        completed: tasks.filter((task) => task.status === 'completed').length,
    };
});

const projectProgress = computed(() => {
    if (taskCounts.value.total === 0) {
        return 0;
    }

    return Math.round(
        (taskCounts.value.completed / taskCounts.value.total) * 100,
    );
});

const boardTasks = computed(() => {
    const tasks = props.project.tasks ?? [];

    return {
        todo: tasks.filter((task) => task.status === 'todo'),
        inProgress: tasks.filter((task) => task.status === 'in_progress'),
        completed: tasks.filter((task) => task.status === 'completed'),
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
    let tasks = props.project.tasks ?? [];

    if (selectedTaskStatus.value !== 'all') {
        tasks = tasks.filter(
            (task) => task.status === selectedTaskStatus.value,
        );
    }

    return [...tasks].sort((a, b) => {
        if (selectedTaskSort.value === 'newest') {
            return (
                new Date(b.created_at).getTime() -
                new Date(a.created_at).getTime()
            );
        }

        if (selectedTaskSort.value === 'oldest') {
            return (
                new Date(a.created_at).getTime() -
                new Date(b.created_at).getTime()
            );
        }

        if (selectedTaskSort.value === 'due_date') {
            return (a.due_date ?? '').localeCompare(b.due_date ?? '');
        }

        const order = {
            todo: 1,
            in_progress: 2,
            completed: 3,
        };

        return order[a.status] - order[b.status];
    });
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

function onDragEnd(event: { item: HTMLElement; to: HTMLElement }) {
    const taskId = Number(event.item.dataset.taskId);
    const status = event.to.dataset.status as Task['status'];

    const task = props.project.tasks?.find((task) => task.id === taskId);

    if (!task || !status || task.status === status) {
        return;
    }

    router.put(
        update({
            project: props.project.id,
            task: task.id,
        }).url,
        {
            title: task.title,
            description: task.description ?? '',
            status,
            due_date: task.due_date,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => toast.success('Task status updated'),
            onError: () => toast.error('Failed to update status'),
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
            <div class="mt-5 space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium">Progress</span>
                    <span class="text-muted-foreground">
                        {{ projectProgress }}%
                    </span>
                </div>
                <Progress :model-value="projectProgress" />
            </div>
            <div
                class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex flex-wrap gap-2">
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
                        >{{ status.label }} ({{
                            status.value === 'todo'
                                ? taskCounts.todo
                                : status.value === 'in_progress'
                                  ? taskCounts.inProgress
                                  : taskCounts.completed
                        }})
                    </Button>
                </div>
                <div class="flex items-center gap-2">
                    <Select v-model="selectedTaskSort">
                        <span class="text-sm text-muted-foreground">
                            Sort by
                        </span>
                        <SelectTrigger class="w-40" size="sm">
                            <SelectValue placeholder="Sort by" />
                        </SelectTrigger>

                        <SelectContent>
                            <SelectItem value="status">Status</SelectItem>
                            <SelectItem value="due_date">Due date</SelectItem>
                            <SelectItem value="newest">Newest</SelectItem>
                            <SelectItem value="oldest">Oldest</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div>
                    <Button
                        size="sm"
                        :variant="viewMode === 'list' ? 'default' : 'outline'"
                        @click="viewMode = 'list'"
                    >
                        List
                    </Button>
                    <Button
                        size="sm"
                        :variant="viewMode === 'board' ? 'default' : 'outline'"
                        @click="viewMode = 'board'"
                    >
                        Board
                    </Button>
                </div>
            </div>

            <div
                v-if="viewMode === 'list' && filteredTasks?.length"
                class="mt-6 space-y-3"
            >
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
                v-if="viewMode === 'board'"
                class="mt-6 grid gap-4 lg:grid-cols-3"
            >
                <!-- To do -->
                <div
                    class="rounded-xl border p-4 transition-colors hover:bg-muted/30"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold">Todo</h3>
                        <Badge variant="outline">
                            {{ boardTasks.todo.length }}
                        </Badge>
                    </div>

                    <VueDraggable
                        :model-value="boardTasks.todo"
                        group="tasks"
                        item-key="id"
                        class="space-y-3"
                        data-status="todo"
                        @end="onDragEnd"
                    >
                        <div
                            v-for="task in boardTasks.todo"
                            :key="task.id"
                            :data-task-id="task.id"
                            class="rounded-xl border p-3"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-medium">{{ task.title }}</p>
                                    <p
                                        v-if="task.description"
                                        class="mt-1 line-clamp-3 text-sm text-muted-foreground"
                                    >
                                        {{ task.description }}
                                    </p>
                                    <p
                                        class="mt-2 text-xs text-muted-foreground"
                                    >
                                        Due {{ formatDate(task.due_date) }}
                                    </p>
                                </div>
                                <div class="shrink-0 items-center gap-1">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7"
                                        @click="openEditTaskDialog(task)"
                                    >
                                        <Edit class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7 text-destructive hover:text-destructive/90"
                                        @click="deleteTask(task)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </VueDraggable>
                </div>
                <!-- In progress -->
                <div
                    class="rounded-xl border p-4 transition-colors hover:bg-muted/30"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold">In Progress</h3>
                        <Badge variant="outline">
                            {{ boardTasks.inProgress.length }}
                        </Badge>
                    </div>

                    <VueDraggable
                        :model-value="boardTasks.inProgress"
                        group="tasks"
                        item-key="id"
                        class="space-y-3"
                        data-status="in_progress"
                        @end="onDragEnd"
                    >
                        <div
                            v-for="task in boardTasks.inProgress"
                            :key="task.id"
                            :data-task-id="task.id"
                            class="rounded-xl border p-3"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-medium">{{ task.title }}</p>
                                    <p
                                        v-if="task.description"
                                        class="mt-1 line-clamp-3 text-sm text-muted-foreground"
                                    >
                                        {{ task.description }}
                                    </p>
                                    <p
                                        class="mt-2 text-xs text-muted-foreground"
                                    >
                                        Due {{ formatDate(task.due_date) }}
                                    </p>
                                </div>
                                <div class="shrink-0 items-center gap-1">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7"
                                        @click="openEditTaskDialog(task)"
                                    >
                                        <Edit class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7 text-destructive hover:text-destructive/90"
                                        @click="deleteTask(task)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </VueDraggable>
                </div>
                <!-- Completed -->
                <div
                    class="rounded-xl border p-4 transition-colors hover:bg-muted/30"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold">Completed</h3>
                        <Badge variant="outline">
                            {{ boardTasks.completed.length }}
                        </Badge>
                    </div>

                    <VueDraggable
                        :model-value="boardTasks.completed"
                        group="tasks"
                        item-key="id"
                        class="space-y-3"
                        data-status="completed"
                        @end="onDragEnd"
                    >
                        <div
                            v-for="task in boardTasks.completed"
                            :key="task.id"
                            :data-task-id="task.id"
                            class="rounded-xl border p-3"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-medium">{{ task.title }}</p>
                                    <p
                                        v-if="task.description"
                                        class="mt-1 line-clamp-3 text-sm text-muted-foreground"
                                    >
                                        {{ task.description }}
                                    </p>
                                    <p
                                        class="mt-2 text-xs text-muted-foreground"
                                    >
                                        Due {{ formatDate(task.due_date) }}
                                    </p>
                                </div>
                                <div class="shrink-0 items-center gap-1">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7"
                                        @click="openEditTaskDialog(task)"
                                    >
                                        <Edit class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7 text-destructive hover:text-destructive/90"
                                        @click="deleteTask(task)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </VueDraggable>
                </div>
            </div>
            <div
                v-if="!filteredTasks?.length"
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
