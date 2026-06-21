<script setup lang="ts">
import { X } from "@lucide/vue";

defineProps<{
  open: boolean;
  title: string;
  description?: string;
  submitLabel?: string;
  submitting?: boolean;
}>();

const emit = defineEmits<{
  close: [];
  submit: [];
}>();
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50 grid place-items-center bg-slate-950/40 p-4" @click.self="emit('close')">
    <section class="max-h-[90vh] w-full max-w-2xl overflow-hidden rounded-lg bg-white shadow-xl">
      <div class="flex items-start justify-between gap-4 border-b border-line px-5 py-4">
        <div>
          <h2 class="text-lg font-bold text-ink">{{ title }}</h2>
          <p v-if="description" class="mt-1 text-sm text-muted">{{ description }}</p>
        </div>
        <button class="focus-ring rounded-md p-2 text-slate-500 hover:bg-slate-100" type="button" aria-label="Close dialog" @click="emit('close')">
          <X class="h-5 w-5" />
        </button>
      </div>
      <form class="grid max-h-[calc(90vh-73px)] gap-4 overflow-y-auto p-5" @submit.prevent="emit('submit')">
        <slot />
        <div class="flex justify-end gap-2 border-t border-line pt-4">
          <button class="focus-ring rounded-md border border-line px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" type="button" @click="emit('close')">
            Cancel
          </button>
          <button
            class="focus-ring rounded-md bg-cyan-700 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-800 disabled:cursor-not-allowed disabled:opacity-60"
            type="submit"
            :disabled="submitting"
          >
            {{ submitting ? "Saving..." : submitLabel ?? "Save" }}
          </button>
        </div>
      </form>
    </section>
  </div>
</template>
