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

const note = ref('')
const submitting = ref(false)

async function handleCheckout() {
  if (cart.isEmpty) {
    notification.warning('Giỏ hàng trống!')
    return
  }
  submitting.value = true
  try {
    const payload = cart.toApiPayload(note.value || null)
    await ordersStore.createOrder(payload)
    notification.success('Tạo đơn hàng thành công!')
    cart.clear()
    note.value = ''
    router.push('/orders')
  } catch (err) {
    const message = err.response?.data?.message || 'Lỗi tạo đơn hàng'
    notification.error(message)
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="pb-32 sm:pb-40">
    <!-- Page Header -->
    <div class="mb-10 px-2">
      <div class="flex items-center justify-between mb-2">
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tighter uppercase">Giỏ hàng</h1>
      </div>
      <p class="text-[11px] text-slate-400 font-black uppercase tracking-[0.2em] mb-6">
        {{ cart.itemCount }} LOẠI MÓN • {{ cart.totalItems }} PHẦN ĂN
      </p>
      
      <router-link
        :to="{ name: 'menu' }"
        class="flex items-center justify-center w-full py-3.5 rounded-[1.25rem] bg-slate-50/50 border-2 border-slate-100/50 text-[12px] font-black text-slate-500 hover:bg-white hover:border-primary-200 hover:text-primary-600 transition-all active:scale-[0.98] uppercase tracking-[0.15em] shadow-sm"
      >
        <span class="mr-2 opacity-50">←</span> CHỌN THÊM MÓN
      </router-link>
    </div>

    <!-- Empty State -->
    <AppEmpty
      v-if="cart.isEmpty"
      icon="🛒"
      title="Giỏ hàng đang trống"
      description="Hãy chọn món từ thực đơn để bắt đầu đặt hàng."
    >
      <button class="btn-apple px-8" @click="router.push({ name: 'menu' })">
        Xem thực đơn ngay
      </button>
    </AppEmpty>

    <!-- Cart Content -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Cart Items -->
      <div class="lg:col-span-2 flex flex-col gap-4">
        <CartItem
          v-for="item in cart.items"
          :key="item.product_id"
          :item="item"
          @increment="cart.incrementQuantity"
          @decrement="cart.decrementQuantity"
          @update-quantity="cart.updateQuantity"
          @remove="cart.removeItem"
        />

        <!-- Note -->
        <div class="mt-4 px-1">
          <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">
            Ghi chú để dễ lọc
          </label>
          <textarea
            v-model="note"
            rows="3"
            class="w-full bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] p-5 text-sm font-bold text-slate-700 focus:outline-none focus:border-primary-500 focus:bg-white transition-all resize-none placeholder-slate-300 shadow-inner"
            placeholder="chuyển khoản, tiền mặc..."
          ></textarea>
        </div>
      </div>

      <!-- Order Summary — Desktop -->
      <div class="hidden lg:block">
        <div class="card p-8 sticky top-24 shadow-xl shadow-slate-200/50 border-slate-200/60">
          <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-8">
            Tóm tắt đơn hàng
          </h3>

          <div class="space-y-4 mb-10">
            <div class="flex justify-between items-center text-sm">
              <span class="text-slate-500 font-bold">Số lượng</span>
              <span class="text-slate-900 font-black">{{ cart.totalItems }} món</span>
            </div>
            <div class="pt-4 border-t border-slate-100 flex justify-between items-center">
              <span class="text-base font-black text-slate-900">Tổng cộng</span>
              <span class="text-3xl font-black text-primary-600 tracking-tighter">
                {{ formatCurrency(cart.totalAmount) }}
              </span>
            </div>
          </div>

          <button
            class="btn-apple w-full py-4 text-base shadow-xl shadow-primary-500/20 active:scale-[0.98] transition-all"
            :disabled="submitting"
            @click="handleCheckout"
          >
            {{ submitting ? 'Đang xử lý...' : 'Đặt hàng ngay' }}
          </button>

          <button
            @click="cart.clear()"
            class="w-full mt-4 text-[11px] font-black uppercase tracking-widest text-slate-400 hover:text-red-500 transition-colors py-2"
          >
            Xoá toàn bộ giỏ hàng
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Floating Checkout Bar -->
    <div
      v-if="!cart.isEmpty"
      class="lg:hidden fixed bottom-0 left-0 right-0 z-40 p-4 bg-white/80 backdrop-blur-xl border-t border-slate-200/60 shadow-[0_-8px_30px_rgba(0,0,0,0.04)]"
    >
      <div class="max-w-xl mx-auto flex items-center justify-between gap-4">
        <div class="min-w-0">
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ cart.totalItems }} phần ăn</p>
          <p class="text-2xl font-black text-slate-900 tracking-tighter truncate">
            {{ formatCurrency(cart.totalAmount) }}
          </p>
        </div>
        <button
          class="btn-apple px-10 py-3.5 text-sm shadow-xl shadow-primary-500/20 active:scale-95 transition-all whitespace-nowrap"
          :disabled="submitting"
          @click="handleCheckout"
        >
          {{ submitting ? '...' : 'Đặt hàng' }}
        </button>
      </div>
    </div>
  </div>
</template>
