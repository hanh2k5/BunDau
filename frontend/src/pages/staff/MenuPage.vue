<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import { useCartStore } from '@/stores/cart.store'
import { useNotificationStore } from '@/stores/notification.store'
import { productsApi } from '@/api/products.api'
import { formatCurrency, removeAccents } from '@/utils/format'
import { categories } from '@/utils/categories'
import ProductCard from '@/components/products/ProductCard.vue'
import AppLoading from '@/components/common/AppLoading.vue'
import AppEmpty from '@/components/common/AppEmpty.vue'
import AppModal from '@/components/common/AppModal.vue'
import AppPagination from '@/components/common/AppPagination.vue'

const router = useRouter()
const auth = useAuthStore()
const cart = useCartStore()
const notification = useNotificationStore()

const loading = ref(true)
const products = ref([])
const activeCategory = ref('all')
const searchQuery = ref('')

// Pagination state
const currentPage = ref(1)
const itemsPerPage = ref(8)

const isDetailModalOpen = ref(false)
const selectedProduct = ref(null)

onMounted(async () => {
  try {
    const res = await productsApi.list()
    products.value = res.data.data
  } catch (err) {
    console.error('Lỗi tải sản phẩm:', err)
  } finally {
    loading.value = false
  }
})

const filteredProducts = computed(() => {
  let result = products.value

  if (activeCategory.value !== 'all') {
    result = result.filter(p => p.category === activeCategory.value)
  }

  if (searchQuery.value) {
    const q = removeAccents(searchQuery.value.toLowerCase())
    result = result.filter(p => 
      removeAccents(p.name.toLowerCase()).includes(q) || 
      removeAccents((p.description || '').toLowerCase()).includes(q)
    )
  }

  return result
})

const totalPages = computed(() => Math.ceil(filteredProducts.value.length / itemsPerPage.value) || 1)

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredProducts.value.slice(start, start + itemsPerPage.value)
})

