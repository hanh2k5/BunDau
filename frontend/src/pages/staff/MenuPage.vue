<script setup>
import { ref, computed, onMounted } from 'vue'
import { useProductsStore } from '@/stores/products.store'
import { useCartStore } from '@/stores/cart.store'
import { useNotificationStore } from '@/stores/notification.store'
import ProductCard from '@/components/products/ProductCard.vue'
import AppLoading from '@/components/common/AppLoading.vue'
import AppEmpty from '@/components/common/AppEmpty.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import AppModal from '@/components/common/AppModal.vue'
import { formatCurrency } from '@/utils/format'
import { useAuthStore } from '@/stores/auth.store'

const productsStore = useProductsStore()
const cart = useCartStore()
const auth = useAuthStore()
const notification = useNotificationStore()

// Search & Filter state
const searchQuery = ref('')
const selectedCategory = ref('all')
const currentPage = ref(1)
const itemsPerPage = ref(8) // User requested ~8 items per page

const categories = [
  { id: 'all', label: 'Tất cả', icon: '🥢' },
  { id: 'combo', label: 'Combo', icon: '🔥' },
  { id: 'bun-cha', label: 'Bún chả', icon: '🍜' },
  { id: 'bun-dau', label: 'Bún đậu', icon: '🍱' },
  { id: 'drink', label: 'Đồ uống', icon: '🥤' },
  { id: 'other', label: 'Đồ thêm', icon: '✨' },
]

// Modal state
const selectedProduct = ref(null)
const isDetailModalOpen = ref(false)

onMounted(() => {
  productsStore.fetchProducts()
})

const filteredProducts = computed(() => {
  return productsStore.products.filter(p => {
    const matchesSearch = p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      p.description?.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesCategory = selectedCategory.value === 'all' || p.category === selectedCategory.value
    return matchesSearch && matchesCategory
  })
})

const totalPages = computed(() => Math.ceil(filteredProducts.value.length / itemsPerPage.value))

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredProducts.value.slice(start, end)
})

function openDetailModal(product) {
  selectedProduct.value = product
  isDetailModalOpen.value = true
}

function closeDetailModal() {
  isDetailModalOpen.value = false
}

function addToCart(product) {
  cart.addItem(product)
  notification.success(`Đã thêm ${product.name} vào giỏ hàng`)
}

function handleSearch() {
  currentPage.value = 1
}

function handleCategoryChange(catId) {
  selectedCategory.value = catId
  currentPage.value = 1
}
</script>

<template>
  <div class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 px-2 sm:px-4">
      <div>
        <h1 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tighter">
          Thực đơn
        </h1>
        <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.3em] mt-3">Hương vị chuẩn vị Bắc • Tinh hoa ẩm thực</p>
      </div>

      <div v-if="cart.totalItems > 0"
        class="flex items-center gap-4 bg-white p-1.5 pl-5 rounded-full border border-primary-100 shadow-xl shadow-primary-500/10 group transition-all hover:scale-105">
        <span class="text-sm font-black text-slate-700 tracking-tight">{{ cart.totalItems }} món chờ đặt</span>
        <router-link to="/cart"
          class="bg-primary-600 hover:bg-primary-700 text-white text-[11px] font-black px-6 py-2.5 rounded-full transition-all shadow-lg shadow-primary-500/20 uppercase tracking-widest">
          Vào giỏ hàng
        </router-link>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col lg:flex-row items-center gap-4 px-2 sm:px-4">
      <!-- Search -->
      <div class="relative w-full lg:max-w-md">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <input v-model="searchQuery" @input="handleSearch" type="text"
          class="w-full bg-white border-2 border-slate-100 rounded-2xl pl-12 pr-4 py-3 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:ring-8 focus:ring-primary-500/10 transition-all shadow-sm font-bold text-sm"
          placeholder="Tìm món ăn yêu thích của bạn..." />
      </div>

      <!-- Categories -->
      <div class="flex-1 w-full overflow-hidden">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar pr-4">
          <button v-for="cat in categories" :key="cat.id" @click="handleCategoryChange(cat.id)"
            class="flex-shrink-0 px-5 py-2.5 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all duration-300 border shadow-sm active:scale-95"
            :class="selectedCategory === cat.id
              ? 'bg-primary-600 text-white border-primary-600 shadow-primary-500/20'
              : 'bg-white text-slate-500 border-slate-100 hover:border-primary-300 hover:text-primary-600'">
            <span class="mr-2">{{ cat.icon }}</span>
            {{ cat.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-if="productsStore.loading" class="flex justify-center items-center py-32">
      <AppLoading size="lg" />
    </div>

    <div v-else-if="filteredProducts.length > 0" class="px-2 sm:px-4 space-y-10">
      <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-8">
        <ProductCard v-for="product in paginatedProducts" :key="product.id" :product="product"
          :show-actions="auth.isLoggedIn" @add-to-cart="addToCart" @view-detail="openDetailModal" />
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex justify-center pt-6">
        <AppPagination v-model:currentPage="currentPage" :total-pages="totalPages"
          :total-items="filteredProducts.length" :items-per-page="itemsPerPage" />
      </div>
    </div>

    <div v-else class="py-32">
      <AppEmpty message="Không tìm thấy món" description="Thử tìm kiếm với từ khóa khác hoặc xem danh mục khác nhé" />
    </div>

    <!-- Product Detail Modal -->
    <AppModal :show="isDetailModalOpen" title="Chi tiết món ăn" size="md" @close="closeDetailModal">
      <div v-if="selectedProduct" class="space-y-6 pt-2">
        <div class="relative w-full aspect-video rounded-[2rem] overflow-hidden bg-slate-100 shadow-2xl shadow-slate-200/50 group">
          <img v-if="selectedProduct.image_url" :src="selectedProduct.image_url"
            class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" />
          <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        </div>

        <div>
          <div class="flex items-start justify-between gap-4 mb-4">
            <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">{{ selectedProduct.name }}</h2>
            <span class="text-2xl font-black text-primary-600 tracking-tighter">{{ formatCurrency(selectedProduct.price) }}</span>
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
