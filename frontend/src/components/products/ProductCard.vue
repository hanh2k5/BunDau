<script setup>
import { formatCurrency } from '@/utils/format'
import { getCategoryLabel } from '@/utils/categories'

defineProps({
  product: {
    type: Object,
    required: true,
  },
  showActions: {
    type: Boolean,
    default: true,
  },
})

defineEmits(['add-to-cart', 'view-detail'])
</script>

<template>
  <div 
    class="card flex flex-col h-full group overflow-hidden cursor-pointer hover:shadow-lg hover:-translate-y-1 transition-all duration-300"
    @click="$emit('view-detail', product)"
  >
    <!-- Image Section: Inset Square for a premium "framed" look -->
    <div class="relative p-2.5">
      <div class="relative aspect-square overflow-hidden rounded-2xl bg-slate-50 flex items-center justify-center group/img">
        <img
          v-if="product.image_url"
          :src="product.image_url"
          class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
        />
        <div v-else class="w-full h-full flex items-center justify-center text-slate-200">
          <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>

        <!-- Category label: Positioned relative to the image container -->
        <div
          v-if="product.category"
          class="absolute top-3 left-3 z-20 text-[8px] font-black uppercase tracking-[0.1em] px-2.5 py-1.2 bg-white/90 backdrop-blur-md rounded-lg text-slate-800 shadow-sm border border-white/40"
        >
          {{ getCategoryLabel(product.category) }}
        </div>
      </div>
    </div>

    <!-- Content Section -->
    <div class="px-2 sm:px-5 py-3 sm:py-5 flex flex-col flex-1">
      <h3 class="text-[13.5px] sm:text-lg font-black text-slate-800 mb-1 leading-tight group-hover:text-primary-600 transition-colors tracking-tighter line-clamp-2">
        {{ product.name }}
      </h3>
      <p class="text-[11px] text-slate-500 font-medium mb-4 line-clamp-1 opacity-60">
        {{ product.description }}
      </p>

      <!-- Footer: Price & Add Button -->
      <div class="mt-auto flex items-center justify-between gap-1">
        <div class="flex-1 min-w-0">
          <p class="text-[8px] font-black text-slate-300 uppercase tracking-widest mb-0.5 whitespace-nowrap">Giá thưởng thức</p>
          <p class="text-[15.5px] sm:text-2xl font-black text-primary-600 tracking-tighter leading-none whitespace-nowrap">
            {{ formatCurrency(product.price) }}
          </p>
        </div>

        <button
          v-if="showActions"
          @click.stop="$emit('add-to-cart', product)"
          class="bg-primary-600 hover:bg-primary-700 text-white transition-all shadow-md shadow-primary-500/10 active:scale-90 flex items-center justify-center shrink-0 overflow-hidden"
          :class="[
            'h-8 w-8 sm:h-11 sm:w-auto rounded-full sm:rounded-2xl',
            'sm:px-6 sm:py-2.5'
          ]"
        >
          <span class="hidden sm:inline font-black text-[11px] uppercase tracking-widest">Thêm</span>
          <svg class="w-4 h-4 sm:hidden translate-y-[0.5px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 5v14M5 12h14" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>
