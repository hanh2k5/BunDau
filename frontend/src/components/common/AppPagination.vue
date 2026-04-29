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
  <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
    <!-- Info -->
    <div v-if="totalItems > 0" style="font-size: 13px; color: #86868b;">
      <span style="font-weight: 600; color: #1d1d1f;">{{ totalItems }}</span> kết quả
    </div>

    <!-- Navigation -->
    <div style="display: flex; align-items: center; gap: 4px;">
      <!-- First Page -->
      <button
        @click="goToPage(1)"
        :disabled="currentPage === 1"
        style="width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #86868b; transition: all 0.15s ease;"
        onmouseenter="if(!this.disabled) { this.style.background='rgba(0,0,0,0.04)'; this.style.color='#1d1d1f'; }"
        onmouseleave="if(!this.disabled) { this.style.background='transparent'; this.style.color='#86868b'; }"
      >
        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
        </svg>
      </button>

      <!-- Previous -->
      <button
        @click="goToPage(currentPage - 1)"
        :disabled="currentPage === 1"
        style="width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #86868b; transition: all 0.15s ease;"
        onmouseenter="if(!this.disabled) { this.style.background='rgba(0,0,0,0.04)'; this.style.color='#1d1d1f'; }"
        onmouseleave="if(!this.disabled) { this.style.background='transparent'; this.style.color='#86868b'; }"
      >
        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>

      <!-- Page Numbers -->
      <div style="display: flex; align-items: center; gap: 4px; margin: 0 4px;">
        <template v-for="(page, index) in pages" :key="index">
          <span v-if="page === '...'" style="padding: 0 4px; color: #86868b; font-weight: 600;">...</span>
          <button
            v-else
            @click="goToPage(page)"
            style="min-width: 32px; height: 32px; border-radius: 8px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.15s ease; padding: 0 8px;"
            :style="currentPage === page ? 'background: #0071e3; color: white;' : 'background: transparent; color: #1d1d1f;'"
            onmouseenter="if(this.style.background === 'transparent') { this.style.background='rgba(0,0,0,0.04)'; }"
            onmouseleave="if(this.style.background === 'rgba(0, 0, 0, 0.04)') { this.style.background='transparent'; }"
          >
            {{ page }}
          </button>
        </template>
      </div>

      <!-- Next -->
      <button
        @click="goToPage(currentPage + 1)"
        :disabled="currentPage === totalPages"
        style="width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #86868b; transition: all 0.15s ease;"
        onmouseenter="if(!this.disabled) { this.style.background='rgba(0,0,0,0.04)'; this.style.color='#1d1d1f'; }"
        onmouseleave="if(!this.disabled) { this.style.background='transparent'; this.style.color='#86868b'; }"
      >
        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <!-- Last Page -->
      <button
        @click="goToPage(totalPages)"
        :disabled="currentPage === totalPages"
        style="width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #86868b; transition: all 0.15s ease;"
        onmouseenter="if(!this.disabled) { this.style.background='rgba(0,0,0,0.04)'; this.style.color='#1d1d1f'; }"
        onmouseleave="if(!this.disabled) { this.style.background='transparent'; this.style.color='#86868b'; }"
      >
        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
        </svg>
      </button>
    </div>
  </div>
</template>
