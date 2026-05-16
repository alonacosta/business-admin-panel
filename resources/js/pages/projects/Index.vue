<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import ProjectDeleteDialog from '@/components/projects/ProjectDeleteDialog.vue';
import ProjectFormDialog from '@/components/projects/ProjectFormDialog.vue';
import ProjectsTable from '@/components/projects/ProjectsTable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index } from '@/routes/projects';
import type {
    Project,
    ProjectStatusOption,
    ProjectFilters,
} from '@/types/project';

type PaginatedProjects = {
    data: Project[];
    links: unknown[];
    meta: unknown;
};

const props = defineProps<{
    projects: PaginatedProjects;
    statuses: ProjectStatusOption[];
    filters: ProjectFilters;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Projects',
                href: '/projects',
            },
        ],
    },
});

const ALL_STATUSES = 'all';

const filterForm = ref<ProjectFilters>({
    search: props.filters.search,
    status: props.filters.status || ALL_STATUSES,
});

watch(
    filterForm,
    (filters) => {
        router.get(
            index().url,
            {
                search: filters.search,
                status: filters.status === ALL_STATUSES ? '' : filters.status,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    },
    { deep: true },
);

function clearFilters() {
    filterForm.value.search = '';
    filterForm.value.status = ALL_STATUSES;
}

const hasActiveFilters = computed(() => {
    return (
        filterForm.value.search !== '' ||
        filterForm.value.status !== ALL_STATUSES
    );
});

const isFormDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);

const selectedProject = ref<Project | null>(null);

function openCreateDialog() {
    selectedProject.value = null;
    isFormDialogOpen.value = true;
}

function openEditDialog(project: Project) {
    selectedProject.value = project;
    isFormDialogOpen.value = true;
}

function openDeleteDialog(project: Project) {
    selectedProject.value = project;
    isDeleteDialogOpen.value = true;
}
</script>
<template>
    <Head title="Projects" />

    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Projects</h1>
                <p class="text-sm text-muted-foreground">
                    Manage business projects
                </p>
            </div>

            <Button @click="openCreateDialog">Create Project</Button>
        </div>

        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <Input
                v-model="filterForm.search"
                placeholder="Search projects..."
                class="md:max-w-sm"
            />
            <Select
                :model-value="filterForm.status"
                @update:model-value="filterForm.status = String($event)"
            >
                <SelectTrigger class="md:w-48">
                    <SelectValue placeholder="All Statuses" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="ALL_STATUSES">All Statuses</SelectItem>
                    <SelectItem
                        v-for="status in statuses"
                        :key="status.value"
                        :value="status.value"
                    >
                        {{ status.label }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Button
                v-if="hasActiveFilters"
                type="button"
                varian="outline"
                @click="clearFilters"
            >
                Clear Filters
            </Button>
        </div>

        <ProjectsTable
            :projects="projects.data"
            @edit="openEditDialog"
            @delete="openDeleteDialog"
        />

        <ProjectFormDialog
            v-model:open="isFormDialogOpen"
            :project="selectedProject"
            :statuses="statuses"
        />

        <ProjectDeleteDialog
            v-model:open="isDeleteDialogOpen"
            :project="selectedProject"
        />
    </div>
</template>
