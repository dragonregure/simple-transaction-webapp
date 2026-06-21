<script setup lang="ts">
import { Search } from "@lucide/vue";

defineProps<{
  search: string;
}>();

const emit = defineEmits<{
  "update:search": [value: string];
}>();
</script>

<template>
  <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
    <label class="relative w-full md:max-w-sm">
      <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
      <input
        class="focus-ring h-10 w-full rounded-md border border-line bg-white pl-9 pr-3 text-sm"
        type="search"
        placeholder="Search"
        :value="search"
        @input="emit('update:search', ($event.target as HTMLInputElement).value)"
      />
    </label>
    <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
      <slot name="actions" />
    </div>
  </div>
</template>
