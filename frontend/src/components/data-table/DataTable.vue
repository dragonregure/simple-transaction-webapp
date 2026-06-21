<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue";
import DataTableContent from "@/components/data-table/DataTableContent.vue";
import DataTablePagination from "@/components/data-table/DataTablePagination.vue";
import DataTableToolbar from "@/components/data-table/DataTableToolbar.vue";
import { isAbortError } from "@/services/httpClient";
import type { DataTableColumn, DataTableQueryState, DataTableResult, DataTableSort } from "@/types";

const props = withDefaults(
  defineProps<{
    columns: DataTableColumn<any>[];
    fetchRows: (state: DataTableQueryState, context: { signal: AbortSignal }) => Promise<DataTableResult<any>>;
    emptyMessage?: string;
    initialPageSize?: number;
    pageSizeOptions?: number[];
    refreshKey?: number;
  }>(),
  {
    emptyMessage: "No records found.",
    initialPageSize: 10,
    pageSizeOptions: () => [10, 25, 50, 100],
    refreshKey: 0,
  },
);

const emit = defineEmits<{
  "state-change": [state: DataTableQueryState];
  error: [message: string];
}>();

const rows = ref<any[]>([]);
const page = ref(1);
const pageSize = ref(props.initialPageSize);
const search = ref("");
const debouncedSearch = ref("");
const sort = ref<DataTableSort | null>(null);
const totalRows = ref(0);
const loading = ref(false);
let searchTimeoutId: number | undefined;
let controller: AbortController | null = null;

const pageCount = computed(() => Math.max(1, Math.ceil(totalRows.value / pageSize.value)));
const queryState = computed<DataTableQueryState>(() => ({
  page: page.value,
  pageSize: pageSize.value,
  search: debouncedSearch.value,
  sort: sort.value,
}));

async function loadRows() {
  controller?.abort();
  controller = new AbortController();
  const activeController = controller;
  loading.value = true;
  emit("state-change", queryState.value);

  try {
    const result = await props.fetchRows(queryState.value, { signal: activeController.signal });
    rows.value = result.data;
    totalRows.value = result.recordsFiltered;
  } catch (caught) {
    if (!isAbortError(caught)) {
      emit("error", caught instanceof Error ? caught.message : "Unable to load records.");
    }
  } finally {
    if (!activeController.signal.aborted) {
      loading.value = false;
    }
  }
}

function handleSort(column: DataTableColumn<any>) {
  if (column.sortable === false) return;

  if (sort.value?.columnId !== column.id) {
    sort.value = { columnId: column.id, direction: "asc" };
    return;
  }

  if (sort.value.direction === "asc") {
    sort.value = { columnId: column.id, direction: "desc" };
    return;
  }

  sort.value = null;
}

watch(search, () => {
  window.clearTimeout(searchTimeoutId);
  searchTimeoutId = window.setTimeout(() => {
    debouncedSearch.value = search.value;
  }, 600);
});

watch([debouncedSearch, pageSize, sort], () => {
  page.value = 1;
});

watch([page, pageSize, debouncedSearch, sort, () => props.refreshKey], () => {
  void loadRows();
}, { immediate: true });

watch(pageCount, (nextPageCount) => {
  if (page.value > nextPageCount) {
    page.value = nextPageCount;
  }
});

onBeforeUnmount(() => {
  window.clearTimeout(searchTimeoutId);
  controller?.abort();
});
</script>

<template>
  <div class="grid gap-3">
    <DataTableToolbar v-model:search="search">
      <template v-if="$slots.toolbarActions" #actions>
        <slot name="toolbarActions" :state="queryState" />
      </template>
    </DataTableToolbar>

    <DataTableContent
      :columns="columns"
      :empty-message="emptyMessage"
      :loading="loading"
      :rows="rows"
      :sort="sort"
      @sort="handleSort"
    >
      <template v-if="$slots.actions" #actions="{ row }">
        <slot name="actions" :row="row" />
      </template>
    </DataTableContent>

    <DataTablePagination
      v-model:page="page"
      v-model:page-size="pageSize"
      :page-count="pageCount"
      :page-size-options="pageSizeOptions"
      :total-rows="totalRows"
    />
  </div>
</template>
