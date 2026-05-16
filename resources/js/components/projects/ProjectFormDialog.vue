<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
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
import { store, update } from '@/routes/projects';
import type {
    Project,
    ProjectFormData,
    ProjectStatusOption,
} from '@/types/project';

const props = defineProps<{
    open: boolean;
    project: Project | null;
    statuses: ProjectStatusOption[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const isEditing = computed(() => !!props.project);

const form = useForm<ProjectFormData>({
    name: '',
    description: '',
    status: 'draft',
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

        if(!props.project) {
            form.reset();
        }
    },
)

watch(
    () => props.project,
    (project) => {
        form.clearErrors();

        if (project) {
            form.name = project.name;
            form.description = project.description ?? '';
            form.status = project.status;
            form.due_date = toDateInputValue(project.due_date);
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
    if (isEditing.value && props.project) {
        form.transform(() => getPayload()).put(update(props.project.id).url, {
            preserveScroll: true,
            onSuccess: () => closeDialog(),
        });

        return;
    }

    form.transform(() => getPayload()).post(store().url, {
        preserveScroll: true,
        onSuccess: () => closeDialog(),
    });
}
</script>
<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-xl">
            <DialogHeader>
                <DialogTitle>
                    {{ isEditing ? 'Edit Project' : 'Create Project' }}
                </DialogTitle>

                <DialogDescription>
                    {{
                        isEditing
                            ? 'Update the project details.'
                            : 'Create a new business project.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="Project Name"
                    />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Project Description"
                    />
                    <InputError :message="form.errors.description" />
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label>Status</Label>
                        <Select
                            :model-value="form.status"
                            @update:model-value="
                                form.status =
                                    $event as ProjectFormData['status']
                            "
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select a status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="status in statuses"
                                    :key="status.value"
                                    :value="status.value"
                                >
                                    {{ status.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.status" />
                    </div>
                    <div>
                        <Label for="due_date">Due Date</Label>
                        <Input
                            id="due_date"
                            v-model="form.due_date"
                            type="date"
                        />
                        <InputError :message="form.errors.due_date" />
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeDialog"
                        >Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{
                            form.processing
                                ? 'Saving...'
                                : isEditing
                                  ? 'Update'
                                  : 'Create'
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
