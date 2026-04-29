<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import { useNotificationStore } from '@/stores/notification.store'
import AppLoading from '@/components/common/AppLoading.vue'

const router = useRouter()
const auth = useAuthStore()
const notification = useNotificationStore()

const form = reactive({
  email: '',
  password: '',
})
const loading = ref(false)
const errors = reactive({})

async function handleLogin() {
  // Clear errors
  Object.keys(errors).forEach((key) => delete errors[key])

  // Client validation
  if (!form.email) {
    errors.email = 'Email là bắt buộc'
    return
  }
  if (!form.password) {
    errors.password = 'Mật khẩu là bắt buộc'
    return
  }

  loading.value = true
  try {
    await auth.login(form)
    notification.success('Chào mừng bạn quay trở lại! 🎉')
    router.push('/menu')
  } catch (err) {
    const status = err.response?.status
    if (status === 422) {
      const serverErrors = err.response.data.errors || {}
      Object.assign(errors, serverErrors)
    } else if (status === 401) {
      errors.general = err.response.data.message || 'Email hoặc mật khẩu không chính xác'
    } else {
      errors.general = 'Lỗi hệ thống, vui lòng thử lại sau'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center p-4 bg-slate-50/50">
    <div class="w-full max-w-[400px]">
      <!-- Card -->
      <div class="card p-8 sm:p-10 shadow-2xl shadow-slate-200/50 border border-slate-200/60">
        
        <!-- Logo & Title -->
        <div class="text-center mb-10">
          <div class="w-16 h-16 rounded-2xl bg-primary-600 mx-auto mb-5 flex items-center justify-center text-white shadow-lg shadow-primary-500/30 ring-4 ring-primary-500/10">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
          </div>
          <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-1.5">Bún Đậu Quán</h1>
          <p class="text-sm text-slate-400 font-bold uppercase tracking-widest">Hệ thống nội bộ</p>
        </div>

        <!-- General Error -->
        <div v-if="errors.general" class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-600 text-sm font-bold animate-shake">
          {{ errors.general }}
        </div>

        <!-- Login Form -->
        <form @submit.prevent="handleLogin" class="space-y-5">
          <!-- Email -->
          <div>
            <label class="block text-[11px] font-black text-slate-400 mb-2 uppercase tracking-widest">Tài khoản Email</label>
            <input
              v-model="form.email"
              type="email"
              autocomplete="email"
              class="input-apple !bg-slate-50/50 !py-3"
              placeholder="admin@gmail.com"
            />
            <p v-if="errors.email" class="mt-1.5 text-xs text-red-500 font-bold pl-1">
              {{ Array.isArray(errors.email) ? errors.email[0] : errors.email }}
            </p>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-[11px] font-black text-slate-400 mb-2 uppercase tracking-widest">Mật khẩu bảo mật</label>
            <input
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              class="input-apple !bg-slate-50/50 !py-3"
              placeholder="••••••••"
            />
            <p v-if="errors.password" class="mt-1.5 text-xs text-red-500 font-bold pl-1">
              {{ Array.isArray(errors.password) ? errors.password[0] : errors.password }}
            </p>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            class="btn-apple w-full !py-3.5 !text-base shadow-xl shadow-primary-500/20 active:scale-[0.98] transition-all mt-4"
            :disabled="loading"
          >
            <span v-if="loading" class="flex items-center justify-center gap-2">
              <AppLoading size="sm" /> Đang đăng nhập...
            </span>
            <span v-else>Đăng nhập hệ thống</span>
          </button>
        </form>

        <!-- Footer link -->
        <div class="mt-10 pt-8 border-t border-slate-100 text-center">
          <router-link 
            to="/" 
            class="text-sm font-black text-primary-600 hover:text-primary-700 uppercase tracking-widest flex items-center justify-center gap-2 transition-all hover:gap-3"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Quay lại thực đơn
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}
.animate-shake {
  animation: shake 0.2s ease-in-out 0s 2;
}
</style>
