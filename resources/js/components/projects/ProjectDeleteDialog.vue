<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { destroy } from '@/routes/projects';
import type { Project } from '@/types/project';

const props = defineProps<{
    open: boolean;
    project: Project | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

function closeDialog() {
    emit('update:open', false);
}

function confirmDelete() {
    if(!props.project) {
        return;
    }

    router.delete(destroy(props.project.id).url, {
        preserveScroll: true,
        onSuccess: () => closeDialog(),
    });
}
</script>
<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete Project</DialogTitle>
                <DialogDescription>
                    Are you sure you want to delete <strong>{{ project?.name }}</strong>? This action cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="closeDialog">Cancel</Button>
                <Button variant="destructive" @click="confirmDelete">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
