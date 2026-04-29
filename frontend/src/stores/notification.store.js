import { defineStore } from 'pinia'

let toastId = 0

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    toasts: [],
  }),

  actions: {
    /**
     * Show a toast notification.
     * @param {'success'|'error'|'warning'|'info'} type
     * @param {string} message
     * @param {number} duration - milliseconds
     */
    show(type, message, duration = 3000) {
      const id = ++toastId
      this.toasts.push({ id, type, message })

      setTimeout(() => {
        this.remove(id)
      }, duration)
    },

    success(message, duration) {
      this.show('success', message, duration)
    },

    error(message, duration = 5000) {
      this.show('error', message, duration)
    },

    warning(message, duration) {
      this.show('warning', message, duration)
    },

    info(message, duration) {
      this.show('info', message, duration)
    },

    danger(message, duration) {
      this.show('danger', message, duration)
    },

    remove(id) {
      this.toasts = this.toasts.filter((t) => t.id !== id)
    },
  },
})
