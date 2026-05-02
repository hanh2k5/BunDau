<script setup>
import { reactive, watch, ref } from 'vue'
import AppButton from '@/components/common/AppButton.vue'
import { categories } from '@/utils/categories'

const props = defineProps({
  product: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['submit', 'cancel'])

const form = reactive({
  name: '',
  price: 0,
  description: '',
  category: 'other',
  is_available: true,
  original_updated_at: null,
})

const imageFile = ref(null)
const imagePreview = ref(null)
const deleteImage = ref(false)
const errors = reactive({})

// Populate form when editing
watch(
  () => props.product,
  (val) => {
    if (val) {
      form.name = val.name || ''
      form.price = val.price || 0
      form.description = val.description || ''
      form.category = val.category || 'other'
      form.is_available = val.is_available ?? true
      form.original_updated_at = val.updated_at || null
      imagePreview.value = val.image_url || null
    } else {
      form.name = ''
      form.price = 0
      form.description = ''
      form.category = 'other'
      form.is_available = true
      form.original_updated_at = null
      imagePreview.value = null
    }
    imageFile.value = null
    deleteImage.value = false
  },
  { immediate: true }
)

function handleFileChange(e) {
  const file = e.target.files[0]
  if (!file) return

  imageFile.value = file
  deleteImage.value = false
  
  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

function removeImage() {
  imageFile.value = null
  imagePreview.value = null
  deleteImage.value = true
}

function handleSubmit() {
  // Clear errors
  Object.keys(errors).forEach((key) => delete errors[key])

  // Basic validation
  if (!form.name.trim()) {
    errors.name = 'Tên sản phẩm là bắt buộc'
    return
  }
  if (form.price < 0) {
    errors.price = 'Giá không được âm'
    return
  }

  // Create FormData
  const formData = new FormData()
  formData.append('name', form.name)
  formData.append('price', form.price)
  formData.append('description', form.description || '')
  formData.append('category', form.category)
  formData.append('is_available', form.is_available ? '1' : '0')
  if (form.original_updated_at) {
    formData.append('original_updated_at', form.original_updated_at)
  }
  
  if (imageFile.value) {
    formData.append('image', imageFile.value)
  } else if (deleteImage.value) {
    formData.append('delete_image', '1')
  }

  emit('submit', formData)
}
</script>

<template>
  <form @submit.prevent="handleSubmit" class="space-y-5">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Info Section -->
      <div class="space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-1.5">Tên sản phẩm *</label>
          <input
            v-model="form.name"
            type="text"
            maxlength="150"
            class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-primary-400 focus:ring-4 focus:ring-primary-500/10 transition-all shadow-sm font-medium"
            placeholder="VD: Bún đậu mắm tôm"
          />
          <p v-if="errors.name" class="mt-1 text-sm text-red-500 font-medium">{{ errors.name }}</p>
        </div>

        <!-- Price -->
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-1.5">Giá (VNĐ) *</label>
          <input
            v-model.number="form.price"
            type="number"
            min="0"
            step="1000"
            class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-primary-400 focus:ring-4 focus:ring-primary-500/10 transition-all shadow-sm font-bold text-lg"
            placeholder="VD: 65000"
          />
          <p v-if="errors.price" class="mt-1 text-sm text-red-500 font-medium">{{ errors.price }}</p>
        </div>

        <!-- Category -->
        <div class="col-span-full">
          <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Phân loại món ăn *</label>
          <div class="flex flex-wrap gap-2">
            <label 
              v-for="cat in categories" 
              :key="cat.id"
              class="relative flex items-center gap-2 px-3 py-2 rounded-xl border transition-all cursor-pointer group active:scale-95"
              :class="form.category === cat.id 
                ? 'border-primary-500 bg-primary-50 text-primary-700 shadow-sm' 
                : 'border-slate-100 bg-slate-50/50 text-slate-500 hover:bg-white hover:border-slate-200'"
            >
              <input 
                type="radio" 
                v-model="form.category" 
                :value="cat.id" 
                class="sr-only"
              />
              <span class="text-base group-hover:scale-110 transition-transform">{{ cat.icon }}</span>
              <span class="text-[10px] font-bold uppercase tracking-wider leading-none">
                {{ cat.label }}
              </span>
              
              <!-- Selection indicator dot -->
              <div v-if="form.category === cat.id" class="w-1 h-1 rounded-full bg-primary-500"></div>
            </label>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-1.5">Mô tả</label>
          <textarea
            v-model="form.description"
            rows="4"
            maxlength="1000"
            class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-primary-400 focus:ring-4 focus:ring-primary-500/10 transition-all resize-none shadow-sm font-medium"
            placeholder="Mô tả chi tiết về món ăn..."
          />
        </div>
      </div>

      <!-- Image Section -->
      <div class="space-y-4">
        <label class="block text-sm font-bold text-slate-700 mb-1.5">Hình ảnh sản phẩm</label>
        
        <div 
          class="relative aspect-square rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 overflow-hidden group transition-all hover:border-primary-400"
          :class="{ 'border-primary-400 bg-primary-50/30': imagePreview }"
        >
          <img 
            v-if="imagePreview" 
            :src="imagePreview" 
            class="w-full h-full object-cover"
          />
          <div v-else class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-xs font-bold uppercase tracking-wider">Tải ảnh lên</span>
          </div>

          <!-- Overlay actions -->
          <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
            <label class="p-2.5 bg-white rounded-xl text-primary-600 cursor-pointer hover:scale-110 transition-transform shadow-lg">
              <input type="file" accept="image/*" class="hidden" @change="handleFileChange" />
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </label>
            <button 
              v-if="imagePreview"
              type="button"
              @click="removeImage"
              class="p-2.5 bg-white rounded-xl text-red-600 cursor-pointer hover:scale-110 transition-transform shadow-lg"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
        <p class="text-xs text-slate-500 font-medium text-center">* Hỗ trợ JPG, PNG. Tối đa 2MB.</p>
      </div>
    </div>

    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
      <!-- Availability -->
      <div class="flex items-center gap-3">
        <label class="relative inline-flex items-center cursor-pointer">
          <input v-model="form.is_available" type="checkbox" class="sr-only peer" />
          <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-500 peer-checked:after:border-white shadow-inner"></div>
        </label>
        <span class="text-sm font-bold text-slate-700">Đang phục vụ</span>
      </div>

      <!-- Actions -->
      <div class="flex gap-3">
        <AppButton variant="secondary" type="button" @click="emit('cancel')">
          Hủy
        </AppButton>
        <AppButton type="submit" :loading="loading" class="min-w-[120px]">
          {{ product ? 'Cập nhật' : 'Tạo mới' }}
        </AppButton>
      </div>
    </div>
  </form>
</template>
