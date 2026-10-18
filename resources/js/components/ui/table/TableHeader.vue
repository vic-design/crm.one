<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <thead
    data-slot="table-header"
    :class="cn('text-xs text-zinc-400 uppercase bg-zinc-950/50 border-b border-zinc-800', props.class)"
  >
    <slot />
  </thead>
</template>
