<script setup lang="ts">
defineProps<{
  page: number;
  pageSize: number;
  pageCount: number;
  totalRows: number;
  pageSizeOptions: number[];
}>();

const emit = defineEmits<{
  "update:page": [value: number];
  "update:pageSize": [value: number];
}>();
</script>

<template>
  <div class="flex flex-col gap-3 text-sm text-muted md:flex-row md:items-center md:justify-between">
    <p>
      Page <span class="font-semibold text-ink">{{ page }}</span> of
      <span class="font-semibold text-ink">{{ pageCount }}</span>
      <span class="text-slate-400">({{ totalRows }} records)</span>
    </p>
    <div class="flex flex-wrap items-center gap-2">
      <label class="flex items-center gap-2">
        Rows
        <select
          class="focus-ring h-9 rounded-md border border-line bg-white px-2 text-sm text-ink"
          :value="pageSize"
          @change="emit('update:pageSize', Number(($event.target as HTMLSelectElement).value))"
        >
          <option v-for="option in pageSizeOptions" :key="option" :value="option">{{ option }}</option>
        </select>
      </label>
      <button
        class="focus-ring rounded-md border border-line px-3 py-2 font-medium text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
        type="button"
        :disabled="page <= 1"
        @click="emit('update:page', page - 1)"
      >
        Previous
      </button>
      <button
        class="focus-ring rounded-md border border-line px-3 py-2 font-medium text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
        type="button"
        :disabled="page >= pageCount"
        @click="emit('update:page', page + 1)"
      >
        Next
      </button>
    </div>
  </div>
</template>
