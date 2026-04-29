import api from './axios'

export const ordersApi = {
  list(params = {}) {
    return api.get('/orders', { params })
  },

  show(id) {
    return api.get(`/orders/${id}`)
  },

  create(data) {
    return api.post('/orders', data)
  },

  pay(id) {
    return api.patch(`/orders/${id}/pay`)
  },

  cancel(id) {
    return api.patch(`/orders/${id}/cancel`)
  },

  destroy(id) {
    return api.delete(`/orders/${id}`)
  },
}
