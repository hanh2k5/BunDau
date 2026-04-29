<script setup>
import AppButton from '@/components/common/AppButton.vue'
import { useRouter } from 'vue-router'

const router = useRouter()
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-surface">
    <div class="text-center">
      <span class="text-8xl mb-6 block">🚫</span>
      <h1 class="text-4xl font-bold text-white mb-3">403</h1>
      <p class="text-lg text-gray-400 mb-8">Bạn không có quyền truy cập trang này</p>
      <AppButton @click="router.push('/')">
        ← Về trang chủ
      </AppButton>
    </div>
  </div>
</template>
