<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Edit, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { formatDate } from '@/lib/date';
import { destroy, store, update } from '@/routes/projects/tasks/comments';
import type { Task, TaskComment } from '@/types/project';

const props = defineProps<{
    open: boolean;
    task: Task | null;
    projectId: number;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const editingCommentId = ref<number | null>(null);

const form = useForm({
    content: '',
});

const editForm = useForm({
    content: '',
});

function submitComment() {
    if (!props.task) {
        return;
    }

    form.post(
        store({
            project: props.projectId,
            task: props.task.id,
        }).url,
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Comment added successfully');
                form.reset();
            },
        },
    );
}

function startEditingComment(comment: TaskComment) {
    editingCommentId.value = comment.id;
    editForm.content = comment.content;
    editForm.clearErrors();
}

function cancelEditingComment() {
    editingCommentId.value = null;
    editForm.reset();
    editForm.clearErrors();
}

function updateComment(comment: TaskComment) {
    if (!props.task) {
        return;
    }

    editForm.put(
        update({
            project: props.projectId,
            task: props.task.id,
            comment: comment.id,
        }).url,
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Comment updated successfully');
                cancelEditingComment();
            },
        },
    );
}

function deleteComment(comment: TaskComment) {
    if (!props.task) {
        return;
    }

    router.delete(
        destroy({
            project: props.projectId,
            task: props.task.id,
            comment: comment.id,
        }).url,
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Comment deleted successfully');
            },
        },
    );
}
</script>
<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle>{{ task?.title }}</DialogTitle>
                <DialogDescription class="sr-only"
                    >View task details and comments.</DialogDescription
                >
            </DialogHeader>
            <div class="space-y-4">
                <p
                    v-if="task?.description"
                    class="text-sm text-muted-foreground"
                >
                    {{ task.description }}
                </p>

                <div class="rounded-xl border p-4">
                    <h3 class="mb-3 font-medium">Comments</h3>
                    <form
                        class="mt-4 space-y-2"
                        @submit.prevent="submitComment"
                    >
                        <Textarea
                            v-model="form.content"
                            placeholder="Add a comment..."
                            rows="3"
                        />
                        <InputError :message="form.errors.content" />

                        <div class="flex justify-end">
                            <Button
                                type="submit"
                                size="sm"
                                :disabled="form.processing"
                            >
                                {{
                                    form.processing
                                        ? 'Sending...'
                                        : 'Add comment'
                                }}
                            </Button>
                        </div>
                    </form>
                    <div class="mt-3 space-y-3" v-if="task?.comments?.length">
                        <div
                            v-for="comment in task.comments"
                            :key="comment.id"
                            class="rounded-lg border p-3"
                        >
                            <div class="item-center mb-2 flex justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">
                                        {{
                                            comment.user?.name ?? 'Deleted User'
                                        }}
                                    </span>
                                    <span class="text-xs text-muted-foreground">
                                        {{ formatDate(comment.created_at) }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7"
                                        @click="startEditingComment(comment)"
                                    >
                                        <Edit class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7 text-destructive hover:text-destructive/90"
                                        @click="deleteComment(comment)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <form
                                v-if="editingCommentId === comment.id"
                                class="space-y-2"
                                @submit.prevent="updateComment(comment)"
                            >
                                <Textarea v-model="editForm.content" rows="3" />
                                <InputError
                                    :message="editForm.errors.content"
                                />

                                <div class="flex justify-end gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        @click="cancelEditingComment"
                                    >
                                        Cancel
                                    </Button>
                                    <Button
                                        type="submit"
                                        size="sm"
                                        :disabled="editForm.processing"
                                    >
                                        {{ editForm.processing ? 'Saving...' : 'Save' }}
                                    </Button>
                                </div>
                            </form>

                            <p v-else class="text-sm text-muted-foreground">
                                {{ comment.content }}
                            </p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">
                        No comments yet
                    </p>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
