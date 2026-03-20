<script setup lang="ts">
import { TableRow, TableCell } from '@/components/ui/table'

const props = withDefaults(
  defineProps<{
    rows?: number
  }>(),
  {
    rows: 7,
  }
)
</script>

<template>
  <TableRow v-for="n in props.rows" :key="`loading-${n}`">
    <TableCell class="px-6 py-4">
      <div class="h-4 w-40 animate-pulse rounded bg-muted"></div>
    </TableCell>

    <TableCell class="px-6 py-4">
      <div class="h-4 w-24 animate-pulse rounded bg-muted"></div>
    </TableCell>

    <TableCell class="px-6 py-4">
      <div class="h-4 w-28 animate-pulse rounded bg-muted"></div>
    </TableCell>

    <TableCell class="px-6 py-4">
      <div class="h-4 w-24 animate-pulse rounded bg-muted"></div>
    </TableCell>

    <TableCell class="px-6 py-4">
      <div class="h-4 w-20 animate-pulse rounded bg-muted"></div>
    </TableCell>

    <TableCell class="px-6 py-4">
      <div class="h-4 w-20 animate-pulse rounded bg-muted"></div>
    </TableCell>
  </TableRow>
</template>