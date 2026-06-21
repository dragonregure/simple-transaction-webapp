<script setup lang="ts">
import { ref } from "vue";
import { Pencil, Plus, Trash2 } from "@lucide/vue";
import AppModal from "@/components/AppModal.vue";
import DataTable from "@/components/data-table/DataTable.vue";
import PageHeader from "@/components/PageHeader.vue";
import {
  createChartOfAccountCategory,
  deleteChartOfAccountCategory,
  getChartOfAccountCategoriesPage,
  getChartOfAccountCategory,
  updateChartOfAccountCategory,
} from "@/services/api";
import type {
  ChartOfAccountCategory,
  ChartOfAccountCategoryPayload,
  DataTableColumn,
} from "@/types";

const columns: DataTableColumn<ChartOfAccountCategory>[] = [
  { id: "name", label: "Name", name: "name" },
  { id: "created_at", label: "Created At", name: "created_at", searchable: false },
];

const refreshKey = ref(0);
const draft = ref<ChartOfAccountCategoryPayload>({ name: "" });
const editingId = ref<number | null>(null);
const modalOpen = ref(false);
const saving = ref(false);
const error = ref("");

function openCreate() {
  editingId.value = null;
  draft.value = { name: "" };
  modalOpen.value = true;
}

async function openEdit(row: ChartOfAccountCategory) {
  error.value = "";
  const response = await getChartOfAccountCategory(row.id);

  editingId.value = response.data.id;
  draft.value = { name: response.data.name };
  modalOpen.value = true;
}

async function saveCategory() {
  saving.value = true;
  error.value = "";

  try {
    if (editingId.value) {
      await updateChartOfAccountCategory(editingId.value, draft.value);
    } else {
      await createChartOfAccountCategory(draft.value);
    }

    modalOpen.value = false;
    refreshKey.value += 1;
  } catch (caught) {
    error.value = caught instanceof Error ? caught.message : "Unable to save category.";
  } finally {
    saving.value = false;
  }
}

async function removeCategory(row: ChartOfAccountCategory) {
  if (!confirm("Delete this category?")) return;

  try {
    error.value = "";
    await deleteChartOfAccountCategory(row.id);
    refreshKey.value += 1;
  } catch (caught) {
    error.value = caught instanceof Error ? caught.message : "Unable to delete category.";
  }
}
</script>

<template>
  <section>
    <PageHeader eyebrow="Master Data" title="COA Categories" description="Group chart of accounts for reporting and account maintenance.">
      <template #actions>
        <button class="focus-ring inline-flex items-center gap-2 rounded-md bg-cyan-700 px-3 py-2 text-sm font-semibold text-white hover:bg-cyan-800" type="button" @click="openCreate">
          <Plus class="h-4 w-4" />
          Add
        </button>
      </template>
    </PageHeader>

    <div v-if="error" class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ error }}</div>

    <DataTable
      :columns="columns"
      empty-message="No categories found."
      :fetch-rows="(state, context) => getChartOfAccountCategoriesPage(columns, state, context)"
      :page-size-options="[5, 10, 15, 25, 50]"
      :refresh-key="refreshKey"
      @error="error = $event"
    >
      <template #actions="{ row }">
        <button class="focus-ring rounded-md border border-line p-2 text-slate-700 hover:bg-slate-50" type="button" title="Edit" aria-label="Edit" @click="openEdit(row)">
          <Pencil class="h-4 w-4" />
        </button>
        <button class="focus-ring rounded-md border border-red-200 p-2 text-red-700 hover:bg-red-50" type="button" title="Delete" aria-label="Delete" @click="removeCategory(row)">
          <Trash2 class="h-4 w-4" />
        </button>
      </template>
    </DataTable>

    <AppModal
      :open="modalOpen"
      :title="editingId ? 'Update COA Category' : 'Create COA Category'"
      submit-label="Save Category"
      :submitting="saving"
      @close="modalOpen = false"
      @submit="saveCategory"
    >
      <label class="grid gap-1.5">
        <span class="text-sm font-medium text-slate-700">Name</span>
        <input v-model="draft.name" class="focus-ring h-10 rounded-md border border-line px-3 text-sm" required maxlength="255" />
      </label>
    </AppModal>
  </section>
</template>
