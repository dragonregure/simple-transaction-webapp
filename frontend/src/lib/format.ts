export function formatMoney(value: number | string | null | undefined): string {
  const normalized = typeof value === "string" ? Number(value.replace(/,/g, "")) : Number(value ?? 0);

  return new Intl.NumberFormat("en-US", {
    maximumFractionDigits: 0,
  }).format(Number.isFinite(normalized) ? normalized : 0);
}

export function today(): string {
  return new Date().toISOString().slice(0, 10);
}
