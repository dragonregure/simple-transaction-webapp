import type { DataTableColumn, DataTableQueryState } from "@/types";

export function toDataTablesQuery<T>(columns: DataTableColumn<T>[], state: DataTableQueryState): string {
  const query = new URLSearchParams();
  const sortColumn = state.sort ? columns.findIndex((column) => column.id === state.sort?.columnId) : -1;

  query.set("draw", String(Date.now()));
  query.set("start", String((state.page - 1) * state.pageSize));
  query.set("length", String(state.pageSize));
  query.set("search[value]", state.search.trim());
  query.set("search[regex]", "false");

  columns.forEach((column, index) => {
    query.set(`columns[${index}][data]`, column.id);
    query.set(`columns[${index}][name]`, column.name ?? column.id);
    query.set(`columns[${index}][searchable]`, String(column.searchable ?? true));
    query.set(`columns[${index}][orderable]`, String(column.sortable ?? true));
    query.set(`columns[${index}][search][value]`, "");
    query.set(`columns[${index}][search][regex]`, "false");
  });

  if (sortColumn >= 0 && state.sort) {
    query.set("order[0][column]", String(sortColumn));
    query.set("order[0][dir]", state.sort.direction);
  }

  return query.toString();
}
