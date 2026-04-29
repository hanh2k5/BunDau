<script setup>
import { ref, onMounted } from 'vue'
import { usersApi } from '@/api/users.api'
import { useNotificationStore } from '@/stores/notification.store'
import { useAuthStore } from '@/stores/auth.store'
import AppLoading from '@/components/common/AppLoading.vue'
import AppModal from '@/components/common/AppModal.vue'

const auth = useAuthStore()
const notification = useNotificationStore()

const loading = ref(true)
const users = ref([])

const isModalOpen = ref(false)
const modalMode = ref('create') // 'create' | 'edit'
const submitting = ref(false)

const form = ref({
  id: null,
  name: '',
  email: '',
  password: '',
  role: 'staff'
})

onMounted(() => {
  fetchUsers()
})

async function fetchUsers() {
  loading.value = true
  try {
    const res = await usersApi.getAll()
    users.value = res.data.data
  } catch (error) {
    notification.error('Không thể tải danh sách nhân sự')
  } finally {
    loading.value = false
  }
}

function openCreateModal() {
  modalMode.value = 'create'
  form.value = { id: null, name: '', email: '', password: '', role: 'staff' }
  isModalOpen.value = true
}

function openEditModal(user) {
  modalMode.value = 'edit'
  form.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    password: '', // Empty password means don't change
    role: user.role
  }
  isModalOpen.value = true
}

function close() {
  isModalOpen.value = false
}

async function handleSubmit() {
  if (!form.value.name || !form.value.email) {
    notification.error('Vui lòng nhập đầy đủ Tên hiển thị và Email')
    return
  }

  if (modalMode.value === 'create' && !form.value.password) {
    notification.error('Mật khẩu không được để trống khi tạo mới')
    return
  }

  submitting.value = true
  try {
    if (modalMode.value === 'create') {
      await usersApi.create({
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
        role: form.value.role
      })
      notification.success('Tạo tài khoản thành công')
    } else {
      const payload = {
        name: form.value.name,
        email: form.value.email,
        role: form.value.role
      }
      if (form.value.password) payload.password = form.value.password

      await usersApi.update(form.value.id, payload)
      notification.success('Cập nhật tài khoản thành công')
    }
    close()
    fetchUsers()
  } catch (error) {
    notification.error(error.response?.data?.message || 'Có lỗi xảy ra')
  } finally {
    submitting.value = false
  }
}

async function handleDelete(user) {
  if (user.id === auth.user.id) {
    notification.error('Không thể tự xóa tài khoản của mình')
    return
  }
  if (!confirm(`Bạn có chắc muốn khóa/xóa tài khoản ${user.name}?`)) return

  try {
    await usersApi.delete(user.id)
    notification.success('Đã xóa tài khoản')
    fetchUsers()
  } catch (error) {
    notification.error('Có lỗi khi xóa tài khoản')
  }
}
</script>

