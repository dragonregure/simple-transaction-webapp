<script setup lang="ts">
import { computed, ref } from "vue";
import { RouterLink, RouterView, useRoute } from "vue-router";
import { BarChart3, ChevronDown, FolderTree, Menu, PanelLeftClose, PanelLeftOpen, ReceiptText, Tags, X } from "@lucide/vue";

const route = useRoute();
const sidebarOpen = ref(false);
const minimized = ref(false);

const navigation = [
  { label: "Transactions", href: "/transactions", icon: ReceiptText },
  { label: "Reports", href: "/reports", icon: BarChart3 },
];

const masterNavigation = [
  { label: "COA", href: "/master/chart-of-accounts", icon: FolderTree },
  { label: "COA Categories", href: "/master/chart-of-account-categories", icon: Tags },
];

const isMasterActive = computed(() => route.path.startsWith("/master"));
</script>

<template>
  <div class="min-h-screen bg-background">
    <aside
      class="fixed inset-y-0 left-0 z-40 hidden border-r border-line bg-white transition-[width] duration-200 lg:block"
      :class="minimized ? 'w-20' : 'w-64'"
    >
      <div class="flex h-full flex-col gap-5 p-4" :class="minimized && 'px-3'">
        <div class="flex items-center gap-3" :class="minimized ? 'justify-center' : 'justify-between'">
          <RouterLink to="/transactions" class="flex min-w-0 items-center gap-3">
            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-cyan-700 text-sm font-bold text-white">ST</div>
            <div v-if="!minimized" class="min-w-0">
              <p class="truncate text-sm font-bold text-ink">Simple Transaction</p>
              <p class="truncate text-xs text-muted">Finance workspace</p>
            </div>
          </RouterLink>
          <button
            class="focus-ring rounded-md p-2 text-slate-500 hover:bg-slate-100"
            :title="minimized ? 'Expand sidebar' : 'Minimize sidebar'"
            :aria-label="minimized ? 'Expand sidebar' : 'Minimize sidebar'"
            type="button"
            @click="minimized = !minimized"
          >
            <PanelLeftOpen v-if="minimized" class="h-4 w-4" />
            <PanelLeftClose v-else class="h-4 w-4" />
          </button>
        </div>

        <nav class="grid gap-1">
          <RouterLink
            v-for="item in navigation"
            :key="item.href"
            :to="item.href"
            class="focus-ring flex h-10 items-center gap-3 rounded-md px-3 text-sm font-medium text-slate-600 hover:bg-cyan-50 hover:text-cyan-900"
            :class="{ 'bg-cyan-100 text-cyan-950': route.path === item.href, 'justify-center px-0': minimized }"
            :title="minimized ? item.label : undefined"
          >
            <component :is="item.icon" class="h-4 w-4 shrink-0" />
            <span v-if="!minimized" class="truncate">{{ item.label }}</span>
          </RouterLink>

          <div class="mt-2">
            <div
              class="flex h-9 items-center gap-2 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400"
              :class="minimized && 'justify-center px-0'"
            >
              <ChevronDown class="h-3.5 w-3.5" :class="{ 'text-cyan-700': isMasterActive }" />
              <span v-if="!minimized">Master Data</span>
            </div>
            <RouterLink
              v-for="item in masterNavigation"
              :key="item.href"
              :to="item.href"
              class="focus-ring flex h-10 items-center gap-3 rounded-md px-3 text-sm font-medium text-slate-600 hover:bg-emerald-50 hover:text-emerald-900"
              :class="{ 'bg-emerald-100 text-emerald-950': route.path === item.href, 'justify-center px-0': minimized }"
              :title="minimized ? item.label : undefined"
            >
              <component :is="item.icon" class="h-4 w-4 shrink-0" />
              <span v-if="!minimized" class="truncate">{{ item.label }}</span>
            </RouterLink>
          </div>
        </nav>

        <div v-if="!minimized" class="mt-auto rounded-md border border-line bg-slate-50 p-3">
          <p class="text-xs font-semibold uppercase text-slate-500">Mode</p>
          <p class="mt-1 text-sm font-semibold text-ink">Vue SPA</p>
        </div>
      </div>
    </aside>

    <div class="transition-[padding] duration-200" :class="minimized ? 'lg:pl-20' : 'lg:pl-64'">
      <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-line bg-white/95 px-4 backdrop-blur md:px-6">
        <button class="focus-ring rounded-md p-2 text-slate-600 hover:bg-slate-100 lg:hidden" type="button" aria-label="Open navigation" @click="sidebarOpen = true">
          <Menu class="h-5 w-5" />
        </button>
        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-semibold text-ink">Simple Transaction Webapp</p>
          <p class="truncate text-xs text-muted">Laravel API with Vue client</p>
        </div>
        <a class="focus-ring rounded-md border border-line px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="http://localhost:8000" target="_blank" rel="noreferrer">
          Blade app
        </a>
      </header>

      <main class="mx-auto w-full max-w-7xl p-4 md:p-6">
        <RouterView />
      </main>
    </div>

    <div v-if="sidebarOpen" class="fixed inset-0 z-50 bg-black/30 lg:hidden" @click="sidebarOpen = false">
      <aside class="h-full w-72 bg-white p-4 shadow-xl" @click.stop>
        <div class="mb-5 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="grid h-10 w-10 place-items-center rounded-lg bg-cyan-700 text-sm font-bold text-white">ST</div>
            <span class="font-bold text-ink">Simple Transaction</span>
          </div>
          <button class="focus-ring rounded-md p-2 text-slate-500" type="button" aria-label="Close navigation" @click="sidebarOpen = false">
            <X class="h-5 w-5" />
          </button>
        </div>
        <nav class="grid gap-1">
          <RouterLink
            v-for="item in [...navigation, ...masterNavigation]"
            :key="item.href"
            :to="item.href"
            class="focus-ring flex h-10 items-center gap-3 rounded-md px-3 text-sm font-medium text-slate-600 hover:bg-slate-100"
            @click="sidebarOpen = false"
          >
            <component :is="item.icon" class="h-4 w-4" />
            {{ item.label }}
          </RouterLink>
        </nav>
      </aside>
    </div>
  </div>
</template>
