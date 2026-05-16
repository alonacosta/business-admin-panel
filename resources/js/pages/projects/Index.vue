<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import ProjectDeleteDialog from '@/components/projects/ProjectDeleteDialog.vue';
import ProjectFormDialog from '@/components/projects/ProjectFormDialog.vue';
import ProjectsTable from '@/components/projects/ProjectsTable.vue';
import { Button } from '@/components/ui/button';
import type { Project, ProjectStatusOption } from '@/types/project';

type PaginatedProjects = {
    data: Project[];
    links: unknown[];
    meta: unknown;
};

defineProps<{
    projects: PaginatedProjects;
    statuses: ProjectStatusOption[];
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
