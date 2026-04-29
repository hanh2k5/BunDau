import api from './axios'

export const usersApi = {
  // Get all users
  getAll() {
    return api.get('/users')
  },

  // Create a new user
  create(data) {
    return api.post('/users', data)
  },

  // Update a user
  update(id, data) {
    return api.put(`/users/${id}`, data)
  },

  // Delete a user
  delete(id) {
    return api.delete(`/users/${id}`)
  }
}
