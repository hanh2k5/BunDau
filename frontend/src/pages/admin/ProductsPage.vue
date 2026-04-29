<script setup>
import { onMounted, ref, computed } from 'vue'
import { useProductsStore } from '@/stores/products.store'
import { useNotificationStore } from '@/stores/notification.store'
import { formatCurrency, removeAccents } from '@/utils/format'
import ProductForm from '@/components/products/ProductForm.vue'
import AppModal from '@/components/common/AppModal.vue'
import AppLoading from '@/components/common/AppLoading.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import AppEmpty from '@/components/common/AppEmpty.vue'

const productsStore = useProductsStore()
const notification = useNotificationStore()

const showModal = ref(false)
const editingProduct = ref(null)
const formLoading = ref(false)

// Search & Pagination state
const searchQuery = ref('')
const statusFilter = ref('all') // 'all', 'available', 'unavailable'
const categoryFilter = ref('all')
const currentPage = ref(1)
const itemsPerPage = ref(10) // Increased for better desktop experience

onMounted(() => {
  productsStore.fetchProducts()
})

const categories = [
  { id: 'all', label: 'Tất cả', icon: '🍱' },
  { id: 'bun-dau', label: 'Bún đậu', icon: '🥢' },
  { id: 'bun-cha', label: 'Bún chả', icon: '🍜' },
  { id: 'combo', label: 'Combo', icon: '🔥' },
  { id: 'drink', label: 'Đồ uống', icon: '🥤' },
  { id: 'other', label: 'Đồ thêm', icon: '✨' },
]

// Computed: Filter by search query, status & category
const filteredProducts = computed(() => {
  let result = productsStore.allProducts

  // Status filter
  if (statusFilter.value === 'available') {
    result = result.filter(p => p.is_available)
  } else if (statusFilter.value === 'unavailable') {
    result = result.filter(p => !p.is_available)
  }

  // Category filter
  if (categoryFilter.value !== 'all') {
    result = result.filter(p => p.category === categoryFilter.value)
  }

  // Search filter
  if (searchQuery.value) {
    const lowerQuery = removeAccents(searchQuery.value.toLowerCase())
    result = result.filter(p => {
      const name = removeAccents(p.name?.toLowerCase() || '')
      const desc = removeAccents(p.description?.toLowerCase() || '')
      return name.includes(lowerQuery) || desc.includes(lowerQuery)
    })
  }

  return result
})

// Computed: Pagination logic
const totalPages = computed(() => Math.ceil(filteredProducts.value.length / itemsPerPage.value) || 1)

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredProducts.value.slice(start, start + itemsPerPage.value)
})

function handleFilterChange() {
  currentPage.value = 1
}

function openCreate() {
  editingProduct.value = null
  showModal.value = true
}

function openEdit(product) {
  editingProduct.value = { ...product }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingProduct.value = null
}

async function handleSubmit(formData) {
  formLoading.value = true
  try {
    if (editingProduct.value) {
      await productsStore.updateProduct(editingProduct.value.id, formData)
      notification.success('Cập nhật sản phẩm thành công')
    } else {
      await productsStore.createProduct(formData)
      notification.success('Tạo sản phẩm thành công')
    }
    closeModal()
  } catch (err) {
    if (err.response?.status === 404 || err.response?.status === 409) {
      notification.warning(err.response?.data?.message || 'Dữ liệu đã thay đổi, đang đồng bộ lại...')
      productsStore.fetchProducts()
      closeModal()
      return
    }
    const message = err.response?.data?.message || 'Lỗi xử lý sản phẩm'
    const errors = err.response?.data?.errors
    if (errors) {
      const firstError = Object.values(errors)[0]
      notification.error(Array.isArray(firstError) ? firstError[0] : firstError)
    } else {
      notification.error(message)
    }
  } finally {
    formLoading.value = false
  }
}

async function handleToggle(product) {
  try {
    await productsStore.toggleProduct(product.id)
    notification.success(product.is_available ? 'Đã tạm ngưng món' : 'Đã mở bán lại món')
  } catch (err) {
    notification.error('Lỗi cập nhật trạng thái')
  }
}

async function handleDelete(product) {
  if (!confirm(`Bạn chắc chắn muốn xóa vĩnh viễn "${product.name}"?`)) return

  try {
    await productsStore.deleteProduct(product.id)
    notification.success('Đã xóa sản phẩm thành công')
    if (paginatedProducts.value.length === 0 && currentPage.value > 1) {
      currentPage.value--
    }
  } catch (err) {
    notification.error('Lỗi khi xóa sản phẩm')
  }
}
</script>

