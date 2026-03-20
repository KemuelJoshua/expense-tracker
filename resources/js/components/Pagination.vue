<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-vue-next'

interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

interface PaginationData {
  from: number | null
  to: number | null
  total: number
  current_page?: number
  last_page?: number
  links: PaginationLink[]
}

const props = defineProps<{
  pagination: PaginationData
}>()

const currentPage = computed(() => props.pagination.current_page ?? 1)
const lastPage = computed(() => props.pagination.last_page ?? 1)

const prevLink = computed(() => props.pagination.links[0]?.url ?? null)
const nextLink = computed(() => props.pagination.links[props.pagination.links.length - 1]?.url ?? null)

const firstPageLink = computed(() => {
  const firstNumberedLink = props.pagination.links.find(
    (link) => link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;' && link.url
  )

  return firstNumberedLink?.url ?? null
})

const lastPageLink = computed(() => {
  const numberedLinks = props.pagination.links.filter(
    (link) => link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;' && link.url
  )

  return numberedLinks[numberedLinks.length - 1]?.url ?? null
})
</script>

<template>
  <div class="flex items-center justify-between px-4">
    <div class="hidden flex-1 text-sm text-muted-foreground lg:flex">
      Showing {{ pagination.from ?? 0 }} to {{ pagination.to ?? 0 }} of {{ pagination.total }} result(s)
    </div>

    <div class="flex w-full items-center gap-8 lg:w-fit">
      <div class="flex w-fit items-center justify-center text-sm font-medium">
        Page {{ currentPage }} of {{ lastPage }}
      </div>

      <div class="ml-auto flex items-center gap-2 lg:ml-0">
        <Button v-if="firstPageLink" as-child variant="outline" class="hidden h-8 w-8 p-0 lg:flex"
          :disabled="!prevLink">
          <Link :href="firstPageLink">
            <span class="sr-only">Go to first page</span>
            <ChevronsLeft class="h-4 w-4" />
          </Link>
        </Button>

        <Button v-if="prevLink" as-child variant="outline" size="icon" class="size-8">
          <Link :href="prevLink">
            <span class="sr-only">Go to previous page</span>
            <ChevronLeft class="h-4 w-4" />
          </Link>
        </Button>

        <Button v-else variant="outline" size="icon" class="size-8" disabled>
          <span class="sr-only">Go to previous page</span>
          <ChevronLeft class="h-4 w-4" />
        </Button>

        <Button v-if="nextLink" as-child variant="outline" size="icon" class="size-8">
          <Link :href="nextLink">
            <span class="sr-only">Go to next page</span>
            <ChevronRight class="h-4 w-4" />
          </Link>
        </Button>

        <Button v-else variant="outline" size="icon" class="size-8" disabled>
          <span class="sr-only">Go to next page</span>
          <ChevronRight class="h-4 w-4" />
        </Button>

        <Button v-if="lastPageLink" as-child variant="outline" size="icon" class="hidden size-8 lg:flex"
          :disabled="!nextLink">
          <Link :href="lastPageLink">
            <span class="sr-only">Go to last page</span>
            <ChevronsRight class="h-4 w-4" />
          </Link>
        </Button>
      </div>
    </div>
  </div>
</template>