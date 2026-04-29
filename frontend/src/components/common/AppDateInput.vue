<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: String,
  label: String
})

const emit = defineEmits(['update:modelValue'])

// Format internal YYYY-MM-DD to display DD/MM/YYYY
const displayValue = computed(() => {
  if (!props.modelValue) return ''
  const [y, m, d] = props.modelValue.split('-')
  return `${d}/${m}/${y}`
})

function onDateChange(e) {
  emit('update:modelValue', e.target.value)
}
</script>

<template>
  <div class="flex flex-col gap-1 min-w-[160px]">
    <label v-if="label" class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">{{ label }}</label>
    <div class="relative group">
      <!-- The visible formatted text -->
      <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
        <span class="text-sm font-bold text-slate-700">{{ displayValue || 'Chọn ngày...' }}</span>
      </div>
      
      <!-- The hidden-but-clickable native picker -->
      <input
        type="date"
        :value="modelValue"
        @input="onDateChange"
        class="bg-white/80 border border-white/90 rounded-2xl pl-4 pr-10 py-2.5 w-full text-transparent focus:outline-none focus:border-primary-400 focus:ring-4 focus:ring-primary-500/10 transition-all cursor-pointer relative z-20 appearance-none shadow-sm"
      />
      
      <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none z-30">
        <svg class="h-4 w-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Hide the default date text but keep the picker functionality */
input[type="date"]::-webkit-datetime-edit,
input[type="date"]::-webkit-inner-spin-button,
input[type="date"]::-webkit-clear-button {
  display: none;
}

input[type="date"]::-webkit-calendar-picker-indicator {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: auto;
  height: auto;
  color: transparent;
  background: transparent;
  cursor: pointer;
}
</style>
