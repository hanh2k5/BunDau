<script setup>
import { formatCurrency, formatDate, formatTime, getStatusLabel, getStatusColor } from '@/utils/format'

defineProps({
  order: { type: Object, required: true },
  showActions: { type: Boolean, default: true },
  payLoading: { type: Boolean, default: false },
  cancelLoading: { type: Boolean, default: false },
})

defineEmits(['pay', 'cancel', 'view', 'add-items'])
</script>

<template>
  <div 
    class="order-card flex flex-col h-full bg-white transition-all duration-500 overflow-hidden relative"
    :class="[
      order.status === 'pending' ? 'glow-pending' : '',
      order.status === 'done' ? 'glow-done' : '',
      order.status === 'cancelled' ? 'glow-cancelled' : ''
    ]"
  >
    <div class="p-5 sm:p-7 flex-1 flex flex-col">
      <!-- Header -->
      <div class="flex items-start justify-between mb-5 sm:mb-6">
        <div class="flex flex-col">
          <div class="flex items-center gap-2 mb-1">
            <div class="relative flex items-center justify-center">
              <span class="absolute inline-flex h-full w-full rounded-full opacity-30 animate-ping" :class="order.status === 'pending' ? 'bg-primary-400' : 'bg-slate-300'"></span>
              <span class="relative inline-flex rounded-full h-1.5 w-1.5" :class="order.status === 'pending' ? 'bg-primary-500' : 'bg-slate-400'"></span>
            </div>
            <p class="text-[9px] text-slate-400 font-black uppercase tracking-[0.2em]">
              {{ order.table_number || 'Mang về' }}
            </p>
          </div>
          <h4 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tighter leading-none">Đơn #{{ order.daily_number || order.id }}</h4>
        </div>
        
        <div class="status-badge" :class="getStatusColor(order.status)">
          {{ getStatusLabel(order.status) }}
        </div>
      </div>

      <!-- Compact Info Grid -->
      <div class="grid grid-cols-2 gap-3 mb-6 sm:mb-8">
        <div class="flex items-center gap-2.5 p-2 sm:p-3 rounded-xl bg-slate-50/50">
          <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white flex items-center justify-center text-slate-400 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-[8px] font-black text-slate-400 uppercase">Nhân viên</span>
            <span class="text-[10px] sm:text-[11px] font-bold text-slate-700 truncate">{{ order.created_by?.name || 'Hệ thống' }}</span>
          </div>
        </div>

        <div class="flex items-center gap-2.5 p-2 sm:p-3 rounded-xl bg-slate-50/50">
          <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white flex items-center justify-center text-slate-400 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-[8px] font-black text-slate-400 uppercase">Thời gian</span>
            <div class="flex flex-col">
              <span class="text-[9px] font-bold text-slate-400 leading-none">{{ formatDate(order.created_at).split(' ')[0] }}</span>
              <span class="text-[12px] sm:text-[13px] font-black text-slate-900 leading-none mt-0.5">{{ formatTime(order.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Section -->
      <div class="mt-auto pt-5 sm:pt-6 border-t border-slate-100/60">
        <div class="flex items-end justify-between mb-5 sm:mb-6">
          <div class="flex flex-col">
            <div class="flex items-center gap-1.5 mb-1 opacity-50">
              <span class="text-[10px]">{{ order.payment_method === 'transfer' ? '🏦' : '💰' }}</span>
              <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">{{ order.payment_method === 'transfer' ? 'CK' : 'TM' }}</span>
            </div>
            <div class="flex items-baseline gap-0.5">
              <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tighter">{{ formatCurrency(order.total).replace(' ₫', '') }}</span>
              <span class="text-xs font-black text-slate-400 uppercase">đ</span>
            </div>
          </div>

          <button
            @click="$emit('view', order)"
            class="h-10 w-10 sm:h-11 sm:w-11 flex items-center justify-center rounded-xl bg-white text-slate-300 hover:text-primary-600 shadow-sm border border-slate-50 transition-all active:scale-90"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          </button>
        </div>

        <!-- Order Actions Grid -->
        <div v-if="order.status === 'pending'" class="grid grid-cols-6 gap-2">
          <button
            @click="$emit('add-items', order)"
            class="col-span-1 h-11 flex items-center justify-center rounded-xl bg-orange-50 text-orange-600 active:scale-95 transition-all"
            title="Thêm món"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          </button>

          <button
            @click="$emit('cancel', order)"
            :disabled="cancelLoading || payLoading"
            class="col-span-2 h-11 rounded-xl bg-slate-50 text-slate-400 font-black text-[10px] uppercase tracking-widest active:scale-95 transition-all"
          >
            Huỷ
          </button>

          <button
            @click="$emit('pay', order)"
            :disabled="payLoading || cancelLoading"
            class="col-span-3 h-11 rounded-xl bg-primary-600 text-white font-black text-[10px] uppercase tracking-widest active:scale-95 shadow-md shadow-primary-500/20 flex items-center justify-center gap-2 transition-all"
          >
            <span v-if="payLoading" class="animate-spin text-sm">◌</span>
            <span v-else>THU TIỀN</span>
          </button>
        </div>
        <div v-else class="h-11 flex items-center justify-center rounded-xl bg-slate-50/50 text-slate-400 text-[9px] font-black uppercase tracking-[0.2em] border border-dashed border-slate-100">
          ĐÃ THANH TOÁN
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.order-card {
  border-radius: 1.75rem;
  box-shadow: 0 10px 30px -15px rgba(0, 0, 0, 0.05);
}

.glow-pending { box-shadow: 0 15px 35px -15px rgba(0, 113, 227, 0.15); }
.glow-done { box-shadow: 0 15px 35px -15px rgba(16, 185, 129, 0.12); }
.glow-cancelled { box-shadow: 0 15px 35px -15px rgba(239, 68, 68, 0.1); }

.order-card:hover { transform: translateY(-3px); }

.status-badge {
  padding: 0.4rem 0.8rem;
  border-radius: 0.75rem;
  font-size: 8px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  background: white;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
}
</style>
