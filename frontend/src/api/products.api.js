import api from './axios'

export const productsApi = {
  list(params = {}) {
    return api.get('/products', { params })
  },

  show(id) {
    return api.get(`/products/${id}`)
  },

  create(data) {
    return api.post('/products', data)
  },

  update(id, data) {
    // If data is FormData (has image), we must use POST with _method override for Laravel to parse it correctly
    if (data instanceof FormData) {
      data.append('_method', 'PUT')
      return api.post(`/products/${id}`, data)
    }
    return api.put(`/products/${id}`, data)
  },

  toggle(id) {
    return api.patch(`/products/${id}/toggle`)
  },

  destroy(id) {
    return api.delete(`/products/${id}`)
  },
}
