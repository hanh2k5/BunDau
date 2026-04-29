import { defineStore } from 'pinia'
import { productsApi } from '@/api/products.api'

export const useProductsStore = defineStore('products', {
  state: () => ({
    products: [],
    loading: false,
    error: null,
  }),

  getters: {
    availableProducts: (state) => state.products.filter((p) => p.is_available),
    allProducts: (state) => state.products,
  },

  actions: {
    async fetchProducts(params = {}) {
      this.loading = true
      this.error = null
      try {
        const { data } = await productsApi.list(params)
        this.products = data.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Lỗi tải danh sách sản phẩm'
        throw err
      } finally {
        this.loading = false
      }
    },

    async createProduct(productData) {
      const { data } = await productsApi.create(productData)
      this.products.push(data.data)
      return data
    },

    async updateProduct(id, productData) {
      const { data } = await productsApi.update(id, productData)
      const index = this.products.findIndex((p) => p.id === id)
      if (index !== -1) {
        this.products[index] = data.data
      }
      return data
    },

    async toggleProduct(id) {
      const { data } = await productsApi.toggle(id)
      const index = this.products.findIndex((p) => p.id === id)
      if (index !== -1) {
        this.products[index] = data.data
      }
      return data
    },

    async deleteProduct(id) {
      await productsApi.destroy(id)
      this.products = this.products.filter((p) => p.id !== id)
    },
  },
})
