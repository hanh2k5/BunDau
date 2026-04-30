<script setup>
import { formatCurrency } from '@/utils/format'

defineProps({
  item: { type: Object, required: true },
})

defineEmits(['increment', 'decrement', 'remove', 'update-quantity'])
</script>

<template>
  <div class="card p-4 group">
    <div class="flex gap-4">
      <!-- Image -->
      <div class="w-20 h-20 sm:w-24 sm:h-24 shrink-0 rounded-2xl bg-slate-50 overflow-hidden relative border border-slate-100/50 shadow-inner">
        <img
          v-if="item.image_url"
          :src="item.image_url"
          class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
        />
        <div v-else class="w-full h-full flex items-center justify-center text-slate-200">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 flex flex-col justify-between min-w-0 py-0.5">
        <!-- Top: Name & Price per unit -->
        <div class="mb-2">
          <h4 class="text-[14px] sm:text-base font-black text-slate-900 uppercase tracking-tight leading-tight mb-1">
            {{ item.name }}
          </h4>
          <p class="text-[11px] sm:text-[13px] font-bold text-primary-600">
            {{ formatCurrency(item.price) }} <span class="text-slate-400 font-medium">/ suất</span>
          </p>
        </div>

        <!-- Bottom: Actions & Total -->
        <div class="flex items-end justify-between gap-4">
          <!-- Quantity Controls -->
          <div class="flex items-center gap-2.5">
            <button
              @click="$emit('decrement', item.product_id)"
              class="w-8 h-8 rounded-full bg-slate-100 text-slate-900 flex items-center justify-center transition-all hover:bg-slate-200 active:scale-90"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4" />
              </svg>
            </button>
            <span class="w-6 text-center font-black text-sm text-slate-900">
              {{ item.quantity }}
            </span>
            <button
              @click="$emit('increment', item.product_id)"
              class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center transition-all hover:bg-primary-700 shadow-md shadow-primary-500/20 active:scale-90"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
              </svg>
            </button>
          </div>

          <!-- Total + Remove -->
          <div class="flex flex-col items-end gap-0.5 pr-1">
            <span class="text-[15px] sm:text-lg font-black text-slate-900 tracking-tight whitespace-nowrap">
              {{ formatCurrency(item.price * item.quantity) }}
            </span>
            <button
              @click="$emit('remove', item.product_id)"
              class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-red-500 transition-all active:scale-95 px-1"
            >
              Xoá
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
