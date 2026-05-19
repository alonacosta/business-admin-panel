<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import ProjectDeleteDialog from '@/components/projects/ProjectDeleteDialog.vue';
import ProjectFormDialog from '@/components/projects/ProjectFormDialog.vue';
import ProjectsTable from '@/components/projects/ProjectsTable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
} from '@/components/ui/pagination';
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
                variant="outline"
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
        <div
            v-if="projects.total > 0"
            class="mt-4 flex flex-col gap-3 rounded-xl border px-4 py-3 md:flex-row md:items-center md:justify-center md:gap-10"
        >
            <p class="text-center md:text-left whitespace-nowrap text-sm text-muted-foreground">
                Showing {{ projects.from }}-{{ projects.to }} of
                {{ projects.total }} projects
            </p>

            <Pagination
                v-if="projects.total > 0"
                :items-per-page="projects.per_page"
                :total="projects.total"
            >
                <PaginationContent class="gap-1">
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-8 w-8"
                        :disabled="!projects.links[0]?.url"
                        @click="
                            visitPaginationUrl(projects.links[0]?.url ?? null)
                        "
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </Button>

                    <template
                        v-for="link in projects.links.slice(1, -1)"
                        :key="link.label"
                    >
                        <Button
                            v-if="link.label !== '...'"
                            variant="outline"
                            size="sm"
                            class="h-8 min-w-8 px-3"
                            :class="{
                                'bg-primary text-primary-foreground':
                                    link.active,
                            }"
                            :disabled="!link.url || link.active"
                            @click="visitPaginationUrl(link.url)"
                        >
                            {{ link.label }}
                        </Button>

                        <PaginationEllipsis v-else />
                    </template>

                    <Button
                        variant="outline"
                        size="sm"
                        class="h-8 w-8"
                        :disabled="
                            !projects.links[projects.links.length - 1]?.url
                        "
                        @click="
                            visitPaginationUrl(
                                projects.links[projects.links.length - 1]
                                    ?.url ?? null,
                            )
                        "
                    >
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </PaginationContent>
            </Pagination>
        </div>

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