<template>
  <div class="space-y-6 pb-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">👥 Nhân sự</h2>
        <p class="text-sm text-slate-500 mt-1 font-medium">Quản lý tài khoản và phân quyền nội bộ</p>
      </div>
      <button @click="openCreateModal" class="btn-apple !py-2.5 !px-5 shadow-lg shadow-primary-500/20">
        + Tạo tài khoản
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <AppLoading size="lg" />
    </div>

    <!-- Content -->
    <div v-else class="space-y-6">
      <!-- Desktop: Table -->
      <div class="hidden md:block card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50/50 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-100">
              <tr>
                <th class="px-6 py-4" style="white-space: nowrap;">Nhân viên</th>
                <th class="px-6 py-4" style="white-space: nowrap;">Tài khoản (Email)</th>
                <th class="px-6 py-4" style="white-space: nowrap;">Chức vụ</th>
                <th class="px-6 py-4 text-center" style="white-space: nowrap;">Thao tác</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50/50 transition-colors group">
                <td class="px-6 py-4" style="white-space: nowrap;">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-black text-sm shrink-0 shadow-sm border border-primary-200/50">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <span class="block font-bold text-slate-800 tracking-tight">{{ user.name }}</span>
                      <span v-if="user.id === auth.user.id" class="text-[10px] font-black text-green-600 uppercase tracking-widest bg-green-50 px-1.5 py-0.5 rounded">Bạn</span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 font-medium text-slate-500" style="white-space: nowrap;">{{ user.email }}</td>
                <td class="px-6 py-4" style="white-space: nowrap;">
                  <span 
                    class="inline-block px-3 py-1 text-[11px] font-black uppercase tracking-wider rounded-lg shadow-sm border"
                    style="white-space: nowrap;"
                    :class="user.role === 'admin' 
                      ? 'bg-purple-50 text-purple-600 border-purple-100' 
                      : 'bg-blue-50 text-blue-600 border-blue-100'"
                  >
                    {{ user.role === 'admin' ? 'Quản lý' : 'Nhân viên' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center" style="white-space: nowrap;">
                  <div class="flex items-center justify-center gap-2">
                    <button 
                      @click="openEditModal(user)"
                      class="px-3 py-1.5 text-[13px] font-bold text-slate-500 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition-all"
                    >
                      Sửa
                    </button>
                    <button 
                      @click="user.id !== auth.user.id && handleDelete(user)"
                      class="px-3 py-1.5 text-[13px] font-bold rounded-xl transition-all"
                      :class="user.id === auth.user.id ? 'invisible' : 'text-slate-500 hover:text-red-600 hover:bg-red-50'"
                      :disabled="user.id === auth.user.id"
                    >
                      Xóa
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Mobile: Card List -->
      <div class="grid grid-cols-1 gap-4 md:hidden">
        <div v-for="user in users" :key="user.id" class="card p-4 space-y-4 border-l-4" :class="user.role === 'admin' ? 'border-purple-500' : 'border-blue-500'">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-black text-base shadow-inner border border-slate-200">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h4 class="font-bold text-slate-900 leading-tight">{{ user.name }}</h4>
                <p class="text-xs text-slate-500 font-medium mt-0.5">{{ user.email }}</p>
              </div>
            </div>
            <span 
              class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-lg shadow-sm border"
              :class="user.role === 'admin' ? 'bg-purple-50 text-purple-600 border-purple-100' : 'bg-blue-50 text-blue-600 border-blue-100'"
            >
              {{ user.role === 'admin' ? 'Quản lý' : 'Nhân viên' }}
            </span>
          </div>
          
          <div class="flex items-center justify-between pt-3 border-t border-slate-100">
            <span v-if="user.id === auth.user.id" class="text-[11px] font-black text-green-600 uppercase tracking-[0.2em] bg-green-50 px-2 py-1 rounded-lg">Tài khoản của bạn</span>
            <span v-else class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">ID: #{{ user.id }}</span>
            
            <div class="flex items-center gap-2">
              <button @click="openEditModal(user)" class="px-4 py-2 text-sm font-bold text-slate-600 bg-slate-50 rounded-xl active:bg-slate-100 transition-colors">
                Chỉnh sửa
              </button>
              <button 
                v-if="user.id !== auth.user.id"
                @click="handleDelete(user)" 
                class="px-4 py-2 text-sm font-bold text-red-500 bg-red-50 rounded-xl active:bg-red-100 transition-colors"
              >
                Xóa
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <AppModal
      :show="isModalOpen"
      :title="modalMode === 'create' ? 'Tạo tài khoản nhân sự' : 'Chỉnh sửa tài khoản'"
      @close="close"
    >
      <div class="space-y-5 pt-2">
        <!-- Name -->
        <div>
          <label class="block text-[11px] font-black text-slate-400 mb-1.5 uppercase tracking-widest">Họ và Tên</label>
          <input 
            v-model="form.name" 
            type="text" 
            class="input-apple !bg-slate-50/50" 
            placeholder="VD: Nguyễn Văn A"
          />
        </div>

        <!-- Email -->
        <div>
          <label class="block text-[11px] font-black text-slate-400 mb-1.5 uppercase tracking-widest">Email (Tên đăng nhập)</label>
          <input 
            v-model="form.email" 
            type="email" 
            class="input-apple !bg-slate-50/50" 
            placeholder="VD: nv.a@gmail.com"
          />
        </div>

        <!-- Password -->
        <div>
          <label class="block text-[11px] font-black text-slate-400 mb-1.5 uppercase tracking-widest">
            Mật khẩu
            <span v-if="modalMode === 'edit'" class="font-bold lowercase normal-case text-primary-500 tracking-normal">(Để trống nếu giữ nguyên)</span>
          </label>
          <input 
            v-model="form.password" 
            type="password" 
            class="input-apple !bg-slate-50/50" 
            placeholder="Mật khẩu bí mật"
          />
        </div>

        <!-- Role -->
        <div>
          <label class="block text-[11px] font-black text-slate-400 mb-2 uppercase tracking-widest">Phân quyền chức vụ</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label 
              class="flex items-center gap-3 p-3.5 rounded-2xl border-2 transition-all cursor-pointer shadow-sm"
              :class="form.role === 'staff' ? 'border-primary-500 bg-primary-50/50 ring-4 ring-primary-500/10' : 'border-slate-100 bg-white hover:border-slate-200'"
            >
              <input type="radio" v-model="form.role" value="staff" class="w-4 h-4 text-primary-600 focus:ring-primary-500 border-slate-300">
              <div>
                <p class="text-sm font-bold text-slate-900 leading-none">Nhân viên</p>
                <p class="text-[10px] text-slate-500 font-medium mt-1">Chỉ quản lý đơn hàng</p>
              </div>
            </label>
            <label 
              class="flex items-center gap-3 p-3.5 rounded-2xl border-2 transition-all cursor-pointer shadow-sm"
              :class="[
                form.role === 'admin' ? 'border-primary-500 bg-primary-50/50 ring-4 ring-primary-500/10' : 'border-slate-100 bg-white hover:border-slate-200',
                form.id === auth.user.id ? 'opacity-50 cursor-not-allowed' : ''
              ]"
            >
              <input type="radio" v-model="form.role" value="admin" class="w-4 h-4 text-primary-600 focus:ring-primary-500 border-slate-300" :disabled="form.id === auth.user.id">
              <div>
                <p class="text-sm font-bold text-slate-900 leading-none">Quản lý</p>
                <p class="text-[10px] text-slate-500 font-medium mt-1">Toàn quyền hệ thống</p>
              </div>
            </label>
          </div>
        </div>
      </div>

      <template #footer>
        <button
          @click="close"
          class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:bg-slate-100 rounded-2xl transition-all"
        >
          Hủy bỏ
        </button>
        <button
          @click="handleSubmit"
          :disabled="submitting"
          class="btn-apple !py-2.5 !px-8 shadow-lg shadow-primary-500/20"
        >
          <span v-if="submitting" class="flex items-center gap-2">
            <AppLoading size="sm" /> Đang xử lý...
          </span>
          <span v-else>{{ modalMode === 'create' ? 'Tạo ngay' : 'Lưu thay đổi' }}</span>
        </button>
      </template>
    </AppModal>
  </div>
</template>
