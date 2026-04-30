<script setup>
import { useAuthStore } from '@/stores/auth.store'
import { useNotificationStore } from '@/stores/notification.store'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const notification = useNotificationStore()
const router = useRouter()

async function handleLogout() {
  await auth.logout()
  notification.success('Đăng xuất thành công')
  router.push('/login')
}
</script>

<template>
  <header class="sticky top-0 z-30 h-[60px] bg-white/80 backdrop-blur-xl border-b border-slate-200/60 flex items-center justify-between px-4 sm:px-6">
    <!-- Left -->
    <div class="flex items-center gap-3">
      <!-- Mobile menu button -->
      <button
        class="lg:hidden p-2 -ml-2 rounded-xl text-slate-600 hover:bg-slate-100 transition-colors"
        @click="$emit('toggle-sidebar')"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <slot name="title">
        <span class="text-base font-black text-slate-900 tracking-wider uppercase">Bún Đậu A Lử</span>
      </slot>
    </div>

    <!-- Right -->
    <div class="flex items-center gap-2 sm:gap-4">
      <template v-if="auth.isLoggedIn">
        <!-- Logout -->
        <button
          @click="handleLogout"
          class="text-[13px] font-black uppercase tracking-widest text-slate-500 hover:text-red-600 px-4 py-2 rounded-xl border border-slate-200/60 hover:border-red-200 hover:bg-red-50 transition-all duration-300"
        >
          Thoát
        </button>
      </template>
      <template v-else>
        <!-- Subtle Admin Login -->
        <router-link
          to="/login"
          class="flex items-center gap-1.5 text-[13px] font-bold text-slate-400 hover:text-primary-600 transition-all px-3 py-2 rounded-xl hover:bg-primary-50"
          title="Khu vực dành cho Quản lý"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.1" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
          <span class="hidden sm:inline uppercase tracking-widest">Nội bộ</span>
        </router-link>
      </template>
    </div>
</header>
</template>
