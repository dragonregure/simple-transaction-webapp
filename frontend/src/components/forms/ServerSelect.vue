<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref, watch } from "vue";
import { ChevronDown, Loader2 } from "@lucide/vue";
import type { SelectOption, SelectOptionsResult } from "@/types";
import { isAbortError } from "@/services/httpClient";

const props = defineProps<{
  modelValue: number | string | null;
  label: string;
  placeholder: string;
  selectedText?: string;
  loadOptions: (search: string, page: number, signal?: AbortSignal) => Promise<SelectOptionsResult>;
  required?: boolean;
}>();

const emit = defineEmits<{
  "update:modelValue": [value: number | string | null];
  "update:selectedText": [value: string];
}>();

const open = ref(false);
const search = ref("");
const page = ref(1);
const options = ref<SelectOption[]>([]);
const hasMore = ref(false);
const isLoading = ref(false);
const error = ref("");
const selectedLabel = ref(props.selectedText ?? "");
const trigger = ref<HTMLButtonElement | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);
const dropdownStyle = ref<Record<string, string>>({});
let timeoutId: number | undefined;
let controller: AbortController | null = null;

const displayValue = computed(() => selectedLabel.value || props.placeholder);

function updateDropdownPosition() {
  const rect = trigger.value?.getBoundingClientRect();
  if (!rect) return;

  const viewportPadding = 16;
  const gap = 4;
  const preferredHeight = 320;
  const spaceBelow = window.innerHeight - rect.bottom - viewportPadding;
  const spaceAbove = rect.top - viewportPadding;
  const showAbove = spaceBelow < 240 && spaceAbove > spaceBelow;
  const maxHeight = Math.max(180, Math.min(preferredHeight, showAbove ? spaceAbove : spaceBelow));

  dropdownStyle.value = {
    left: `${rect.left}px`,
    top: `${showAbove ? rect.top - maxHeight - gap : rect.bottom + gap}px`,
    width: `${rect.width}px`,
    maxHeight: `${maxHeight}px`,
  };
}

async function load(reset = false) {
  controller?.abort();
  controller = new AbortController();

  if (reset) {
    page.value = 1;
    options.value = [];
  }

  isLoading.value = true;
  error.value = "";

  try {
    const result = await props.loadOptions(search.value, page.value, controller.signal);
    options.value = reset ? result.results : [...options.value, ...result.results];
    hasMore.value = result.pagination.more;
  } catch (caught) {
    if (!isAbortError(caught)) {
      error.value = caught instanceof Error ? caught.message : "Unable to load options.";
    }
  } finally {
    isLoading.value = false;
  }
}

function selectOption(option: SelectOption) {
  selectedLabel.value = option.text;
  emit("update:modelValue", option.id);
  emit("update:selectedText", option.text);
  open.value = false;
}

async function toggleOpen() {
  open.value = !open.value;

  if (open.value) {
    await nextTick();
    updateDropdownPosition();
    searchInput.value?.focus();
  }
}

function loadMore() {
  if (!hasMore.value || isLoading.value) return;
  page.value += 1;
  void load(false);
}

watch(open, (isOpen) => {
  if (isOpen && options.value.length === 0) {
    void load(true);
  }

  if (isOpen) {
    window.addEventListener("resize", updateDropdownPosition);
    window.addEventListener("scroll", updateDropdownPosition, true);
    return;
  }

  window.removeEventListener("resize", updateDropdownPosition);
  window.removeEventListener("scroll", updateDropdownPosition, true);
});

watch(search, () => {
  window.clearTimeout(timeoutId);
  timeoutId = window.setTimeout(() => void load(true), 350);
});

watch(
  () => props.selectedText,
  (value) => {
    if (value !== undefined) {
      selectedLabel.value = value;
    }
  },
);

onBeforeUnmount(() => {
  window.clearTimeout(timeoutId);
  controller?.abort();
  window.removeEventListener("resize", updateDropdownPosition);
  window.removeEventListener("scroll", updateDropdownPosition, true);
});
</script>

<template>
  <label class="grid gap-1.5">
    <span class="text-sm font-medium text-slate-700">{{ label }}</span>
    <div class="relative">
      <button
        ref="trigger"
        class="focus-ring flex h-10 w-full items-center justify-between gap-2 rounded-md border border-line bg-white px-3 text-left text-sm"
        type="button"
        :aria-expanded="open"
        @click="toggleOpen"
      >
        <span class="truncate" :class="selectedLabel ? 'text-ink' : 'text-slate-400'">{{ displayValue }}</span>
        <ChevronDown class="h-4 w-4 shrink-0 text-slate-400" />
      </button>
      <input v-if="required" :value="modelValue ?? ''" class="sr-only" tabindex="-1" required />
      <Teleport to="body">
      <div v-if="open" class="fixed z-[60] overflow-hidden rounded-md border border-line bg-white p-2 shadow-lg" :style="dropdownStyle">
        <input
          ref="searchInput"
          v-model="search"
          class="focus-ring h-9 w-full rounded-md border border-line px-3 text-sm"
          type="search"
          :placeholder="placeholder"
        />
        <div class="mt-2 overflow-y-auto" :style="{ maxHeight: `calc(${dropdownStyle.maxHeight ?? '320px'} - 96px)` }">
          <button
            v-for="option in options"
            :key="option.id"
            class="focus-ring flex w-full rounded-md px-3 py-2 text-left text-sm hover:bg-slate-100"
            type="button"
            @click="selectOption(option)"
          >
            {{ option.text }}
          </button>
          <p v-if="!isLoading && options.length === 0" class="px-3 py-3 text-sm text-muted">No options found.</p>
          <p v-if="error" class="px-3 py-2 text-sm text-red-700">{{ error }}</p>
        </div>
        <button
          v-if="hasMore"
          class="focus-ring mt-2 w-full rounded-md border border-line px-3 py-2 text-sm font-medium hover:bg-slate-50"
          type="button"
          @click="loadMore"
        >
          Load more
        </button>
        <div v-if="isLoading" class="flex items-center gap-2 px-3 py-2 text-sm text-muted">
          <Loader2 class="h-4 w-4 animate-spin" />
          Loading
        </div>
      </div>
      </Teleport>
    </div>
  </label>
</template>
