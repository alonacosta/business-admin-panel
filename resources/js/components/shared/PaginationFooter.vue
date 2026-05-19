<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
} from '@/components/ui/pagination';
import type { PaginationLink } from '@/types/project';

defineProps<{
    from: number | null;
    to: number | null;
    total: number;
    perPage: number;
    links: PaginationLink[];
}>();

const emit = defineEmits<{ visit: [url: string | null] }>();
</script>
<template>
    <div
        v-if="total > 0"
        class="mt-4 flex flex-col gap-3 rounded-xl border px-4 py-3 md:flex-row md:items-center md:justify-center md:gap-10"
    >
        <p
            class="text-center text-sm whitespace-nowrap text-muted-foreground md:text-left"
        >
            Showing {{ from }}-{{ to }} of
            {{ total }} projects
        </p>

        <Pagination
            v-if="total > 0"
            :items-per-page="perPage"
            :total="total"
        >
            <PaginationContent class="gap-1">
                <Button
                    variant="outline"
                    size="sm"
                    class="h-8 w-8"
                    :disabled="!links[0]?.url"
                    @click="emit('visit', links[0]?.url ?? null)"
                >
                    <ChevronLeft class="h-4 w-4" />
                </Button>

                <template
                    v-for="link in links.slice(1, -1)"
                    :key="link.label"
                >
                    <Button
                        v-if="link.label !== '...'"
                        variant="outline"
                        size="sm"
                        class="h-8 min-w-8 px-3"
                        :class="{
                            'bg-primary text-primary-foreground': link.active,
                        }"
                        :disabled="!link.url || link.active"
                        @click="emit('visit', link.url)"
                    >
                        {{ link.label }}
                    </Button>

                    <PaginationEllipsis v-else />
                </template>

                <Button
                    variant="outline"
                    size="sm"
                    class="h-8 w-8"
                    :disabled="!links[links.length - 1]?.url"
                    @click="
                       emit('visit',
                            links[links.length - 1]?.url ??
                                null,
                        )
                    "
                >
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </PaginationContent>
        </Pagination>
    </div>
</template>
