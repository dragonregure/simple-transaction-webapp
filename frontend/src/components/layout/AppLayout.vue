<script setup lang="ts">
import { computed, ref, watch, type Component } from "vue";
import { RouterLink, RouterView, useRoute } from "vue-router";
import { BarChart3, ChevronDown, FolderTree, GitFork, Link2, Menu, PanelLeftClose, PanelLeftOpen, ReceiptText, Tags, X } from "@lucide/vue";

type NavigationItem = {
  label: string;
  href: string;
  icon: Component;
};

type ExternalNavigationItem = {
  label: string;
  href: string;
  icon?: Component;
  imageSrc?: string;
  imageAlt?: string;
};

const route = useRoute();
const sidebarOpen = ref(false);
const minimized = ref(false);
const masterExpanded = ref(route.path.startsWith("/master"));
const otherLinksExpanded = ref(true);

const navigation: NavigationItem[] = [
  { label: "Transactions", href: "/transactions", icon: ReceiptText },
  { label: "Reports", href: "/reports", icon: BarChart3 },
];

const masterNavigation: NavigationItem[] = [
  { label: "COA", href: "/master/chart-of-accounts", icon: FolderTree },
  { label: "COA Categories", href: "/master/chart-of-account-categories", icon: Tags },
];

const otherLinks: ExternalNavigationItem[] = [
  { label: "Repository", href: "https://github.com/dragonregure/simple-transaction-webapp", icon: GitFork },
  { label: "Get Lifely", href: "https://getlifely.vercel.app/", imageSrc: "/images/lifely-icon.png", imageAlt: "" },
  { label: "Lifely Repository", href: "https://github.com/dragonregure/lifely", icon: GitFork },
];

const isMasterActive = computed(() => route.path.startsWith("/master"));

watch(
  () => route.path,
  (path) => {
    if (path.startsWith("/master")) {
      masterExpanded.value = true;
    }
  },
);

function toggleMasterMenu() {
  masterExpanded.value = !masterExpanded.value;
}

function toggleOtherLinksMenu() {
  otherLinksExpanded.value = !otherLinksExpanded.value;
}
</script>

