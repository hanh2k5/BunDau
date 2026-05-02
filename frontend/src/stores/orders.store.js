import { defineStore } from 'pinia'
import { ordersApi } from '@/api/orders.api'

export const useOrdersStore = defineStore('orders', {
  state: () => ({
    orders: [],
    pagination: null,
    loading: false,
    error: null,
  }),

  actions: {
    async fetchOrders(params = {}) {
      this.loading = true
      this.error = null
      try {
        const { data } = await ordersApi.list(params)
        this.orders = data.data
        this.pagination = data.meta || null
      } catch (err) {
        this.error = err.response?.data?.message || 'Lỗi tải danh sách đơn hàng'
        throw err
      } finally {
        this.loading = false
      }
    },

    async createOrder(payload) {
      const { data } = await ordersApi.create(payload)
      return data
    },

    async payOrder(id, paymentMethod = 'cash') {
      const { data } = await ordersApi.pay(id, paymentMethod)
      const index = this.orders.findIndex((o) => o.id === id)
      if (index !== -1) {
        this.orders[index] = data.data
      }
      return data
    },

    async addItemsToOrder(id, payload) {
      const { data } = await ordersApi.addItems(id, payload)
      const index = this.orders.findIndex((o) => o.id === id)
      if (index !== -1) {
        this.orders[index] = data.data
      }
      return data
    },

    async cancelOrder(id) {
      const { data } = await ordersApi.cancel(id)
      const index = this.orders.findIndex((o) => o.id === id)
      if (index !== -1) {
        this.orders[index] = data.data
      }
      return data
    },

    async deleteOrder(id) {
      await ordersApi.destroy(id)
      this.orders = this.orders.filter((o) => o.id !== id)
    },
  },
})
