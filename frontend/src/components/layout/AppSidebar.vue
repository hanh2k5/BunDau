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
    class="fixed top-0 left-0 bottom-0 z-50 flex flex-col w-[260px] bg-white border-r border-slate-200/60 transform transition-transform duration-300 ease-in-out lg:translate-x-0"
    :class="isOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'"
  >
    <!-- Logo -->
    <div class="flex items-center gap-3 px-7 h-[70px] border-b border-slate-100 shrink-0">
      <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white shadow-lg shadow-primary-500/30">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
      </div>
      <div class="flex flex-col">
        <span class="text-base font-black text-slate-900 tracking-tight leading-none uppercase">Bún Đậu A Lử</span>
        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Hương vị gia truyền</span>
      </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto p-4 space-y-1.5 custom-scrollbar">
      <div class="px-3 mb-2">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Danh mục</p>
      </div>

      <router-link
        v-for="item in menuItems"
        :key="item.name"
        :to="{ name: item.name }"
        @click="$emit('close')"
        class="flex items-center gap-3 px-4 py-3 rounded-2xl text-[13px] font-bold transition-all duration-300 group relative overflow-hidden"
        :class="route.name === item.name 
          ? 'bg-primary-50 text-primary-600 shadow-sm shadow-primary-500/5' 
          : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'"
      >
        <!-- Active indicator -->
        <div 
          v-if="route.name === item.name" 
          class="absolute left-0 top-3 bottom-3 w-1 bg-primary-600 rounded-r-full"
        ></div>

        <svg 
          class="w-5 h-5 shrink-0 transition-transform group-hover:scale-110" 
          :class="route.name === item.name ? 'text-primary-600' : 'text-slate-400 group-hover:text-slate-600'"
          fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.svgPath" />
        </svg>
        <span class="flex-1">{{ item.label }}</span>
        
        <span
          v-if="item.badge && cart.totalItems > 0"
          class="bg-primary-600 text-white text-[10px] font-black px-2 py-0.5 rounded-lg shadow-lg shadow-primary-500/30 animate-bounce"
        >{{ cart.totalItems }}</span>

        <!-- Chevron for non-active -->
        <svg 
          v-if="route.name !== item.name"
          class="w-3.5 h-3.5 text-slate-300 opacity-0 group-hover:opacity-100 transition-all -translate-x-2 group-hover:translate-x-0" 
          fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
        </svg>
      </router-link>
    </nav>

    <!-- User -->
    <div v-if="auth.isLoggedIn" class="p-5 border-t border-slate-100 shrink-0">
      <div class="flex items-center gap-3 p-3 bg-slate-50/50 rounded-[1.5rem] border border-slate-100/50 shadow-inner group hover:bg-white hover:shadow-md transition-all duration-500 cursor-default">
        <div class="relative">
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 text-primary-700 flex items-center justify-center font-black text-sm shrink-0 shadow-sm group-hover:scale-105 transition-transform">
            {{ auth.user?.name?.charAt(0)?.toUpperCase() }}
          </div>
          <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></div>
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-xs font-black text-slate-800 truncate tracking-tight">{{ auth.user?.name }}</p>
          <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">
            {{ auth.user?.role === 'admin' ? 'Quản lý' : 'Nhân sự' }}
          </p>
        </div>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
