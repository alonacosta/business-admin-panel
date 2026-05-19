<script setup lang="ts">
import {
    createColumnHelper,
    FlexRender,
    getCoreRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import { computed, h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDate } from '@/lib/date';
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
        return '↕';
    }

    return props.direction === 'asc' ? '↑' : '↓';
}

function sortableHeader(label: string, column: string) {
    return () =>
        h(
            'button',
            {
                class: 'flex items-center gap-1 fornt-medium hover:text-primary',
                onClick: () => emit('sort', column),
            },
            [label, h('span', { class: 'text-xs text-muted-foreground' }, sortIcon(column))],
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
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="emit('edit', row.original)"
                                >
                                    Edit
                                </Button>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    @click="emit('delete', row.original)"
                                >
                                    Delete
                                </Button>
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
                        class="h-24 text-center"
                    >
                        No projects found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
