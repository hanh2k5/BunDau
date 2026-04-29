<script setup>
import { ref } from 'vue'
import AppSidebar from './AppSidebar.vue'
import AppNavbar from './AppNavbar.vue'

const isSidebarOpen = ref(false)

function toggleSidebar() {
  isSidebarOpen.value = !isSidebarOpen.value
}

function closeSidebar() {
  isSidebarOpen.value = false
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <!-- Sidebar (fixed, 260px wide on desktop) -->
    <AppSidebar :is-open="isSidebarOpen" @close="closeSidebar" />

    <!-- Main content pushed right on desktop -->
    <div class="flex flex-col min-h-screen transition-all duration-300 ease-in-out lg:ml-[260px]">
      <AppNavbar @toggle-sidebar="toggleSidebar" />

      <main class="flex-1 w-full max-w-7xl mx-auto p-4 sm:p-8">
        <router-view v-slot="{ Component }">
          <transition name="page" mode="out-in">
            <component :is="Component" :key="$route.path" />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>
