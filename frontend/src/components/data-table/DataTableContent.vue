<script setup lang="ts" generic="T extends DataTableRow">
import { ArrowDown, ArrowUp, ChevronsUpDown, Loader2 } from "@lucide/vue";
import type { DataTableColumn, DataTableRow, DataTableSort } from "@/types";

defineProps<{
  columns: DataTableColumn<T>[];
  rows: T[];
  sort: DataTableSort | null;
  loading?: boolean;
  emptyMessage: string;
}>();

const emit = defineEmits<{
  sort: [column: DataTableColumn<T>];
}>();

defineSlots<{
  actions?: (props: { row: T }) => unknown;
}>();

function formatCell(row: T, column: DataTableColumn<T>): string {
  if (column.format) {
    return column.format(row);
  }

  const value = (row as unknown as Record<string, unknown>)[column.id];

  if (value === null || value === undefined || value === "") {
    return "-";
  }

  return String(value);
}
</script>

<template>
  <div class="overflow-hidden rounded-md border border-line bg-white shadow-panel">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[760px] border-collapse text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
          <tr>
            <th v-for="column in columns" :key="column.id" class="border-b border-line px-4 py-3" :class="column.className">
              <button
                v-if="column.sortable !== false"
                class="focus-ring inline-flex items-center gap-1 rounded text-left hover:text-ink"
                type="button"
                @click="emit('sort', column)"
              >
                {{ column.label }}
                <ArrowUp v-if="sort?.columnId === column.id && sort.direction === 'asc'" class="h-3.5 w-3.5" />
                <ArrowDown v-else-if="sort?.columnId === column.id && sort.direction === 'desc'" class="h-3.5 w-3.5" />
                <ChevronsUpDown v-else class="h-3.5 w-3.5 text-slate-300" />
              </button>
              <span v-else>{{ column.label }}</span>
            </th>
            <th v-if="$slots.actions" class="border-b border-line px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-4 py-8 text-center text-muted">
              <span class="inline-flex items-center gap-2">
                <Loader2 class="h-4 w-4 animate-spin" />
                Loading records
              </span>
            </td>
          </tr>
          <tr v-else-if="rows.length === 0">
            <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-4 py-8 text-center text-muted">
              {{ emptyMessage }}
            </td>
          </tr>
          <template v-else>
            <tr v-for="row in rows" :key="row.id" class="border-t border-line hover:bg-slate-50/70">
              <td v-for="column in columns" :key="column.id" class="px-4 py-3 align-middle text-slate-700" :class="column.className">
                {{ formatCell(row, column) }}
              </td>
              <td v-if="$slots.actions" class="px-4 py-3 text-right">
                <div class="inline-flex items-center justify-end gap-2">
                  <slot name="actions" :row="row" />
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>
