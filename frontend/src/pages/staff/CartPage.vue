<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart.store'
import { useOrdersStore } from '@/stores/orders.store'
import { useNotificationStore } from '@/stores/notification.store'
import { formatCurrency } from '@/utils/format'
import CartItem from '@/components/orders/CartItem.vue'
import AppButton from '@/components/common/AppButton.vue'
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
  <div style="padding-bottom: 120px;">

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="font-semibold" style="font-size: 28px; color: #1d1d1f; letter-spacing: -0.03em; margin: 0;">Giỏ hàng</h1>
        <p style="font-size: 13px; color: #86868b; margin-top: 4px;">
          {{ cart.itemCount }} loại món · {{ cart.totalItems }} sản phẩm
        </p>
      </div>
      <router-link
        :to="{ name: 'menu' }"
        class="btn-apple-outline"
        style="font-size: 13px; padding: 7px 16px;"
      >
        ← Chọn thêm
      </router-link>
    </div>

    <!-- Empty State -->
    <AppEmpty
      v-if="cart.isEmpty"
      icon="🛒"
      title="Giỏ hàng đang trống"
      description="Hãy chọn món từ thực đơn để bắt đầu đặt hàng."
    >
      <button class="btn-apple" @click="router.push({ name: 'menu' })">
        Xem thực đơn
      </button>
    </AppEmpty>

    <!-- Cart Content -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Cart Items -->
      <div class="lg:col-span-2 flex flex-col gap-3">
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

      <!-- Order Summary — Desktop -->
      <div class="hidden lg:block">
        <div class="card" style="padding: 28px; position: sticky; top: 72px;">
          <h3 style="font-size: 12px; font-weight: 600; color: #86868b; letter-spacing: 0.04em; text-transform: uppercase; margin-bottom: 24px;">
            Tóm tắt đơn hàng
          </h3>

          <!-- Summary rows -->
          <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 14px; color: #86868b;">Số lượng</span>
              <span style="font-size: 14px; font-weight: 500; color: #1d1d1f;">{{ cart.totalItems }} món</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid rgba(0,0,0,0.06);">
              <span style="font-size: 15px; font-weight: 500; color: #1d1d1f;">Tổng cộng</span>
              <span style="font-size: 22px; font-weight: 600; color: #0071e3; letter-spacing: -0.03em;">
                {{ formatCurrency(cart.totalAmount) }}
              </span>
            </div>
          </div>

          <!-- Note -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 12px; color: #86868b; margin-bottom: 8px; font-weight: 500;">
              Ghi chú đặc biệt
            </label>
            <textarea
              v-model="note"
              rows="3"
              class="input-apple"
              style="border-radius: 12px; resize: none; font-size: 14px;"
              placeholder="Ví dụ: Ít mắm tôm, nhiều quất..."
            ></textarea>
          </div>

          <!-- Checkout -->
          <button
            class="btn-apple"
            style="width: 100%; padding: 14px; font-size: 15px;"
            :disabled="submitting"
            @click="handleCheckout"
          >
            {{ submitting ? 'Đang xử lý...' : 'Đặt hàng' }}
          </button>

          <button
            @click="cart.clear()"
            style="width: 100%; margin-top: 12px; background: none; border: none; font-size: 13px; color: #86868b; cursor: pointer; padding: 6px; transition: color 0.15s ease;"
            onmouseenter="this.style.color='#ff3b30'"
            onmouseleave="this.style.color='#86868b'"
          >
            Xoá giỏ hàng
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Floating Checkout Bar -->
    <div
      v-if="!cart.isEmpty"
      class="lg:hidden"
      style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 40; padding: 16px; background: rgba(255,255,255,0.85); backdrop-filter: saturate(180%) blur(20px); -webkit-backdrop-filter: saturate(180%) blur(20px); border-top: 1px solid rgba(0,0,0,0.08);"
    >
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
        <div>
          <p style="font-size: 12px; color: #86868b; margin: 0;">{{ cart.totalItems }} món</p>
          <p style="font-size: 20px; font-weight: 600; color: #1d1d1f; letter-spacing: -0.03em; margin: 0;">
            {{ formatCurrency(cart.totalAmount) }}
          </p>
        </div>
        <button
          class="btn-apple"
          style="padding: 12px 28px; font-size: 15px;"
          :disabled="submitting"
          @click="handleCheckout"
        >
          {{ submitting ? 'Đang xử lý...' : 'Đặt hàng' }}
        </button>
      </div>
    </div>

  </div>
</template>
