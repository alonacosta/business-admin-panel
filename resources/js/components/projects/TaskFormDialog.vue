<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { toDateInputValue } from '@/lib/date';
import { store, update } from '@/routes/projects/tasks';
import type {
    Project,
    Task,
    TaskFormData,
    TaskStatusOption,
} from '@/types/project';

const props = defineProps<{
    open: boolean;
    project: Project;
    task: Task | null;
    taskStatuses: TaskStatusOption[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const isEditing = computed(() => !!props.task);

const form = useForm<TaskFormData>({
    title: '',
    description: '',
    status: 'todo',
    due_date: '',
});

watch(
    () => props.open,
    (open) => {
        form.clearErrors();

        if (!open) {
            form.reset();

            return;
        }

        if (!props.task) {
            form.reset();
        }
    },
);

watch(
    () => props.task,
    (task) => {
        form.clearErrors();

        if (task) {
            form.title = task.title;
            form.description = task.description ?? '';
            form.status = task.status;
            form.due_date = toDateInputValue(task.due_date);
        } else {
            form.reset();
        }
    },
    { immediate: true },
);

function closeDialog() {
    emit('update:open', false);
}

function getPayload() {
    return {
        ...form.data(),
        due_date: form.due_date || null,
    };
}

function submit() {
    if (isEditing.value && props.task) {
        form.transform(() => getPayload()).put(
            update({
                project: props.project.id,
                task: props.task.id,
            }).url,
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success('Task updated successfully');
                    closeDialog();
                },
            },
        );

        return;
    }

    form.transform(() => getPayload()).post(store(props.project.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Task created successfully');
            closeDialog();
        },
    });
}
</script>
<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-xl">
            <DialogHeader>
                <DialogTitle>{{
                    isEditing ? 'Edit Task' : 'Add Task'
                }}</DialogTitle>
                <DialogDescription
                    >{{ isEditing
                    ? 'Update the task details.'
                    : 'Create a new task for this project' }}</DialogDescription
                >
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <Label for="title">Title</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        placeholder="Task Title"
                    />
                    <InputError :message="form.errors.title" />
                </div>
                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Task Description"
                    />

                    <InputError :message="form.errors.description" />
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label>Status</Label>
                        <Select
                            :model-value="form.status"
                            @update:model-value="
                                form.status = $event as TaskFormData['status']
                            "
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select Status" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem
                                    v-for="status in taskStatuses"
                                    :key="status.value"
                                    :value="status.value"
                                    >{{ status.label }}</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.status" />
                    </div>
                    <div class="space-y-2">
                        <Label for="task_due_date">Due Date</Label>
                        <Input
                            id="task_due_date"
                            v-model="form.due_date"
                            type="date"
                        />

                        <InputError :message="form.errors.due_date" />
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="closeDialog"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : isEditing ? 'Update Task' : 'Create Task' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
