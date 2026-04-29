<script setup>
import { onMounted } from 'vue'
import AppToast from '@/components/common/AppToast.vue'

onMounted(() => {
  // Sync auth state across tabs
  window.addEventListener('storage', (event) => {
    if (event.key === 'auth' && !event.newValue) {
      // User logged out in another tab
      window.location.reload()
    }
  })
})
</script>

<template>
  <router-view />
  <AppToast />
</template>
