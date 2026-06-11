export type ProjectStatus = 'draft' | 'active' | 'completed' | 'archived';

export type ProjectOwner = {
    id: number;
    name: string;
    email: string;
};

export type Project = {
    id: number;
    owner_id: number;
    name: string;
    description: string | null;
    status: ProjectStatus;
    due_date: string | null;
    created_at: string;
    updated_at: string;
    owner?: ProjectOwner;
    tasks?: Task[];
};

export type ProjectFormData = {
    name: string;
    description: string;
    status: ProjectStatus;
    due_date: string;
};

export type ProjectStatusOption = {
    value: ProjectStatus;
    label: string;
};

export type ProjectFilters = {
    search: string;
    status: string;
    sort: string;
    direction: 'asc' | 'desc';
}

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
}

export type TaskStatus = 'todo' | 'in_progress' | 'completed';

export type Task = {
    id: number;
    project_id: number;
    owner_id: number;
    title: string;
    description: string | null;
    status: TaskStatus;
    due_date: string | null;
    created_at: string;
    updated_at: string;
    owner?: ProjectOwner;
    comments?: TaskComment[];
};

export type TaskFormData = {
    title: string;
    description: string;
    status: TaskStatus;
    due_date: string;
};

export type TaskStatusOption = {
    value: TaskStatus;
    label: string;
};

export type TaskCommentMention = {
    id: number;
    task_comment_id: number;
    user_id: number;
};

export type TaskComment = {
    id: number;
    content: string;
    created_at: string;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
    mentions?: TaskCommentMention[];
};

export type MentionUser = {
    id: number;
    name: string;
    email: string;
}
