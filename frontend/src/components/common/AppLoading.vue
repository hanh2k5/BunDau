<script setup>
defineProps({
  size: { type: String, default: 'md' }, // sm, md, lg
})
</script>

<template>
  <div
    class="rounded-full border-2 border-slate-200 border-t-primary-600 animate-spin"
    :class="{
      'w-4 h-4': size === 'sm',
      'w-6 h-6': size === 'md',
      'w-10 h-10': size === 'lg'
    }"
  ></div>
</template>
