<script setup lang="ts">
defineProps<{
  eyebrow: string;
  title: string;
  description?: string;
}>();
</script>

<template>
  <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
    <div class="min-w-0">
      <p class="text-xs font-semibold uppercase tracking-wide text-cyan-700">{{ eyebrow }}</p>
      <h1 class="mt-1 text-2xl font-bold text-ink">{{ title }}</h1>
      <p v-if="description" class="mt-1 max-w-3xl text-sm text-muted">{{ description }}</p>
    </div>
    <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
      <slot name="actions" />
    </div>
  </div>
</template>
