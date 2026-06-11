<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { Edit, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
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
import { search as searchUsers } from '@/routes/users';
import type { User } from '@/types';
import type { MentionUser, Task, TaskComment } from '@/types/project';

const props = defineProps<{
    open: boolean;
    task: Task | null;
    projectId: number;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const editingCommentId = ref<number | null>(null);

const users = ref<User[]>([]);
const showMentions = ref(false);
const mentionSearch = ref('');
const isSelectingMention = ref(false);

const editUsers = ref<MentionUser[]>([]);
const showEditMentions = ref(false);
const editMentionSearch = ref('');
const isSelectingEditMention = ref(false);

const form = useForm({
    content: '',
    mentioned_user_ids: [] as number[],
});

const editForm = useForm({
    content: '',
    mentioned_user_ids: [] as number[],
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
                form.mentioned_user_ids = [];
            },
        },
    );
}

function startEditingComment(comment: TaskComment) {
    editingCommentId.value = comment.id;
    editForm.content = comment.content;

    editForm.mentioned_user_ids = comment.mentions?.map(
        (mention) => mention.user_id,
    ) ?? [];
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
                editForm.mentioned_user_ids = [];
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

const mentionRegex = /(?:^|\s)@([^\s@]*)$/;

watch(
    () => form.content,
    (value) => {
        if (isSelectingMention.value) {
            isSelectingMention.value = false;

            return;
        }

        const match = value.match(mentionRegex);

        if (!match) {
            showMentions.value = false;
            mentionSearch.value = '';
            users.value = [];

            return;
        }

        mentionSearch.value = match[1].trim();
        showMentions.value = true;
    },
);

watch(
    () => editForm.content,
    (value) => {
        if (isSelectingEditMention.value) {
            isSelectingEditMention.value = false;

            return;
        }

        const match = value.match(mentionRegex);

        if (!match) {
            showEditMentions.value = false;
            editMentionSearch.value = '';
            editUsers.value = [];

            return;
        }

        editMentionSearch.value = match[1].trim();
        showEditMentions.value = true;
    },
);

watchDebounced(
    mentionSearch,
    async (search) => {
        if (!search.length || !showMentions.value) {
            users.value = [];

            return;
        }

        const response = await fetch(
            searchUsers({
                query: {
                    search,
                },
            }).url,
        );
        users.value = await response.json();
    },
    {
        debounce: 300,
        maxWait: 500,
    },
);

watchDebounced(
    editMentionSearch,
    async (search) => {
        if (!search.length || !showEditMentions.value) {
            editUsers.value = [];

            return;
        }

        const response = await fetch(
            searchUsers({
                query: {
                    search,
                },
            }).url,
        );
        editUsers.value = await response.json();
    },
    {
        debounce: 300,
        maxWait: 500,
    },
);

function selectMention(user: MentionUser) {
    if (!form.content.match(mentionRegex)) {
        return;
    }

    isSelectingMention.value = true;

    form.content = form.content.replace(mentionRegex, ` @${user.name} `);

    if (!form.mentioned_user_ids.includes(user.id)) {
        form.mentioned_user_ids.push(user.id);
    }

    showMentions.value = false;
    mentionSearch.value = '';
    users.value = [];
}

function selectEditMention(user: MentionUser) {
    if (!editForm.content.match(mentionRegex)) {
        return;
    }

    isSelectingEditMention.value = true;

    editForm.content = editForm.content.replace(mentionRegex, ` @${user.name} `);

    if (!editForm.mentioned_user_ids.includes(user.id)) {
        editForm.mentioned_user_ids.push(user.id);
    }

    showEditMentions.value = false;
    editMentionSearch.value = '';
    editUsers.value = [];
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
                        <div
                            v-if="showMentions && users.length"
                            class="mt-2 max-h-60 overflow-y-auto rounded-lg border bg-background shadow-lg"
                        >
                            <button
                                v-for="user in users"
                                :key="user.id"
                                type="button"
                                class="flex items-center gap-3 px-3 py-2 text-left hover:bg-muted"
                                @click="selectMention(user)"
                            >
                                <div
                                    class="forn-medium flex h-9 w-9 items-center justify-center rounded-full bg-muted text-sm"
                                >
                                    {{ user.name.charAt(0) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="trancate forn-medium">
                                        {{ user.name }}
                                    </p>
                                    <p
                                        class="trancate text-xs text-muted-foreground"
                                    >
                                        {{ user.email }}
                                    </p>
                                </div>
                            </button>
                        </div>
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
                                <div
                                    v-if="showEditMentions && editUsers.length"
                                    class="mt-2 max-h-60 overflow-y-auto rounded-lg border bg-background shadow-lg"
                                >
                                    <button
                                        v-for="user in editUsers"
                                        :key="user.id"
                                        type="button"
                                        class="flex w-full items-center gap-3 px-3 py-2 text-left hover:bg-muted"
                                        @click="selectEditMention(user)"
                                    >
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-full bg-muted text-sm font-medium"
                                        >
                                            {{ user.name.charAt(0) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p
                                                class="trancate text-sm font-medium"
                                            >
                                                {{ user.name }}
                                            </p>
                                            <p
                                                class="trancate text-xs text-muted-foreground"
                                            >
                                                {{ user.email }}
                                            </p>
                                        </div>
                                    </button>
                                </div>
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
                                        {{
                                            editForm.processing
                                                ? 'Saving...'
                                                : 'Save'
                                        }}
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
