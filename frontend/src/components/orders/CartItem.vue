<script setup>
import { formatCurrency } from '@/utils/format'

defineProps({
  item: { type: Object, required: true },
})

defineEmits(['increment', 'decrement', 'remove', 'update-quantity'])
</script>

<template>
  <div class="card flex items-center gap-4 sm:gap-5 group" style="padding: 16px;">
    <!-- Product Image -->
    <div
      class="flex-shrink-0 overflow-hidden"
      style="width: 72px; height: 72px; border-radius: 14px; background: #f5f5f7;"
    >
      <img
        v-if="item.image_url"
        :src="item.image_url"
        style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;"
        onmouseenter="this.style.transform='scale(1.06)'"
        onmouseleave="this.style.transform='scale(1)'"
      />
      <div v-else class="w-full h-full flex items-center justify-center">
        <svg style="width: 28px; height: 28px; color: #c7c7cc;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
    </div>

    <!-- Info -->
    <div class="flex-1 min-w-0">
      <h4 class="font-black mb-0.5 truncate uppercase tracking-tight" style="font-size: 15px; color: #1d1d1f;">
        {{ item.name }}
      </h4>
      <p style="font-size: 13px; color: #0071e3; font-weight: 500;">
        {{ formatCurrency(item.price) }} / suất
      </p>
    </div>

    <!-- Quantity Controls -->
    <div class="flex items-center gap-2 flex-shrink-0">
      <button
        @click="$emit('decrement', item.product_id)"
        style="width: 30px; height: 30px; border-radius: 50%; background: rgba(0,0,0,0.05); border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.15s ease; color: #1d1d1f;"
        onmouseenter="this.style.background='rgba(0,0,0,0.09)'"
        onmouseleave="this.style.background='rgba(0,0,0,0.05)'"
      >
        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
        </svg>
      </button>

      <span class="text-center font-semibold" style="width: 28px; font-size: 15px; color: #1d1d1f;">
        {{ item.quantity }}
      </span>

      <button
        @click="$emit('increment', item.product_id)"
        style="width: 30px; height: 30px; border-radius: 50%; background: #0071e3; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.15s ease; color: white;"
        onmouseenter="this.style.background='#0077ed'"
        onmouseleave="this.style.background='#0071e3'"
      >
        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
      </button>
    </div>

    <!-- Subtotal + Remove -->
    <div class="flex-shrink-0 flex flex-col items-end gap-1.5" style="min-width: 80px;">
      <span class="font-semibold" style="font-size: 15px; color: #1d1d1f; letter-spacing: -0.02em;">
        {{ formatCurrency(item.price * item.quantity) }}
      </span>
      <button
        @click="$emit('remove', item.product_id)"
        style="font-size: 11px; color: #86868b; background: none; border: none; cursor: pointer; transition: color 0.15s ease; padding: 0;"
        onmouseenter="this.style.color='#ff3b30'"
        onmouseleave="this.style.color='#86868b'"
      >
        Xoá
      </button>
    </div>
  </div>
</template>
