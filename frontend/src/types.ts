export type SortDirection = "asc" | "desc";

export type DataTableSort = {
  columnId: string;
  direction: SortDirection;
};

export type DataTableQueryState = {
  page: number;
  pageSize: number;
  search: string;
  sort: DataTableSort | null;
};

export type DataTableColumn<T> = {
  id: string;
  label: string;
  name?: string;
  sortable?: boolean;
  searchable?: boolean;
  className?: string;
  format?: (row: T) => string;
};

export type DataTableResult<T> = {
  data: T[];
  draw: number;
  recordsTotal: number;
  recordsFiltered: number;
};

export type SelectOption = {
  id: number | string;
  text: string;
};

export type SelectOptionsResult = {
  results: SelectOption[];
  pagination: {
    more: boolean;
  };
};

export type ApiResource<T> = {
  data: T;
};

export type Transaction = {
  id: string;
  idempotency_key: string | null;
  transaction_date: string;
  chart_of_account_id: number;
  chart_of_account_code?: string;
  chart_of_account_name?: string;
  chart_of_account?: SelectOption & {
    code: string;
    name: string;
  };
  description: string | null;
  amount?: number;
  debit: number | string;
  credit: number | string;
};

export type TransactionPayload = {
  idempotency_key: string;
  transaction_date: string;
  chart_of_account_id: number;
  description: string;
  amount: number;
};

export type ChartOfAccount = {
  id: number;
  code: string;
  name: string;
  account_type: string;
  account_type_label?: string;
  category_id: number;
  category?: {
    id: number;
    name: string;
  } | null;
};

export type ChartOfAccountPayload = {
  code: string;
  name: string;
  account_type: string;
  category_id: number;
};

export type ChartOfAccountCategory = {
  id: number;
  name: string;
  created_at?: string;
  updated_at?: string;
};

export type ChartOfAccountCategoryPayload = {
  name: string;
};

export type AccountTypeOption = {
  value: string;
  label: string;
};

export type MonthlyReport = {
  year: number;
  months: Record<string, string>;
  income_rows: ReportRow[];
  expense_rows: ReportRow[];
  total_income: Record<string, number>;
  total_expense: Record<string, number>;
  net_income: Record<string, number>;
};

export type ReportRow = {
  label: string;
  amounts: Record<string, number>;
};

export type ReportResponse = {
  available_years: number[];
  selected_year: number;
  report: MonthlyReport;
};
