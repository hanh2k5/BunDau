<script setup>
defineProps({
  variant: { type: String, default: 'primary' }, // primary, secondary, danger, ghost, success
  size:    { type: String, default: 'md' },       // sm, md, lg
  loading: { type: Boolean, default: false },
  disabled:{ type: Boolean, default: false },
  block:   { type: Boolean, default: false },
})

// Pure inline styles per variant — no Tailwind custom colors needed
const variantStyle = {
  primary:   'background:#0071e3; color:#fff;',
  secondary: 'background:rgba(0,0,0,0.06); color:#1d1d1f;',
  danger:    'background:#ff3b30; color:#fff;',
  ghost:     'background:transparent; color:#0071e3;',
  success:   'background:#30d158; color:#fff;',
}

const sizeStyle = {
  sm: 'padding:6px 14px; font-size:13px;',
  md: 'padding:9px 20px; font-size:14px;',
  lg: 'padding:13px 28px; font-size:15px;',
}
</script>

<template>
  <button
    :style="`
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      font-family: inherit;
      font-weight: 500;
      letter-spacing: -0.01em;
      border-radius: 980px;
      border: none;
      cursor: pointer;
      transition: opacity 0.15s ease, transform 0.1s ease;
      white-space: nowrap;
      ${variantStyle[variant] || variantStyle.primary}
      ${sizeStyle[size] || sizeStyle.md}
      ${block ? 'width:100%;' : ''}
      ${(disabled || loading) ? 'opacity:0.5; cursor:not-allowed; pointer-events:none;' : ''}
    `"
    :disabled="disabled || loading"
  >
    <!-- Loading spinner -->
    <svg v-if="loading" style="width:16px; height:16px; animation: spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
      <circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
      <path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
    </svg>
    <slot />
  </button>
</template>

<style scoped>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
button:hover:not(:disabled) { opacity: 0.88; }
button:active:not(:disabled) { transform: scale(0.97); }
</style>
