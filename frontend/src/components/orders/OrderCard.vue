<script setup>
import { formatCurrency, formatDate, getStatusLabel, getStatusColor } from '@/utils/format'

defineProps({
  order: { type: Object, required: true },
  showActions: { type: Boolean, default: true },
  payLoading: { type: Boolean, default: false },
  cancelLoading: { type: Boolean, default: false },
})

defineEmits(['pay', 'cancel', 'view'])
</script>

<template>
  <div class="card flex flex-col h-full" style="padding: 24px;">
    <!-- Header -->
    <div class="flex items-center justify-between mb-5">
      <div class="flex flex-col">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Đơn hàng</span>
        <h4 class="text-xl font-bold text-slate-900 tracking-tight">#{{ order.id }}</h4>
      </div>
      <div :class="getStatusColor(order.status)" class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-white bg-white shadow-sm">
        {{ getStatusLabel(order.status) }}
      </div>
    </div>

    <!-- Info -->
    <div class="space-y-4 flex-1 mb-6">
      <div class="flex items-center gap-3 text-slate-600">
        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <span class="text-xs font-semibold">{{ order.created_by?.name || 'Hệ thống' }}</span>
      </div>
      <div class="flex items-center gap-3 text-slate-600">
        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <span class="text-xs font-semibold">{{ formatDate(order.created_at) }}</span>
      </div>
    </div>

    <!-- Footer -->
    <div class="pt-5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex flex-col">
        <span class="text-[9px] font-bold text-slate-400 uppercase mb-0.5">Thanh toán</span>
        <span class="font-black" style="font-size: 20px; color: #0071e3; letter-spacing: -0.02em;">{{ formatCurrency(order.total) }}</span>
      </div>

      <div class="flex items-center gap-2">
        <!-- Actions for Pending -->
        <template v-if="order.status === 'pending'">
          <button
            @click="$emit('cancel', order)"
            :disabled="cancelLoading || payLoading"
            class="h-9 px-4 rounded-xl bg-slate-50 text-slate-500 hover:bg-red-50 hover:text-red-500 active:bg-red-50 active:text-red-500 font-black text-[11px] uppercase tracking-widest transition-all active:scale-95 border border-slate-100/50"
          >
            <span v-if="cancelLoading">...</span>
            <span v-else>Huỷ đơn</span>
          </button>
          
          <button
            @click="$emit('pay', order)"
            :disabled="payLoading || cancelLoading"
            class="h-9 px-5 rounded-xl bg-primary-600 text-white hover:bg-primary-700 font-black text-[11px] uppercase tracking-widest transition-all active:scale-95 shadow-lg shadow-primary-500/20"
          >
            <span v-if="payLoading">...</span>
            <span v-else>Thu tiền</span>
          </button>
        </template>

        <!-- View Detail (Eye) -->
        <button
          @click="$emit('view', order)"
          class="h-9 w-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-900 hover:border-slate-300 transition-all shadow-sm active:scale-95"
          title="Xem chi tiết"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        </button>
      </div>
    </div>
  </div>
</template>
