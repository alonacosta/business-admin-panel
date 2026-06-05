<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
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
import { store } from '@/routes/projects/tasks/comments';
import type { Task } from '@/types/project';

const props = defineProps<{
    open: boolean;
    task: Task | null;
    projectId: number;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
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
                    <div class="space-y-3 mt-3" v-if="task?.comments?.length">
                        <div
                            v-for="comment in task.comments"
                            :key="comment.id"
                            class="rounded-lg border p-3"
                        >
                            <div class="item-center mb-2 flex justify-between">
                                <span class="font-medium">
                                    {{ comment.user?.name ?? 'Deleted User' }}
                                </span>
                                <span class="text-sm text-muted-foreground">
                                    {{ formatDate(comment.created_at) }}
                                </span>
                            </div>
                            <p class="text-sm text-muted-foreground">
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
