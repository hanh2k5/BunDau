<script setup>
defineProps({
  icon: { type: String, default: '📭' },
  title: { type: String, default: 'Không có dữ liệu' },
  description: { type: String, default: '' },
})
</script>

<template>
  <div class="flex flex-col items-center justify-center py-20 px-6 text-center">
    <!-- Icon -->
    <div
      class="flex items-center justify-center mb-8"
      style="width: 96px; height: 96px; border-radius: 28px; background: #f5f5f7; border: 1px solid rgba(0,0,0,0.06); font-size: 42px;"
    >
      {{ icon }}
    </div>

    <h3 class="font-semibold mb-2" style="font-size: 22px; color: #1d1d1f; letter-spacing: -0.025em;">
      {{ title }}
    </h3>
    <p v-if="description" style="font-size: 14px; color: #86868b; max-width: 280px; line-height: 1.5;">
      {{ description }}
    </p>

    <div class="mt-8">
      <slot />
    </div>
  </div>
</template>
