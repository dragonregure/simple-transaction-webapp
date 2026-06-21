<script setup lang="ts">
import { ref } from "vue";
import { Pencil, Plus, Trash2 } from "@lucide/vue";
import AppModal from "@/components/AppModal.vue";
import DataTable from "@/components/data-table/DataTable.vue";
import ServerSelect from "@/components/forms/ServerSelect.vue";
import PageHeader from "@/components/PageHeader.vue";
import {
  createTransaction,
  deleteTransaction,
  getAccountOptions,
  getTransaction,
  getTransactionsPage,
  updateTransaction,
} from "@/services/api";
import { formatMoney, today } from "@/lib/format";
import type { DataTableColumn, Transaction, TransactionPayload } from "@/types";

const columns: DataTableColumn<Transaction>[] = [
  { id: "transaction_date", label: "Date", name: "transaction_date" },
  { id: "chart_of_account_code", label: "COA Code", name: "chart_of_accounts.code" },
  { id: "chart_of_account_name", label: "COA Name", name: "chart_of_accounts.name" },
  { id: "description", label: "Description", name: "description" },
  { id: "debit", label: "Debit", name: "debit", className: "text-right", format: (row) => formatMoney(row.debit) },
  { id: "credit", label: "Credit", name: "credit", className: "text-right", format: (row) => formatMoney(row.credit) },
];

const blankDraft = (): TransactionPayload => ({
  idempotency_key: crypto.randomUUID(),
  transaction_date: today(),
  chart_of_account_id: 0,
  description: "",
  amount: 1,
});

const refreshKey = ref(0);
const draft = ref<TransactionPayload>(blankDraft());
const selectedAccountText = ref("");
const editingId = ref<string | null>(null);
const modalOpen = ref(false);
const saving = ref(false);
const error = ref("");

function openCreate() {
  editingId.value = null;
  selectedAccountText.value = "";
  draft.value = blankDraft();
  modalOpen.value = true;
}

async function openEdit(row: Transaction) {
  error.value = "";
  const response = await getTransaction(row.id);
  const transaction = response.data;

  editingId.value = transaction.id;
  selectedAccountText.value = transaction.chart_of_account?.text ?? "";
  draft.value = {
    idempotency_key: transaction.idempotency_key ?? crypto.randomUUID(),
    transaction_date: transaction.transaction_date,
    chart_of_account_id: Number(transaction.chart_of_account_id),
    description: transaction.description ?? "",
    amount: Number(transaction.amount ?? 1),
  };
  modalOpen.value = true;
}

async function saveTransaction() {
  saving.value = true;
  error.value = "";

  try {
    if (editingId.value) {
      await updateTransaction(editingId.value, draft.value);
    } else {
      await createTransaction(draft.value);
    }

    modalOpen.value = false;
    refreshKey.value += 1;
  } catch (caught) {
    error.value = caught instanceof Error ? caught.message : "Unable to save transaction.";
  } finally {
    saving.value = false;
  }
}

async function removeTransaction(row: Transaction) {
  if (!confirm("Delete this transaction?")) return;

  error.value = "";
  await deleteTransaction(row.id);
  refreshKey.value += 1;
}

</script>

<template>
  <section>
    <PageHeader eyebrow="Transactions" title="Transactions" description="Create, update, search, sort, and paginate transaction records.">
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
      empty-message="No transactions found."
      :fetch-rows="(state, context) => getTransactionsPage(columns, state, context)"
      :refresh-key="refreshKey"
      @error="error = $event"
    >
      <template #actions="{ row }">
        <button class="focus-ring rounded-md border border-line p-2 text-slate-700 hover:bg-slate-50" type="button" title="Edit" aria-label="Edit" @click="openEdit(row)">
          <Pencil class="h-4 w-4" />
        </button>
        <button class="focus-ring rounded-md border border-red-200 p-2 text-red-700 hover:bg-red-50" type="button" title="Delete" aria-label="Delete" @click="removeTransaction(row)">
          <Trash2 class="h-4 w-4" />
        </button>
      </template>
    </DataTable>

    <AppModal
      :open="modalOpen"
      :title="editingId ? 'Update Transaction' : 'Create Transaction'"
      submit-label="Save Transaction"
      :submitting="saving"
      @close="modalOpen = false"
      @submit="saveTransaction"
    >
      <div class="grid gap-4 md:grid-cols-2">
        <label class="grid gap-1.5">
          <span class="text-sm font-medium text-slate-700">Date</span>
          <input v-model="draft.transaction_date" class="focus-ring h-10 rounded-md border border-line px-3 text-sm" type="date" required />
        </label>
        <label class="grid gap-1.5">
          <span class="text-sm font-medium text-slate-700">Amount</span>
          <input v-model.number="draft.amount" class="focus-ring h-10 rounded-md border border-line px-3 text-sm" type="number" min="1" required />
        </label>
      </div>
      <ServerSelect
        v-model="draft.chart_of_account_id"
        v-model:selected-text="selectedAccountText"
        label="Chart of Account"
        placeholder="Search account code or name"
        required
        :load-options="getAccountOptions"
      />
      <label class="grid gap-1.5">
        <span class="text-sm font-medium text-slate-700">Description</span>
        <textarea v-model="draft.description" class="focus-ring min-h-24 rounded-md border border-line px-3 py-2 text-sm" maxlength="1000" />
      </label>
    </AppModal>
  </section>
</template>
