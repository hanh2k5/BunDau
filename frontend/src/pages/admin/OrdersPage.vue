<script setup>
import { useRoute } from 'vue-router'
import { onMounted, ref, computed, watch } from 'vue'
import { useOrdersStore } from '@/stores/orders.store'
import { useNotificationStore } from '@/stores/notification.store'
import { removeAccents, formatCurrency, formatDate, getStatusLabel, getStatusColor } from '@/utils/format'
import OrderCard from '@/components/orders/OrderCard.vue'
import AppLoading from '@/components/common/AppLoading.vue'
import AppEmpty from '@/components/common/AppEmpty.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import AppModal from '@/components/common/AppModal.vue'
import AppDateInput from '@/components/common/AppDateInput.vue'

const route = useRoute()
const ordersStore = useOrdersStore()
const notification = useNotificationStore()

const statusFilter = ref('')
const dateFilter = ref(route.query.date || '')
const payingId = ref(null)
const cancellingId = ref(null)
const selectedOrder = ref(null)

// Search & Pagination state
const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(6)

onMounted(() => {
  loadOrders()
})

// Watch for route query changes (e.g. when clicking from Revenue page)
watch(() => route.query.date, (newDate) => {
  dateFilter.value = newDate || ''
  loadOrders()
})

function loadOrders() {
  const params = {}
  if (statusFilter.value) params.status = statusFilter.value
  if (dateFilter.value) params.date = dateFilter.value
  
  ordersStore.fetchOrders(params)
  // Reset pagination on filter change
  currentPage.value = 1
}

function onFilterChange() {
  loadOrders()
}

// Computed: Filter by search query (Order ID or Staff Name)
const filteredOrders = computed(() => {
  if (!searchQuery.value) return ordersStore.orders
  const lowerQuery = removeAccents(searchQuery.value.toLowerCase())
  return ordersStore.orders.filter(o => {
    const id = o.id.toString()
    const staffName = removeAccents(o.created_by?.name?.toLowerCase() || '')
    const note = removeAccents(o.note?.toLowerCase() || '')
    return id.includes(lowerQuery) || staffName.includes(lowerQuery) || note.includes(lowerQuery)
  })
})

// Computed: Pagination logic
const totalPages = computed(() => Math.ceil(filteredOrders.value.length / itemsPerPage.value) || 1)

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredOrders.value.slice(start, start + itemsPerPage.value)
})

function handleSearch() {
  currentPage.value = 1
}

async function handlePay(order) {
  payingId.value = order.id
  try {
    await ordersStore.payOrder(order.id)
    notification.success('Thanh toán thành công! 🎉')
  } catch (err) {
    notification.error(err.response?.data?.message || 'Lỗi thanh toán')
  } finally {
    payingId.value = null
  }
}

async function handleCancel(order) {
  if (!confirm('Bạn chắc chắn muốn hủy đơn hàng này?')) return

  cancellingId.value = order.id
  try {
    await ordersStore.cancelOrder(order.id)
    notification.danger('Đã hủy đơn hàng thành công')
  } catch (err) {
    notification.error(err.response?.data?.message || 'Lỗi hủy đơn')
  } finally {
    cancellingId.value = null
  }
}
</script>

