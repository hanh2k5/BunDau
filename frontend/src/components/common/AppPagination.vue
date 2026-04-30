<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: { type: Number, required: true },
  totalPages: { type: Number, required: true },
  totalItems: { type: Number, default: 0 },
  itemsPerPage: { type: Number, default: 0 },
})

const emit = defineEmits(['update:currentPage'])

const pages = computed(() => {
  const current = props.currentPage
  const last = props.totalPages
  const delta = 2
  const left = current - delta
  const right = current + delta + 1
  const range = []
  const rangeWithDots = []
  let l

  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= left && i < right)) {
      range.push(i)
    }
  }

  for (const i of range) {
    if (l) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1)
      } else if (i - l !== 1) {
        rangeWithDots.push('...')
      }
    }
    rangeWithDots.push(i)
    l = i
  }

  return rangeWithDots
})

function goToPage(page) {
  if (page === '...' || page === props.currentPage || page < 1 || page > props.totalPages) return
  emit('update:currentPage', page)
}
</script>

<template>
  <div class="flex flex-col sm:flex-row items-center justify-center gap-4 w-full">
    <!-- Info -->
    <div v-if="totalItems > 0" class="text-[13px] font-medium text-slate-500">
      <span class="font-bold text-slate-900">{{ totalItems }}</span> kết quả
    </div>

    <!-- Navigation -->
    <div class="flex items-center gap-1 sm:gap-1.5">
      <button
        @click="goToPage(1)"
        :disabled="currentPage === 1"
        class="flex w-8 h-8 sm:w-9 sm:h-9 rounded-xl items-center justify-center transition-all duration-200 disabled:opacity-30 disabled:cursor-not-allowed"
        :class="currentPage === 1 ? 'text-slate-300' : 'text-slate-600 hover:bg-white hover:shadow-sm hover:text-primary-600'"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
        </svg>
      </button>

      <!-- Previous -->
      <button
        @click="goToPage(currentPage - 1)"
        :disabled="currentPage === 1"
        class="w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-200 disabled:opacity-30 disabled:cursor-not-allowed"
        :class="currentPage === 1 ? 'text-slate-300' : 'text-slate-600 hover:bg-white hover:shadow-sm hover:text-primary-600'"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
        </svg>
      </button>

      <!-- Page Numbers -->
      <div class="flex items-center gap-1 mx-0.5">
        <template v-for="(page, index) in pages" :key="index">
          <span v-if="page === '...'" class="px-1.5 text-slate-400 font-bold text-[11px]">...</span>
          <button
            v-else
            @click="goToPage(page)"
            class="min-w-[32px] sm:min-w-[36px] h-8 sm:h-9 rounded-lg sm:rounded-xl text-[12px] sm:text-[13px] font-bold transition-all duration-200"
            :class="currentPage === page 
              ? 'bg-primary-600 text-white shadow-md shadow-primary-500/20' 
              : 'text-slate-600 hover:bg-white hover:shadow-sm hover:text-primary-600 border border-transparent hover:border-slate-100'"
          >
            {{ page }}
          </button>
        </template>
      </div>

      <!-- Next -->
      <button
        @click="goToPage(currentPage + 1)"
        :disabled="currentPage === totalPages"
        class="w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-200 disabled:opacity-30 disabled:cursor-not-allowed"
        :class="currentPage === totalPages ? 'text-slate-300' : 'text-slate-600 hover:bg-white hover:shadow-sm hover:text-primary-600'"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <button
        @click="goToPage(totalPages)"
        :disabled="currentPage === totalPages"
        class="flex w-8 h-8 sm:w-9 sm:h-9 rounded-xl items-center justify-center transition-all duration-200 disabled:opacity-30 disabled:cursor-not-allowed"
        :class="currentPage === totalPages ? 'text-slate-300' : 'text-slate-600 hover:bg-white hover:shadow-sm hover:text-primary-600'"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
        </svg>
      </button>
    </div>
  </div>
</template>
