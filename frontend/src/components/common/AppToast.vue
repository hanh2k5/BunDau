<script setup>
import { useNotificationStore } from '@/stores/notification.store'

const notification = useNotificationStore()

const iconMap = {
  success: '✅',
  error: '❌',
  warning: '⚠️',
  info: 'ℹ️',
  danger: '🗑️',
}

const colorMap = {
  success: 'border-emerald-200 bg-emerald-50 shadow-emerald-500/20 text-emerald-800',
  error: 'border-red-200 bg-red-50 shadow-red-500/20 text-red-800',
  warning: 'border-amber-200 bg-amber-50 shadow-amber-500/20 text-amber-800',
  info: 'border-sky-200 bg-sky-50 shadow-sky-500/20 text-sky-800',
  danger: 'border-red-200 bg-red-50 shadow-red-500/20 text-red-800',
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[100] flex flex-col gap-3 max-w-sm">
      <TransitionGroup name="toast">
        <div
          v-for="toast in notification.toasts"
          :key="toast.id"
          :class="[
            'flex items-start gap-3 p-4 rounded-xl border backdrop-blur-xl shadow-xl',
            colorMap[toast.type],
          ]"
        >
          <span class="text-lg shrink-0 drop-shadow-sm">{{ iconMap[toast.type] }}</span>
          <p class="text-sm font-semibold flex-1">{{ toast.message }}</p>
          <button
            class="shrink-0 text-slate-400 hover:text-slate-700 transition-colors cursor-pointer"
            @click="notification.remove(toast.id)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active {
  animation: toast-in 0.3s ease-out;
}
.toast-leave-active {
  animation: toast-out 0.3s ease-in forwards;
}
@keyframes toast-in {
  from { opacity: 0; transform: translateX(100%); }
  to   { opacity: 1; transform: translateX(0); }
}
@keyframes toast-out {
  from { opacity: 1; transform: translateX(0); }
  to   { opacity: 0; transform: translateX(100%); }
}
</style>
