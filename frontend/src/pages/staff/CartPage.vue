<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart.store'
import { useOrdersStore } from '@/stores/orders.store'
import { useNotificationStore } from '@/stores/notification.store'
import { formatCurrency } from '@/utils/format'
import CartItem from '@/components/orders/CartItem.vue'
import AppEmpty from '@/components/common/AppEmpty.vue'

const router = useRouter()
const cart = useCartStore()
const ordersStore = useOrdersStore()
const notification = useNotificationStore()

const submitting = ref(false)

async function handleCheckout() {
  if (cart.isEmpty) {
    notification.warning('Giỏ hàng trống!')
    return
  }
  
  submitting.value = true
  try {
    // Note is removed, using null
    const payload = cart.toApiPayload(null, 'cash', cart.tableNumber || null)
    
    if (cart.activeOrderId) {
      await ordersStore.addItemsToOrder(cart.activeOrderId, payload)
      notification.success('Đã thêm món xong!')
    } else {
      await ordersStore.createOrder(payload)
      notification.success('Đã đặt món!')
    }
    
    cart.clear()
    router.push('/orders')
  } catch (err) {
    notification.error(err.response?.data?.message || 'Lỗi xử lý')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="pb-32 sm:pb-40 px-3">
    <!-- Page Header -->
    <div class="mb-5">
      <h1 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-0.5">
        {{ cart.activeOrderId ? 'Xác nhận món thêm' : 'Giỏ hàng' }}
      </h1>
      <p class="text-[9px] text-slate-400 font-black uppercase tracking-widest">
        {{ cart.totalItems }} phần ăn • {{ formatCurrency(cart.totalAmount) }}
      </p>
    </div>

    <!-- Empty State -->
    <AppEmpty
      v-if="cart.isEmpty"
      icon="🛒"
      title="Giỏ trống"
      description="Quay lại thực đơn chọn món nhé!"
    >
      <button class="btn-apple px-8" @click="router.push({ name: 'menu' })">
        Xem thực đơn
      </button>
    </AppEmpty>

    <!-- Cart Content -->
    <div v-else class="space-y-4">
      <!-- Table Selection (Only for New Order) -->
      <div v-if="!cart.activeOrderId" class="bg-white p-5 rounded-[1.75rem] shadow-sm border border-slate-50">
        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">📍 Chọn bàn</label>
        <input
          v-model="cart.tableNumber"
          type="text"
          placeholder="chọn bàn..."
          class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm font-black text-slate-800 focus:ring-2 focus:ring-primary-500/20 transition-all mb-3"
        />
        <div class="flex gap-1.5 overflow-x-auto no-scrollbar pb-1">
          <button 
            v-for="t in ['Bàn quạt cam', 'Bàn bình đá', 'Bàn giữa', 'Bàn sát bên', 'Bàn sát bên (trái)']" 
            :key="t"
            @click="cart.tableNumber = t"
            class="flex-shrink-0 px-3 py-1.5 rounded-lg text-[10px] font-black transition-all active:scale-95 whitespace-nowrap"
            :class="cart.tableNumber === t ? 'bg-primary-600 text-white shadow-md shadow-primary-500/20' : 'bg-slate-50 text-slate-500'"
          >
            {{ t }}
          </button>
        </div>
      </div>

      <!-- Editing Info (Locked Table) -->
      <div v-else class="bg-orange-500 text-white p-4 rounded-[1.75rem] shadow-lg shadow-orange-500/20 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="text-xl">🍜</span>
          <div>
            <p class="text-[8px] font-black uppercase tracking-widest text-orange-100">Đang thêm món cho</p>
            <p class="text-[16px] font-black tracking-tight leading-none">{{ cart.tableNumber || 'Đơn #' + cart.activeOrderId }}</p>
          </div>
        </div>
        <button @click="cart.clear()" class="text-[9px] font-black uppercase tracking-widest bg-white/20 px-3 py-1.5 rounded-lg active:scale-95">Huỷ sửa</button>
      </div>

      <!-- Cart Items List -->
      <div class="space-y-3">
        <CartItem
          v-for="item in cart.items"
          :key="item.product_id"
          :item="item"
          @increment="cart.incrementQuantity"
          @decrement="cart.decrementQuantity"
          @update-quantity="cart.updateQuantity"
          @remove="cart.removeItem"
        />
      </div>

      <!-- Quick Add More -->
      <button
        @click="router.push({ name: 'menu' })"
        class="w-full py-4 rounded-2xl border-2 border-dashed border-slate-200 text-slate-400 text-[11px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all active:scale-[0.98]"
      >
        + Chọn thêm món khác
      </button>
    </div>

    <!-- Mobile Floating Checkout Bar -->
    <div
      v-if="!cart.isEmpty"
      class="fixed bottom-0 left-0 right-0 z-40 p-3 bg-white/90 backdrop-blur-xl border-t border-slate-100 shadow-[0_-10px_30px_rgba(0,0,0,0.05)]"
    >
      <div class="max-w-xl mx-auto flex items-center justify-between gap-4">
        <div class="min-w-0 pl-1">
          <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ cart.totalItems }} phần</p>
          <p class="text-xl font-black text-slate-900 tracking-tighter truncate leading-none">
            {{ formatCurrency(cart.totalAmount) }}
          </p>
        </div>
        <button
          class="bg-primary-600 text-white px-8 py-3.5 rounded-xl font-black text-[12px] uppercase tracking-widest shadow-lg shadow-primary-500/20 active:scale-95 transition-all disabled:opacity-50"
          :disabled="submitting"
          @click="handleCheckout"
        >
          {{ submitting ? '...' : (cart.activeOrderId ? 'Xác nhận' : 'Đặt món') }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
