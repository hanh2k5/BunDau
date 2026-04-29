<script setup>
defineProps({
  label: {
    type: String,
    required: true,
  },
  variant: {
    type: String,
    default: 'default', // default, success, warning, danger
  },
})

const variantClasses = {
  default: 'text-gray-300 bg-gray-500/20 border-gray-500/30',
  success: 'text-emerald-400 bg-emerald-400/10 border-emerald-400/30',
  warning: 'text-amber-400 bg-amber-400/10 border-amber-400/30',
  danger: 'text-red-400 bg-red-400/10 border-red-400/30',
  info: 'text-sky-400 bg-sky-400/10 border-sky-400/30',
}
</script>

<template>
  <span
    :class="[
      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border',
      variantClasses[variant],
    ]"
  >
    {{ label }}
  </span>
</template>
