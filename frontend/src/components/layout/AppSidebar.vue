<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import { useCartStore } from '@/stores/cart.store'

defineProps({ isOpen: { type: Boolean, default: false } })
defineEmits(['close'])

const route = useRoute()
const auth = useAuthStore()
const cart = useCartStore()

const menuItems = computed(() => {
  const items = [
    {
      name: 'menu', label: 'Thực đơn',
      svgPath: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
      roles: ['admin', 'staff', 'guest']
    },
    {
      name: 'cart', label: 'Giỏ hàng',
      svgPath: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
      roles: ['admin', 'staff'],
      badge: true
    },
    {
      name: 'orders', label: 'Đơn hàng',
      svgPath: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
      roles: ['admin', 'staff']
    },
    {
      name: 'products', label: 'Sản phẩm',
      svgPath: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
      roles: ['admin']
    },
    {
      name: 'revenue', label: 'Doanh thu',
      svgPath: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
      roles: ['admin']
    },
    {
      name: 'staff', label: 'Nhân sự',
      svgPath: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
      roles: ['admin']
    },
  ]
  const userRole = auth.user?.role || 'guest'
  return items.filter(i => i.roles.includes(userRole))
})
</script>

<template>
  <!-- Mobile backdrop -->
  <transition name="fade">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40 bg-black/25 backdrop-blur-sm lg:hidden transition-opacity"
      @click="$emit('close')"
    ></div>
  </transition>

  <!-- Sidebar panel -->
  <aside
    class="fixed top-0 left-0 bottom-0 z-50 flex flex-col w-[260px] bg-white/95 backdrop-blur-2xl border-r border-slate-200/60 transform transition-transform duration-300 ease-in-out lg:translate-x-0"
    :class="isOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'"
  >
    <!-- Logo -->
    <div class="flex items-center gap-3 px-6 h-[60px] border-b border-slate-100 shrink-0">
      <div class="w-8 h-8 rounded-xl bg-primary-600 flex items-center justify-center text-white shadow-sm shadow-primary-500/20">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
      </div>
      <span class="text-lg font-bold text-slate-800 tracking-tight">Bún Đậu</span>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto p-4 space-y-1">
      <router-link
        v-for="item in menuItems"
        :key="item.name"
        :to="{ name: item.name }"
        @click="$emit('close')"
        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200"
        :class="route.name === item.name 
          ? 'bg-primary-50 text-primary-600 shadow-sm shadow-primary-500/10' 
          : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
      >
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.svgPath" />
        </svg>
        <span class="flex-1">{{ item.label }}</span>
        <span
          v-if="item.badge && cart.totalItems > 0"
          class="bg-primary-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full"
        >{{ cart.totalItems }}</span>
      </router-link>
    </nav>

    <!-- User -->
    <div v-if="auth.isLoggedIn" class="p-4 border-t border-slate-100 shrink-0">
      <div class="flex items-center gap-3 px-2 py-2">
        <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-sm shrink-0">
          {{ auth.user?.name?.charAt(0)?.toUpperCase() }}
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-sm font-bold text-slate-800 truncate">{{ auth.user?.name }}</p>
          <p class="text-xs font-medium text-slate-500 capitalize">{{ auth.user?.role }}</p>
        </div>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
