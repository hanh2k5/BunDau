import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
  }),

  getters: {
    totalItems: (state) => state.items.reduce((acc, item) => acc + item.quantity, 0),
    totalAmount: (state) => state.items.reduce((acc, item) => acc + item.price * item.quantity, 0),
    isEmpty: (state) => state.items.length === 0,
    itemCount: (state) => state.items.length,
  },

  actions: {
    addItem(product) {
      const existing = this.items.find((i) => i.product_id === product.id)
      if (existing) {
        // Update image_url if it's missing (for old cart items)
        if (!existing.image_url && product.image_url) {
          existing.image_url = product.image_url
        }
        if (existing.quantity < 99) {
          existing.quantity++
        }
      } else {
        this.items.push({
          product_id: product.id,
          name: product.name,
          price: product.price,
          image_url: product.image_url,
          quantity: 1,
        })
      }
    },

    removeItem(productId) {
      this.items = this.items.filter((i) => i.product_id !== productId)
    },

    updateQuantity(productId, quantity) {
      const item = this.items.find((i) => i.product_id === productId)
      if (item) {
        if (quantity <= 0) {
          this.removeItem(productId)
        } else if (quantity <= 99) {
          item.quantity = quantity
        }
      }
    },

    incrementQuantity(productId) {
      const item = this.items.find((i) => i.product_id === productId)
      if (item && item.quantity < 99) {
        item.quantity++
      }
    },

    decrementQuantity(productId) {
      const item = this.items.find((i) => i.product_id === productId)
      if (item) {
        if (item.quantity <= 1) {
          this.removeItem(productId)
        } else {
          item.quantity--
        }
      }
    },

    clear() {
      this.items = []
    },

    setItems(items) {
      this.items = items.map(item => ({
        product_id: item.product_id,
        name: item.product_name,
        price: item.price,
        image_url: item.product_image_url,
        quantity: item.quantity
      }))
    },

    /**
     * Format items for API submission.
     */
    toApiPayload(note = null) {
      return {
        items: this.items.map((item) => ({
          product_id: item.product_id,
          quantity: item.quantity,
        })),
        note,
      }
    },
  },

  persist: true,
})