<template>
  <div class="space-y-6 pb-8">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">🧾 Đơn hàng</h2>
        <p class="text-sm text-slate-500 mt-1 font-medium">
          {{ ordersStore.orders.length }} đơn trong danh sách
        </p>
      </div>

      <!-- Filters Container -->
      <div class="flex flex-col sm:flex-row items-center gap-3">
        <!-- Date filter -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <AppDateInput 
            v-model="dateFilter"
            @update:model-value="onFilterChange"
            class="flex-1 sm:flex-initial"
          />
          <button 
            v-if="dateFilter"
            @click="dateFilter = ''; onFilterChange()"
            class="p-2.5 rounded-xl bg-white border border-slate-100 text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all shadow-sm"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Status Filter Tabs -->
        <div class="flex bg-slate-100/80 p-1 rounded-2xl w-full sm:w-auto overflow-x-auto no-scrollbar border border-slate-200/50 shadow-inner">
          <button
            v-for="status in [
              { value: '', label: 'Tất cả' },
              { value: 'pending', label: 'Chờ xử lý' },
              { value: 'done', label: 'Đã xong' },
              { value: 'cancelled', label: 'Đã hủy' },
            ]"
            :key="status.value"
            @click="statusFilter = status.value; onFilterChange()"
            class="px-4 py-1.5 text-[13px] font-bold rounded-xl transition-all duration-300 whitespace-nowrap"
            :class="statusFilter === status.value 
              ? 'bg-white text-slate-900 shadow-sm ring-1 ring-slate-200/50' 
              : 'text-slate-500 hover:text-slate-800'"
          >
            {{ status.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="card p-4">
      <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <input
          v-model="searchQuery"
          @input="handleSearch"
          type="text"
          class="input-apple !pl-11 !bg-slate-50/50 !border-none"
          placeholder="Tìm đơn theo mã (ID), tên nhân viên, ghi chú..."
        />
      </div>
    </div>

    <!-- Content -->
    <div v-if="ordersStore.loading" class="flex justify-center py-20">
      <AppLoading size="lg" />
    </div>

    <div v-else-if="filteredOrders.length === 0">
      <AppEmpty 
        message="Không tìm thấy đơn hàng nào" 
        description="Hãy thử đổi bộ lọc hoặc từ khóa tìm kiếm"
      />
    </div>

    <div v-else class="space-y-6">
      <!-- Orders Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <OrderCard
          v-for="order in paginatedOrders"
          :key="order.id"
          :order="order"
          :pay-loading="payingId === order.id"
          :cancel-loading="cancellingId === order.id"
          @pay="handlePay"
          @cancel="handleCancel"
          @view="selectedOrder = $event"
        />
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex justify-center pt-4">
        <AppPagination
          v-model:currentPage="currentPage"
          :total-pages="totalPages"
          :total-items="filteredOrders.length"
          :items-per-page="itemsPerPage"
        />
      </div>
    </div>

    <!-- Order Detail Modal -->
    <AppModal
      :show="!!selectedOrder"
      title="Chi tiết Đơn hàng"
      size="md"
      @close="selectedOrder = null"
    >
      <div v-if="selectedOrder" class="space-y-6 pt-2">
        <!-- Info Grid -->
        <div class="grid grid-cols-2 gap-y-5 gap-x-6 bg-slate-50/50 p-6 rounded-3xl border border-slate-100 shadow-inner">
          <div>
            <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.1em] mb-1">Mã đơn hàng</p>
            <p class="font-black text-slate-900 text-xl tracking-tight">#{{ selectedOrder.id }}</p>
          </div>
          <div class="text-right">
            <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.1em] mb-1">Trạng thái</p>
            <span 
              class="px-3.5 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-sm"
              :class="getStatusColor(selectedOrder.status)"
            >
              {{ getStatusLabel(selectedOrder.status) }}
            </span>
          </div>
          <div>
            <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.1em] mb-1.5">Nhân viên trực</p>
            <p class="font-bold text-slate-800 text-sm flex items-center gap-2">
               <span class="w-7 h-7 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-[11px] font-black shadow-sm">
                 {{ selectedOrder.created_by?.name?.charAt(0).toUpperCase() || 'S' }}
               </span>
               {{ selectedOrder.created_by?.name || 'Hệ thống' }}
            </p>
          </div>
          <div class="text-right">
            <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.1em] mb-1.5">Thời điểm đặt</p>
            <p class="font-bold text-slate-700 text-sm tracking-tight">{{ formatDate(selectedOrder.created_at) }}</p>
          </div>
        </div>

        <!-- Items List -->
        <div>
          <div class="flex items-center justify-between mb-4 px-1">
            <h4 class="font-black text-slate-900 text-[11px] uppercase tracking-widest">Danh sách món ăn</h4>
            <span class="text-[10px] font-black px-2.5 py-1 bg-slate-100 text-slate-500 rounded-lg uppercase tracking-wide">
              {{ selectedOrder.items?.length || 0 }} món
            </span>
          </div>
          <div class="space-y-2.5 max-h-[320px] overflow-y-auto no-scrollbar pr-1">
            <div
              v-for="item in selectedOrder.items"
              :key="item.id"
              class="flex items-center gap-4 p-3.5 border border-slate-100 rounded-2xl bg-white hover:bg-slate-50/50 transition-all duration-200 group"
            >
              <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 overflow-hidden shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                <img v-if="item.product_image_url" :src="item.product_image_url" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-bold text-slate-900 text-[15px] leading-tight truncate">{{ item.product_name || 'Sản phẩm' }}</p>
                <div class="flex items-center gap-2.5 mt-1">
                  <span class="text-[11px] font-black text-primary-600 bg-primary-50 px-2 py-0.5 rounded-md">{{ formatCurrency(item.price) }}</span>
                  <span class="text-[11px] text-slate-400 font-bold">x{{ item.quantity }}</span>
                </div>
              </div>
              <p class="font-black text-slate-900 text-[15px] tracking-tight">
                {{ formatCurrency(item.subtotal || item.price * item.quantity) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div class="space-y-4 pt-2">
          <div v-if="selectedOrder.note" class="bg-amber-50/50 p-4 rounded-2xl border border-amber-100/50 shadow-inner">
            <p class="text-[9px] text-amber-600 font-black uppercase tracking-[0.2em] mb-2">Ghi chú từ bếp</p>
            <p class="text-slate-700 text-xs font-bold leading-relaxed">"{{ selectedOrder.note }}"</p>
          </div>

          <div class="flex items-center justify-between p-6 bg-gradient-to-br from-primary-600 to-primary-700 rounded-[2rem] shadow-2xl shadow-primary-500/30 relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 blur-3xl rounded-full group-hover:scale-125 transition-transform duration-700"></div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-black/10 blur-3xl rounded-full group-hover:scale-125 transition-transform duration-700"></div>
            
            <div class="relative z-10">
              <span class="font-black text-white/70 text-[10px] uppercase tracking-[0.2em]">Tổng thanh toán</span>
              <div class="flex items-baseline gap-1 mt-0.5">
                <span class="text-3xl font-black text-white tracking-tighter">{{ formatCurrency(selectedOrder.total) }}</span>
              </div>
            </div>
            
            <button 
              @click="selectedOrder = null" 
              class="relative z-10 bg-white/20 hover:bg-white/30 backdrop-blur-md px-5 py-2.5 rounded-2xl text-white text-xs font-black uppercase tracking-widest transition-all hover:scale-105 active:scale-95"
            >
              Đóng
            </button>
          </div>
        </div>
      </div>
    </AppModal>
  </div>
</template>
