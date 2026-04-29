<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
  },
})

const emit = defineEmits(['close'])

const sizeClasses = {
  sm: 'max-w-md',
  md: 'max-w-lg',
  lg: 'max-w-2xl',
}
</script>

<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
      >
        <!-- Backdrop -->
        <div
          class="absolute inset-0 bg-black/60 backdrop-blur-sm"
          @click="emit('close')"
        />

        <!-- Modal content -->
        <Transition
          appear
          enter-active-class="transition duration-400 ease-out"
          enter-from-class="transform scale-95 opacity-0 translate-y-4"
          enter-to-class="transform scale-100 opacity-100 translate-y-0"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="transform scale-100 opacity-100 translate-y-0"
          leave-to-class="transform scale-95 opacity-0 translate-y-4"
        >
          <div
            v-if="show"
            :class="['relative w-full flex flex-col bg-white rounded-[24px] sm:rounded-[32px] p-5 sm:p-8 shadow-[0_32px_64px_-12px_rgba(0,0,0,0.14)] border border-slate-200/60 max-h-[92vh]', sizeClasses[size]]"
          >
            <!-- Header -->
            <div v-if="title || $slots.header" class="flex-shrink-0 flex items-center justify-between mb-6">
              <slot name="header">
                <h3 class="text-[17px] font-bold text-slate-900 tracking-tight leading-tight">{{ title }}</h3>
              </slot>
              <button
                class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center hover:bg-slate-200 hover:text-slate-600 transition-all duration-200"
                @click="emit('close')"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Body -->
            <div class="text-slate-600 font-medium overflow-y-auto pr-1 flex-1 min-h-0 no-scrollbar">
              <slot />
            </div>

            <!-- Footer -->
            <div v-if="$slots.footer" class="flex-shrink-0 mt-7 flex justify-end gap-3">
              <slot name="footer" />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