<template>
  <div class="min-h-screen bg-background">
    <aside
      class="fixed inset-y-0 left-0 z-40 hidden border-r border-line bg-white transition-[width] duration-200 lg:block"
      :class="minimized ? 'w-20' : 'w-64'"
    >
      <div class="flex h-full flex-col gap-5 p-4" :class="minimized && 'px-3'">
        <div class="flex items-center gap-3" :class="minimized ? 'flex-col justify-center' : 'justify-between'">
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
            <button
              class="focus-ring flex h-9 w-full items-center gap-2 rounded-md px-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400 hover:bg-slate-50 hover:text-slate-600"
              :class="{ 'justify-center px-0': minimized, 'text-cyan-700': isMasterActive }"
              type="button"
              :title="minimized ? 'Master Data' : undefined"
              :aria-expanded="masterExpanded"
              aria-controls="desktop-master-data-menu"
              @click="toggleMasterMenu"
            >
              <ChevronDown class="h-3.5 w-3.5 transition-transform" :class="{ '-rotate-90': !masterExpanded }" />
              <span v-if="!minimized">Master Data</span>
            </button>
            <div v-if="masterExpanded" id="desktop-master-data-menu" class="grid gap-1">
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
          </div>
        </nav>

        <div class="mt-auto grid gap-1">
          <button
            class="focus-ring flex h-9 w-full items-center gap-2 rounded-md px-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400 hover:bg-slate-50 hover:text-slate-600"
            :class="{ 'justify-center px-0': minimized }"
            type="button"
            :title="minimized ? 'Other Links' : undefined"
            :aria-expanded="otherLinksExpanded"
            aria-controls="desktop-other-links-menu"
            @click="toggleOtherLinksMenu"
          >
            <ChevronDown class="h-3.5 w-3.5 transition-transform" :class="{ '-rotate-90': !otherLinksExpanded }" />
            <Link2 class="h-3.5 w-3.5 shrink-0" />
            <span v-if="!minimized">Other Links</span>
          </button>
          <div v-if="otherLinksExpanded" id="desktop-other-links-menu" class="grid gap-1">
            <a
              v-for="item in otherLinks"
              :key="item.href"
              :href="item.href"
              class="focus-ring flex h-10 items-center gap-3 rounded-md px-3 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-950"
              :class="{ 'justify-center px-0': minimized }"
              :title="minimized ? item.label : undefined"
              target="_blank"
              rel="noopener noreferrer"
            >
              <component v-if="item.icon" :is="item.icon" class="h-4 w-4 shrink-0" />
              <span v-else class="grid h-4 w-4 shrink-0 place-items-center overflow-hidden rounded-sm">
                <img :src="item.imageSrc" :alt="item.imageAlt" class="h-4 w-4 object-contain" />
              </span>
              <span v-if="!minimized" class="truncate">{{ item.label }}</span>
            </a>
          </div>
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
            v-for="item in navigation"
            :key="item.href"
            :to="item.href"
            class="focus-ring flex h-10 items-center gap-3 rounded-md px-3 text-sm font-medium text-slate-600 hover:bg-slate-100"
            @click="sidebarOpen = false"
          >
            <component :is="item.icon" class="h-4 w-4" />
            {{ item.label }}
          </RouterLink>
          <button
            class="focus-ring mt-2 flex h-9 items-center gap-2 rounded-md px-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400 hover:bg-slate-50 hover:text-slate-600"
            :class="{ 'text-cyan-700': isMasterActive }"
            type="button"
            :aria-expanded="masterExpanded"
            aria-controls="mobile-master-data-menu"
            @click="toggleMasterMenu"
          >
            <ChevronDown class="h-3.5 w-3.5 transition-transform" :class="{ '-rotate-90': !masterExpanded }" />
            <span>Master Data</span>
          </button>
          <div v-if="masterExpanded" id="mobile-master-data-menu" class="grid gap-1">
            <RouterLink
              v-for="item in masterNavigation"
              :key="item.href"
              :to="item.href"
              class="focus-ring flex h-10 items-center gap-3 rounded-md px-3 text-sm font-medium text-slate-600 hover:bg-slate-100"
              @click="sidebarOpen = false"
            >
              <component :is="item.icon" class="h-4 w-4" />
              {{ item.label }}
            </RouterLink>
          </div>
          <button
            class="focus-ring mt-2 flex h-9 items-center gap-2 rounded-md px-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400 hover:bg-slate-50 hover:text-slate-600"
            type="button"
            :aria-expanded="otherLinksExpanded"
            aria-controls="mobile-other-links-menu"
            @click="toggleOtherLinksMenu"
          >
            <ChevronDown class="h-3.5 w-3.5 transition-transform" :class="{ '-rotate-90': !otherLinksExpanded }" />
            <Link2 class="h-3.5 w-3.5" />
            <span>Other Links</span>
          </button>
          <div v-if="otherLinksExpanded" id="mobile-other-links-menu" class="grid gap-1">
            <a
              v-for="item in otherLinks"
              :key="item.href"
              :href="item.href"
              class="focus-ring flex h-10 items-center gap-3 rounded-md px-3 text-sm font-medium text-slate-600 hover:bg-slate-100"
              target="_blank"
              rel="noopener noreferrer"
              @click="sidebarOpen = false"
            >
              <component v-if="item.icon" :is="item.icon" class="h-4 w-4" />
              <span v-else class="grid h-4 w-4 place-items-center overflow-hidden rounded-sm">
                <img :src="item.imageSrc" :alt="item.imageAlt" class="h-4 w-4 object-contain" />
              </span>
              {{ item.label }}
            </a>
          </div>
        </nav>
      </aside>
    </div>
  </div>
</template>
