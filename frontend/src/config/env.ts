function normalizeUrl(url: string): string {
  const normalizedUrl = url.replace(/\/$/, "");

  return normalizedUrl === "" ? "/" : normalizedUrl;
}

export const API_BASE_URL = normalizeUrl(import.meta.env.VITE_API_BASE_URL ?? "/api/v1");
export const BLADE_APP_URL = normalizeUrl(
  import.meta.env.VITE_BLADE_APP_URL ?? API_BASE_URL.replace(/\/api\/v1$/, ""),
);
