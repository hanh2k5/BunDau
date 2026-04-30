import { defineStore } from 'pinia'
import { authApi } from '@/api/auth.api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token && !!state.user,
    isAdmin: (state) => state.user?.role === 'admin',
    isStaff: (state) => state.user?.role === 'staff',
    userName: (state) => state.user?.name || '',
    userRole: (state) => state.user?.role || '',
  },

  actions: {
    async login(credentials) {
      const { data } = await authApi.login(credentials)
      this.token = data.data.token
      this.user = data.data.user
      return data
    },

    async fetchMe() {
      try {
        const { data } = await authApi.me()
        this.user = data.data
        return data
      } catch {
        this.logout()
        throw new Error('Session expired')
      }
    },

    async logout() {
      try {
        if (this.token) {
          await authApi.logout()
        }
      } catch {
        // Ignore logout errors
      } finally {
        const { useCartStore } = await import('./cart.store')
        const cart = useCartStore()
        cart.clear()
        
        this.token = null
        this.user = null
      }
    },
  },

  persist: true,
})
