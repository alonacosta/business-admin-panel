<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    createColumnHelper,
    FlexRender,
    getCoreRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import { ArrowDown, ArrowUp, ArrowUpDown, MoreHorizontal, Pencil, Trash2, FolderOpen, Eye } from 'lucide-vue-next';
import { computed, h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDate } from '@/lib/date';
import { show } from '@/routes/projects';
import type { Project } from '@/types/project';

const props = defineProps<{
    projects: Project[];
    sort: string;
    direction: 'asc' | 'desc';
}>();

const emit = defineEmits<{
    edit: [project: Project];
    delete: [project: Project];
    sort: [column: string];
}>();

function sortIcon(column: string) {
    if (props.sort !== column) {
        return ArrowUpDown;
    }

    return props.direction === 'asc' ? ArrowUp : ArrowDown;
}

function sortableHeader(label: string, column: string) {
    return () =>
        h(
            'button',
            {
                class: 'flex items-center gap-1 fornt-medium hover:text-primary',
                onClick: () => emit('sort', column),
            },
            [label, h(sortIcon(column), { class: 'h-4 w-4 text-muted-foreground'})],
        );
}

const columnHelper = createColumnHelper<Project>();

const columns = [
    columnHelper.accessor('name', {
        header: sortableHeader('Name', 'name'),
        cell: ({ row }) => row.original.name,
    }),
    columnHelper.accessor('status', {
        header: sortableHeader('Status', 'status'),
        cell: ({ row }) => row.original.status,
    }),
    columnHelper.accessor('owner.name', {
        header: 'Owner',
        cell: ({ row }) => row.original.owner?.name ?? '-',
    }),
    columnHelper.accessor('due_date', {
        header: sortableHeader('Due date', 'due_date'),
        cell: ({ row }) => formatDate(row.original.due_date),
    }),
    columnHelper.display({
        id: 'actions',
        header: '',
        cell: ({ row }) => row.original,
    }),
];

const data = computed(() => props.projects);

const table = useVueTable({
    get data() {
        return data.value;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
});

function getStatusLabel(status: Project['status']) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}
</script>
<template>
    <div class="rounded-xl border">
        <Table>
            <TableHeader>
                <TableRow
                    v-for="headerGroup in table.getHeaderGroups()"
                    :key="headerGroup.id"
                >
                    <TableHead
                        v-for="header in headerGroup.headers"
                        :key="header.id"
                    >
                        <FlexRender
                            v-if="!header.isPlaceholder"
                            :render="header.column.columnDef.header"
                            :props="header.getContext()"
                        />
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="row in table.getRowModel().rows" :key="row.id">
                    <TableCell
                        v-for="cell in row.getVisibleCells()"
                        :key="cell.id"
                    >
                        <template v-if="cell.column.id === 'status'">
                            <Badge variant="secondary">
                                {{ getStatusLabel(row.original.status) }}
                            </Badge>
                        </template>

                        <template v-else-if="cell.column.id === 'actions'">
                            <div class="flex justify-end gap-2">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="w-8 h-8">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-40">
                                        <DropdownMenuItem>
                                            <Link :href="show(row.original.id).url" class="flex items-center">
                                                <Eye class="mr-2 h-4 w-4" />
                                                View Details
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="emit('edit', row.original)">
                                            <Pencil class="mr-2 h-4 w-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            class="text-destructive focus:text-destructive"
                                            @click="emit('delete', row.original)">
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </template>

                        <template v-else>
                            <FlexRender
                                :render="cell.column.columnDef.cell"
                                :props="cell.getContext()"
                            />
                        </template>
                    </TableCell>
                </TableRow>
                <TableRow v-if="!table.getRowModel().rows.length">
                    <TableCell
                        :colspan="columns.length"
                        class="h-52"
                    >
                        <div
                            class="flex flex-col items-center justify-center gap-3 text-center"
                        >
                            <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                                <FolderOpen class="h-6 w-6 text-muted-foreground" />
                            </div>

                            <div class="space-y-1">
                                <h3 class="text-sm font-medium">No project found</h3>
                                <p class="text-sm text-muted-foreground">
                                    Try adjusting your filters or create a new project.
                                </p>
                            </div>
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
