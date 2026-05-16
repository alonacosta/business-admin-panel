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