<template>
  <div class="space-y-6 pb-8">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">🍱 Sản phẩm</h2>
        <p class="text-sm text-slate-500 mt-1 font-medium">Quản lý kho thực đơn và kho món ăn</p>
      </div>
      <button @click="openCreate" class="btn-apple !py-2.5 !px-5 shadow-lg shadow-primary-500/20">
        + Thêm món mới
      </button>
    </div>

    <!-- Filters & Search -->
    <div class="card p-4 sm:p-5 space-y-4">
      <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <input
          v-model="searchQuery"
          @input="handleFilterChange"
          type="text"
          class="input-apple !pl-11 !bg-slate-50/50"
          placeholder="Tìm kiếm theo tên món ăn..."
        />
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <select
          v-model="categoryFilter"
          @change="handleFilterChange"
          class="input-apple !w-auto min-w-[160px] !py-2 !text-sm"
        >
          <option value="all">Tất cả phân loại</option>
          <option v-for="cat in categories.slice(1)" :key="cat.id" :value="cat.id">
            {{ cat.icon }} {{ cat.label }}
          </option>
        </select>

        <select 
          v-model="statusFilter"
          @change="handleFilterChange"
          class="input-apple !w-auto min-w-[160px] !py-2 !text-sm"
        >
          <option value="all">Tất cả trạng thái</option>
          <option value="available">Đang phục vụ</option>
          <option value="unavailable">Ngưng phục vụ</option>
        </select>
      </div>
    </div>

    <!-- Content -->
    <div v-if="productsStore.loading" class="flex justify-center py-20">
      <AppLoading size="lg" />
    </div>

    <div v-else-if="filteredProducts.length === 0">
      <AppEmpty 
        message="Không tìm thấy sản phẩm nào phù hợp" 
        description="Hãy thử đổi từ khóa tìm kiếm hoặc chọn bộ lọc khác"
      />
    </div>

    <div v-else class="space-y-6">
      <!-- Desktop Table -->
      <div class="hidden md:block card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50/50 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-100">
              <tr>
                <th class="px-6 py-4">Sản phẩm</th>
                <th class="px-6 py-4">Mô tả</th>
                <th class="px-6 py-4 text-right">Giá tiền</th>
                <th class="px-6 py-4 text-center">Trạng thái</th>
                <th class="px-6 py-4 text-center">Thao tác</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="product in paginatedProducts" :key="product.id" class="hover:bg-slate-50/50 transition-colors group">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 shadow-sm">
                      <img v-if="product.image_url" :src="product.image_url" class="w-full h-full object-cover" />
                      <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                      </div>
                    </div>
                    <div>
                      <span class="block font-bold text-slate-800 tracking-tight">{{ product.name }}</span>
                      <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">ID: #{{ product.id }}</span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <p class="text-xs text-slate-500 line-clamp-2 max-w-xs leading-relaxed" :title="product.description">
                    {{ product.description || '—' }}
                  </p>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="font-black text-primary-600 text-base tracking-tight">{{ formatCurrency(product.price) }}</span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex justify-center">
                    <button
                      @click="handleToggle(product)"
                      class="relative inline-flex h-6 w-11 items-center rounded-full transition-all duration-300 focus:outline-none shadow-inner"
                      :class="product.is_available ? 'bg-primary-500' : 'bg-slate-200'"
                    >
                      <span
                        class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform duration-300 shadow-sm"
                        :class="product.is_available ? 'translate-x-5.5' : 'translate-x-0.5'"
                      />
                    </button>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex justify-center items-center gap-2">
                    <button
                      @click="openEdit(product)"
                      class="p-2 text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition-all"
                      title="Chỉnh sửa"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="handleDelete(product)"
                      class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all"
                      title="Xóa"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Mobile Card List -->
      <div class="grid grid-cols-1 gap-4 md:hidden">
        <div v-for="product in paginatedProducts" :key="product.id" class="card p-4 space-y-4">
          <div class="flex gap-4">
            <div class="w-20 h-20 rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 shadow-sm">
              <img v-if="product.image_url" :src="product.image_url" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
            <div class="flex-1 min-w-0 flex flex-col justify-between">
              <div>
                <h4 class="font-bold text-slate-900 leading-tight truncate pr-2">{{ product.name }}</h4>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">ID: #{{ product.id }}</p>
                <p class="text-xs text-slate-500 line-clamp-2 mt-1.5">{{ product.description || 'Hương vị bún đậu truyền thống' }}</p>
              </div>
              <div class="flex items-center justify-between mt-2">
                <span class="font-black text-primary-600 text-base tracking-tight">{{ formatCurrency(product.price) }}</span>
              </div>
            </div>
          </div>
          
          <div class="flex items-center justify-between pt-3 border-t border-slate-100">
            <div class="flex items-center gap-3">
              <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Mở bán:</span>
              <button
                @click="handleToggle(product)"
                class="relative inline-flex h-5 w-10 items-center rounded-full transition-all duration-300 shadow-inner"
                :class="product.is_available ? 'bg-primary-500' : 'bg-slate-200'"
              >
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-300 shadow-sm"
                  :class="product.is_available ? 'translate-x-5.5' : 'translate-x-0.5'"
                />
              </button>
            </div>
            <div class="flex items-center gap-2">
              <button @click="openEdit(product)" class="p-2 text-slate-500 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button @click="handleDelete(product)" class="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex justify-center pt-4">
        <AppPagination
          v-model:currentPage="currentPage"
          :total-pages="totalPages"
          :total-items="filteredProducts.length"
          :items-per-page="itemsPerPage"
        />
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <AppModal
      :show="showModal"
      :title="editingProduct ? 'Cập nhật món ăn' : 'Thêm món ăn mới'"
      size="md"
      @close="closeModal"
    >
      <ProductForm
        :product="editingProduct"
        :loading="formLoading"
        @submit="handleSubmit"
        @cancel="closeModal"
      />
    </AppModal>
  </div>
</template>