function handleFilterChange() {
  currentPage.value = 1
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

watch([activeCategory, searchQuery], () => {
  handleFilterChange()
})

function addToCart(product) {
  cart.addItem(product)
  notification.success(`Đã thêm ${product.name} vào giỏ hàng!`)
}

function openDetailModal(product) {
  selectedProduct.value = product
  isDetailModalOpen.value = true
}

function closeDetailModal() {
  isDetailModalOpen.value = false
  selectedProduct.value = null
}
</script>

<template>
  <div class="space-y-8 pb-20">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 px-2">
      <div class="space-y-2">
        <h1 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tighter leading-none">Thực đơn</h1>
        <p class="text-[12px] sm:text-[13px] text-slate-400 font-bold uppercase tracking-[0.2em]">Hương vị chuẩn vị Bắc • Tinh hoa ẩm thực</p>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 pt-1">
          <div class="flex items-center gap-1.5 text-slate-400">
            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-[10px] font-bold text-slate-500">Số 3, Đường 35, Hiệp Bình, TP. HCM</span>
          </div>
          <div class="flex items-center gap-1.5 text-slate-400 border-l border-slate-200 pl-4 sm:border-l sm:pl-4">
            <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-[10px] font-bold text-slate-500 whitespace-nowrap">Mở cửa: 10:00 - 21:00</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Search & Filter Bar (Fixed Top) -->
    <div class="sticky top-0 z-30 bg-white/90 backdrop-blur-xl border-b border-slate-200/60 px-4 py-3 mb-6">
      <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center gap-4">
        <div class="relative w-full max-w-lg">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
          <input
            v-model="searchQuery"
            type="text"
            class="w-full bg-slate-100 border-none rounded-xl py-2.5 pl-10 pr-4 text-sm font-bold text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary-500/10 focus:outline-none transition-all"
            placeholder="Tìm nhanh món ăn..."
          />
        </div>

        <!-- Categories -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar w-full sm:w-auto">
          <button
            @click="activeCategory = 'all'"
            class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap"
            :class="activeCategory === 'all' 
              ? 'bg-primary-600 text-white shadow-md shadow-primary-500/20 active:scale-95' 
              : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
          >
            <span class="mr-1.5">🥢</span> Tất cả
          </button>
          <button
            v-for="cat in categories"
            :key="cat.id"
            @click="activeCategory = cat.id"
            class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap"
            :class="activeCategory === cat.id 
              ? 'bg-primary-600 text-white shadow-md shadow-primary-500/20 active:scale-95' 
              : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
          >
            <span class="mr-1.5">{{ cat.icon }}</span> {{ cat.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Product Grid -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 px-2">
      <div v-for="i in 8" :key="i" class="card h-80 animate-pulse bg-slate-100/50"></div>
    </div>

    <div v-else-if="filteredProducts.length > 0" class="space-y-10">
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 px-2">
        <ProductCard
          v-for="product in paginatedProducts"
          :key="product.id"
          :product="product"
          @add-to-cart="addToCart"
          @view-detail="openDetailModal"
        />
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex justify-center px-2 pt-4 border-t border-slate-100">
        <AppPagination
          v-model:currentPage="currentPage"
          :total-pages="totalPages"
          :total-items="filteredProducts.length"
          :items-per-page="itemsPerPage"
        />
      </div>
    </div>

    <div v-else class="py-32">
      <AppEmpty message="Không tìm thấy món" description="Thử tìm kiếm với từ khóa khác hoặc xem danh mục khác nhé" />
    </div>

    <!-- Product Detail Modal -->
    <AppModal :show="isDetailModalOpen" title="Chi tiết món ăn" size="md" @close="closeDetailModal">
      <div v-if="selectedProduct" class="space-y-6 pt-2 select-none">
        <div class="relative w-full aspect-square overflow-hidden rounded-3xl bg-slate-100 border border-slate-100 shadow-inner mb-6">
          <img v-if="selectedProduct.image_url" :src="selectedProduct.image_url"
            class="w-full h-full object-cover pointer-events-none" 
            draggable="false" />
          <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
            <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        </div>

        <div class="px-1">
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 sm:gap-4 mb-4">
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-tight flex-1">{{ selectedProduct.name }}</h2>
            <span class="text-xl sm:text-2xl font-black text-primary-600 tracking-tighter whitespace-nowrap">{{ formatCurrency(selectedProduct.price) }}</span>
          </div>

          <div class="flex gap-2 mb-6">
            <span class="px-3 py-1 bg-primary-50 text-primary-600 text-[10px] font-black uppercase tracking-widest rounded-lg border border-primary-100">
              {{ categories.find(c => c.id === selectedProduct.category)?.label || 'Đặc sản' }}
            </span>
            <span v-if="selectedProduct.is_available" class="px-3 py-1 bg-green-50 text-green-600 text-[10px] font-black uppercase tracking-widest rounded-lg border border-green-100">
              Sẵn sàng
            </span>
          </div>

          <div class="bg-slate-50/50 p-6 rounded-3xl border border-slate-100 shadow-inner">
            <p class="text-[9px] text-slate-400 font-black uppercase tracking-[0.2em] mb-3">Mô tả chi tiết</p>
            <p class="text-sm text-slate-600 leading-relaxed font-bold">
              "{{ selectedProduct.description || 'Món ăn mang hương vị bún đậu truyền thống, được chuẩn bị từ các nguyên liệu sạch và tươi nhất trong ngày.' }}"
            </p>
          </div>
        </div>

        <div v-if="auth.isLoggedIn" class="pt-2">
          <button @click="() => { addToCart(selectedProduct); closeDetailModal(); }"
            class="w-full bg-primary-600 hover:bg-primary-700 text-white py-4 rounded-2xl font-black text-[12px] uppercase tracking-[0.3em] transition-all shadow-xl shadow-primary-500/30 active:scale-95">
            Thêm vào đơn ngay
          </button>
        </div>
      </div>
    </AppModal>

    <!-- Floating Quick Cart (Fixed Bottom) -->
    <transition name="slide-up">
      <div v-if="cart.totalItems > 0" class="fixed bottom-0 left-0 right-0 z-40 lg:hidden">
        <button 
          @click="() => router.push({ name: 'cart' })"
          class="w-full bg-white border-t border-slate-200 shadow-[0_-8px_30px_rgba(0,0,0,0.05)] p-4 flex items-center justify-between active:bg-slate-50 transition-colors"
        >
          <div class="flex items-center gap-3 pl-2">
            <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center text-primary-600 relative">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
              <div class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-primary-600 text-white text-[10px] font-black rounded-full flex items-center justify-center border-2 border-white">
                {{ cart.totalItems }}
              </div>
            </div>
            <div class="text-left">
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-0.5">Vào giỏ hàng nhanh</p>
              <p class="text-xl font-black text-primary-600 tracking-tighter leading-none">{{ formatCurrency(cart.totalAmount) }}</p>
            </div>
          </div>
          <div class="pr-2 text-primary-600">
            <div class="flex items-center gap-1 font-black text-[11px] uppercase tracking-widest">
              Xem ngay
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </div>
        </button>
      </div>
    </transition>
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

.slide-up-enter-active, .slide-up-leave-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-up-enter-from, .slide-up-leave-to {
  transform: translateY(120%) scale(0.9);
  opacity: 0;
}
</style>
