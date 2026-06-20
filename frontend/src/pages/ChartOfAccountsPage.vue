<script setup lang="ts">
import { onMounted, ref } from "vue";
import { Pencil, Plus, Trash2 } from "@lucide/vue";
import AppModal from "@/components/AppModal.vue";
import DataTable from "@/components/data-table/DataTable.vue";
import ServerSelect from "@/components/forms/ServerSelect.vue";
import PageHeader from "@/components/PageHeader.vue";
import {
  createChartOfAccount,
  deleteChartOfAccount,
  getAccountTypes,
  getCategoryOptions,
  getChartOfAccount,
  getChartOfAccountsPage,
  updateChartOfAccount,
} from "@/services/api";
import type { AccountTypeOption, ChartOfAccount, ChartOfAccountPayload, DataTableColumn } from "@/types";

const columns: DataTableColumn<ChartOfAccount>[] = [
  { id: "code", label: "Code", name: "code" },
  { id: "name", label: "Name", name: "name" },
  { id: "account_type", label: "Type", name: "account_type", format: (row) => row.account_type_label ?? row.account_type },
  { id: "category", label: "Category", name: "category.name", format: (row) => row.category?.name ?? String((row as any).category ?? "-") },
];

const blankDraft = (): ChartOfAccountPayload => ({
  code: "",
  name: "",
  account_type: "expense",
  category_id: 0,
});

const refreshKey = ref(0);
const accountTypes = ref<AccountTypeOption[]>([]);
const draft = ref<ChartOfAccountPayload>(blankDraft());
const selectedCategoryText = ref("");
const editingId = ref<number | null>(null);
const modalOpen = ref(false);
const saving = ref(false);
const error = ref("");

onMounted(async () => {
  accountTypes.value = (await getAccountTypes()).data;
});

function openCreate() {
  editingId.value = null;
  selectedCategoryText.value = "";
  draft.value = blankDraft();
  modalOpen.value = true;
}

async function openEdit(row: ChartOfAccount) {
  error.value = "";
  const response = await getChartOfAccount(row.id);
  const account = response.data;

  editingId.value = account.id;
  selectedCategoryText.value = account.category?.name ?? "";
  draft.value = {
    code: account.code,
    name: account.name,
    account_type: account.account_type,
    category_id: account.category_id,
  };
  modalOpen.value = true;
}

async function saveAccount() {
  saving.value = true;
  error.value = "";

  try {
    if (editingId.value) {
      await updateChartOfAccount(editingId.value, draft.value);
    } else {
      await createChartOfAccount(draft.value);
    }

    modalOpen.value = false;
    refreshKey.value += 1;
  } catch (caught) {
    error.value = caught instanceof Error ? caught.message : "Unable to save chart of account.";
  } finally {
    saving.value = false;
  }
}

async function removeAccount(row: ChartOfAccount) {
  if (!confirm("Delete this chart of account?")) return;

  try {
    error.value = "";
    await deleteChartOfAccount(row.id);
    refreshKey.value += 1;
  } catch (caught) {
    error.value = caught instanceof Error ? caught.message : "Unable to delete chart of account.";
  }
}
</script>

<template>
  <section>
    <PageHeader eyebrow="Master Data" title="Chart of Accounts" description="Maintain the accounts used by transaction entry and reporting.">
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
      empty-message="No chart of accounts found."
      :fetch-rows="(state, context) => getChartOfAccountsPage(columns, state, context)"
      :page-size-options="[5, 10, 15, 25, 50]"
      :refresh-key="refreshKey"
      @error="error = $event"
    >
      <template #actions="{ row }">
        <button class="focus-ring rounded-md border border-line p-2 text-slate-700 hover:bg-slate-50" type="button" title="Edit" aria-label="Edit" @click="openEdit(row)">
          <Pencil class="h-4 w-4" />
        </button>
        <button class="focus-ring rounded-md border border-red-200 p-2 text-red-700 hover:bg-red-50" type="button" title="Delete" aria-label="Delete" @click="removeAccount(row)">
          <Trash2 class="h-4 w-4" />
        </button>
      </template>
    </DataTable>

    <AppModal
      :open="modalOpen"
      :title="editingId ? 'Update Chart of Account' : 'Create Chart of Account'"
      submit-label="Save Account"
      :submitting="saving"
      @close="modalOpen = false"
      @submit="saveAccount"
    >
      <div class="grid gap-4 md:grid-cols-2">
        <label class="grid gap-1.5">
          <span class="text-sm font-medium text-slate-700">Code</span>
          <input v-model="draft.code" class="focus-ring h-10 rounded-md border border-line px-3 text-sm" required maxlength="255" />
        </label>
        <label class="grid gap-1.5">
          <span class="text-sm font-medium text-slate-700">Account Type</span>
          <select v-model="draft.account_type" class="focus-ring h-10 rounded-md border border-line bg-white px-3 text-sm" required>
            <option v-for="option in accountTypes" :key="option.value" :value="option.value">{{ option.label }}</option>
          </select>
        </label>
      </div>
      <label class="grid gap-1.5">
        <span class="text-sm font-medium text-slate-700">Name</span>
        <input v-model="draft.name" class="focus-ring h-10 rounded-md border border-line px-3 text-sm" required maxlength="255" />
      </label>
      <ServerSelect
        v-model="draft.category_id"
        v-model:selected-text="selectedCategoryText"
        label="Category"
        placeholder="Search category"
        required
        :load-options="getCategoryOptions"
      />
    </AppModal>
  </section>
</template>
