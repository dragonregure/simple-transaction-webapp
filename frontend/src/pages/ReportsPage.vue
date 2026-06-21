<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import { Download, Loader2 } from "@lucide/vue";
import PageHeader from "@/components/PageHeader.vue";
import { exportReport, getReport } from "@/services/api";
import { formatMoney } from "@/lib/format";
import type { MonthlyReport, ReportRow } from "@/types";

const selectedYear = ref<number | null>(null);
const availableYears = ref<number[]>([]);
const report = ref<MonthlyReport | null>(null);
const loading = ref(true);
const exporting = ref(false);
const error = ref("");

const monthKeys = computed(() => Object.keys(report.value?.months ?? {}).sort((a, b) => Number(a) - Number(b)));

const rows = computed(() => {
  if (!report.value) return [];

  return [
    ...report.value.income_rows.map((row) => ({ ...row, section: "Income" })),
    { label: "Total Income", amounts: report.value.total_income, section: "Total" },
    ...report.value.expense_rows.map((row) => ({ ...row, section: "Expense" })),
    { label: "Total Expense", amounts: report.value.total_expense, section: "Total" },
    { label: "Net Income", amounts: report.value.net_income, section: "Net" },
  ] as Array<ReportRow & { section: string }>;
});

async function loadReport(year?: number | null) {
  loading.value = true;
  error.value = "";

  try {
    const response = await getReport(year ?? undefined);
    availableYears.value = response.data.available_years;
    selectedYear.value = response.data.selected_year;
    report.value = response.data.report;
  } catch (caught) {
    error.value = caught instanceof Error ? caught.message : "Unable to load report.";
  } finally {
    loading.value = false;
  }
}

async function downloadReport() {
  if (!selectedYear.value) return;

  exporting.value = true;
  error.value = "";

  try {
    await exportReport(selectedYear.value);
  } catch (caught) {
    error.value = caught instanceof Error ? caught.message : "Unable to export report.";
  } finally {
    exporting.value = false;
  }
}

onMounted(() => {
  void loadReport();
});

watch(selectedYear, (year, previous) => {
  if (year && previous && year !== previous) {
    void loadReport(year);
  }
});
</script>

<template>
  <section>
    <PageHeader eyebrow="Reports" title="Monthly Category Report" description="Review income, expense, and net income by category for the selected year.">
      <template #actions>
        <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
          Year
          <select v-model.number="selectedYear" class="focus-ring h-10 rounded-md border border-line bg-white px-3 text-sm">
            <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
          </select>
        </label>
        <button
          class="focus-ring inline-flex items-center gap-2 rounded-md border border-line bg-white px-3 py-2 text-sm font-medium hover:bg-slate-50 disabled:opacity-60"
          type="button"
          :disabled="exporting || !selectedYear"
          @click="downloadReport"
        >
          <Loader2 v-if="exporting" class="h-4 w-4 animate-spin" />
          <Download v-else class="h-4 w-4" />
          XLSX
        </button>
      </template>
    </PageHeader>

    <div v-if="error" class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ error }}</div>

    <div class="overflow-hidden rounded-md border border-line bg-white shadow-panel">
      <div v-if="loading" class="flex h-64 items-center justify-center gap-2 text-sm text-muted">
        <Loader2 class="h-4 w-4 animate-spin" />
        Loading report
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full min-w-[980px] border-collapse text-sm">
          <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
              <th class="sticky left-0 z-10 border-b border-line bg-slate-50 px-4 py-3">Category</th>
              <th v-for="month in monthKeys" :key="month" class="border-b border-line px-4 py-3 text-right">
                {{ report?.months[month] }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in rows" :key="`${row.section}-${row.label}`" class="border-t border-line" :class="row.section === 'Net' ? 'bg-cyan-50 font-bold text-cyan-950' : row.section === 'Total' ? 'bg-slate-50 font-semibold' : ''">
              <td class="sticky left-0 border-r border-line bg-inherit px-4 py-3 text-slate-800">{{ row.label }}</td>
              <td v-for="month in monthKeys" :key="month" class="px-4 py-3 text-right tabular-nums">
                {{ formatMoney(row.amounts[month] ?? 0) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</template>
