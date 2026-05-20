<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { RotateCcw } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import ProjectDeleteDialog from '@/components/projects/ProjectDeleteDialog.vue';
import ProjectFormDialog from '@/components/projects/ProjectFormDialog.vue';
import ProjectsTable from '@/components/projects/ProjectsTable.vue';
import PaginationFooter from '@/components/shared/PaginationFooter.vue';
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
    PaginationLink,
} from '@/types/project';

type PaginatedProjects = {
    data: Project[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    per_page: number;
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
    sort: props.filters.sort || 'created_at',
    direction: props.filters.direction || 'desc',
});

function handleSort(column: string) {
    if (filterForm.value.sort === column) {
        filterForm.value.direction =
            filterForm.value.direction === 'asc' ? 'desc' : 'asc';

        return;
    }

    filterForm.value.sort = column;
    filterForm.value.direction = 'asc';
}

function visitProjects(filters: ProjectFilters) {
    router.get(index().url, {
        search: filters.search,
        status: filters.status === ALL_STATUSES ? '' : filters.status,
        sort: filters.sort,
        direction: filters.direction,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

const debouncedVisitProjects = useDebounceFn((filters: ProjectFilters) => visitProjects(filters), 400);

watch(
    () => filterForm.value.search,
    () => debouncedVisitProjects(filterForm.value),
);

watch(
    () => [
        filterForm.value.status,
        filterForm.value.sort,
        filterForm.value.direction,
    ],
    () => visitProjects(filterForm.value),
);

function clearFilters() {
    filterForm.value.search = '';
    filterForm.value.status = ALL_STATUSES;
}

function resetSorting() {
    filterForm.value.sort = 'created_at';
    filterForm.value.direction = 'desc';
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

function visitPaginationUrl(url: string | null) {
    if (!url) {
        return;
    }

    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}
</script>
<template>
    <Head title="Projects" />

    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Projects</h1>
                <div
                    class="flex items-center gap-2 text-sm text-muted-foreground"
                >
                    <p>Manage business projects</p>
                    <p>({{ projects.total }} projects)</p>
                </div>
            </div>

            <Button @click="openCreateDialog">Create Project</Button>
        </div>

        <div class="flex items-center justify-between">
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
                        <SelectItem :value="ALL_STATUSES"
                            >All Statuses</SelectItem
                        >
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
                    variant="outline"
                    @click="clearFilters"
                >
                    Clear Filters
                </Button>
            </div>

            <Button
                v-if="
                    filterForm.sort !== 'created_at' ||
                    filterForm.direction !== 'desc'
                "
                variant="ghost"
                size="sm"
                @click="resetSorting"
            >
                <RotateCcw class="h-4 w-4" />
                Reset Sorting
            </Button>
        </div>
        <ProjectsTable
            :projects="projects.data"
            :sort="filterForm.sort"
            :direction="filterForm.direction"
            @sort="handleSort"
            @edit="openEditDialog"
            @delete="openDeleteDialog"
        />
        <PaginationFooter
            :from="projects.from"
            :to="projects.to"
            :total="projects.total"
            :per-page="projects.per_page"
            :links="projects.links"
            @visit="visitPaginationUrl"
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
