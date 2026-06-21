import { toDataTablesQuery } from "@/services/dataTableParams";
import { apiRequest, downloadRequest } from "@/services/httpClient";
import type {
  AccountTypeOption,
  ApiResource,
  ChartOfAccount,
  ChartOfAccountCategory,
  ChartOfAccountCategoryPayload,
  ChartOfAccountPayload,
  DataTableColumn,
  DataTableQueryState,
  DataTableResult,
  ReportResponse,
  SelectOptionsResult,
  Transaction,
  TransactionPayload,
} from "@/types";

type RequestContext = {
  signal?: AbortSignal;
};

function withQuery(path: string, query: string): string {
  return query ? `${path}?${query}` : path;
}

export function getTransactionsPage(
  columns: DataTableColumn<Transaction>[],
  state: DataTableQueryState,
  context: RequestContext = {},
) {
  return apiRequest<DataTableResult<Transaction>>(withQuery("/transactions", toDataTablesQuery(columns, state)), {
    signal: context.signal,
  });
}

export function createTransaction(payload: TransactionPayload) {
  return apiRequest<ApiResource<Transaction>>("/transactions", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export function getTransaction(id: string) {
  return apiRequest<ApiResource<Transaction>>(`/transactions/${id}`);
}

export function updateTransaction(id: string, payload: TransactionPayload) {
  return apiRequest<ApiResource<Transaction>>(`/transactions/${id}`, {
    method: "PUT",
    body: JSON.stringify(payload),
  });
}

export function deleteTransaction(id: string) {
  return apiRequest<void>(`/transactions/${id}`, {
    method: "DELETE",
  });
}

export function getChartOfAccountsPage(
  columns: DataTableColumn<ChartOfAccount>[],
  state: DataTableQueryState,
  context: RequestContext = {},
) {
  return apiRequest<DataTableResult<ChartOfAccount>>(withQuery("/chart-of-accounts", toDataTablesQuery(columns, state)), {
    signal: context.signal,
  });
}

export function createChartOfAccount(payload: ChartOfAccountPayload) {
  return apiRequest<ApiResource<ChartOfAccount>>("/chart-of-accounts", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export function getChartOfAccount(id: number) {
  return apiRequest<ApiResource<ChartOfAccount>>(`/chart-of-accounts/${id}`);
}

export function updateChartOfAccount(id: number, payload: ChartOfAccountPayload) {
  return apiRequest<ApiResource<ChartOfAccount>>(`/chart-of-accounts/${id}`, {
    method: "PUT",
    body: JSON.stringify(payload),
  });
}

export function deleteChartOfAccount(id: number) {
  return apiRequest<void>(`/chart-of-accounts/${id}`, {
    method: "DELETE",
  });
}

export function getAccountTypes() {
  return apiRequest<{ data: AccountTypeOption[] }>("/chart-of-accounts/types");
}

export function getAccountOptions(search: string, page: number, signal?: AbortSignal) {
  const query = new URLSearchParams({
    term: search,
    page: String(page),
    per_page: "20",
  });

  return apiRequest<SelectOptionsResult>(`/chart-of-accounts/select-options?${query.toString()}`, { signal });
}

export function getChartOfAccountCategoriesPage(
  columns: DataTableColumn<ChartOfAccountCategory>[],
  state: DataTableQueryState,
  context: RequestContext = {},
) {
  return apiRequest<DataTableResult<ChartOfAccountCategory>>(
    withQuery("/chart-of-account-categories", toDataTablesQuery(columns, state)),
    { signal: context.signal },
  );
}

export function createChartOfAccountCategory(payload: ChartOfAccountCategoryPayload) {
  return apiRequest<ApiResource<ChartOfAccountCategory>>("/chart-of-account-categories", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export function getChartOfAccountCategory(id: number) {
  return apiRequest<ApiResource<ChartOfAccountCategory>>(`/chart-of-account-categories/${id}`);
}

export function updateChartOfAccountCategory(id: number, payload: ChartOfAccountCategoryPayload) {
  return apiRequest<ApiResource<ChartOfAccountCategory>>(`/chart-of-account-categories/${id}`, {
    method: "PUT",
    body: JSON.stringify(payload),
  });
}

export function deleteChartOfAccountCategory(id: number) {
  return apiRequest<void>(`/chart-of-account-categories/${id}`, {
    method: "DELETE",
  });
}

export function getCategoryOptions(search: string, page: number, signal?: AbortSignal) {
  const query = new URLSearchParams({
    term: search,
    page: String(page),
    per_page: "20",
  });

  return apiRequest<SelectOptionsResult>(`/chart-of-account-categories/select-options?${query.toString()}`, { signal });
}

export function getReport(year?: number) {
  const query = new URLSearchParams();

  if (year) {
    query.set("year", String(year));
  }

  return apiRequest<ApiResource<ReportResponse>>(withQuery("/reports", query.toString()));
}

export function exportReport(year: number) {
  return downloadRequest(`/reports/export?year=${year}`, `transaction-report-${year}.xlsx`);
}
