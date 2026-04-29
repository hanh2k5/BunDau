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
    <!-- Image -->
    <div class="relative overflow-hidden bg-slate-100 flex items-center justify-center w-full shrink-0" style="aspect-ratio: 4/3;">
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

      <!-- Category label -->
      <div
        v-if="product.category"
        class="absolute top-4 left-4 text-[9px] font-black uppercase tracking-[0.1em] px-3 py-1.5 bg-white/90 backdrop-blur-md rounded-xl text-slate-800 shadow-sm border border-white/40"
      >
        {{ getCategoryLabel(product.category) }}
      </div>
    </div>

    <!-- Content -->
    <div class="p-5 flex flex-col flex-1">
      <h3 class="text-lg font-black text-slate-800 mb-1 leading-tight group-hover:text-primary-600 transition-colors">
        {{ product.name }}
      </h3>
      <p class="text-[13px] text-slate-500 font-medium mb-5 line-clamp-1">
        {{ product.description }}
      </p>

      <div class="mt-auto flex items-end justify-between">
        <div>
          <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-1">Giá thưởng thức</p>
          <p class="text-2xl font-black text-primary-600 tracking-tighter leading-none">
            {{ formatCurrency(product.price) }}
          </p>
        </div>

        <button
          v-if="showActions"
          @click.stop="$emit('add-to-cart', product)"
          class="btn-apple !py-1.5 !px-3 !text-xs shadow-sm hover:shadow-md"
        >
          Thêm
        </button>
      </div>
    </div>
  </div>
</template>
