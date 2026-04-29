import axios from 'axios'
import router from '@/router'
import { useNotificationStore } from '@/stores/notification.store'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
  },
  withCredentials: true,
})

// Request interceptor: inject Bearer token
api.interceptors.request.use((config) => {
  // Lazy import to avoid circular dependency
  const authData = JSON.parse(localStorage.getItem('auth') || '{}')
  const token = authData?.token
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Response interceptor: handle global errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status
    const notification = useNotificationStore()

    if (status === 401) {
      // Clear stored auth state
      localStorage.removeItem('auth')
      if (router.currentRoute.value.name !== 'login' && router.currentRoute.value.name !== 'menu') {
        notification.error('Phiên đăng nhập đã hết hạn hoặc bạn đã đăng xuất ở tab khác.')
        router.push('/login')
      }
    }

    if (status === 403) {
      notification.error('Bạn không có quyền thực hiện thao tác này.')
      router.push('/403')
    }

    if (status === 422) {
      const errors = error.response?.data?.errors
      if (errors) {
        const firstError = Object.values(errors)[0][0]
        notification.error(firstError)
      } else {
        notification.error(error.response?.data?.message || 'Dữ liệu không hợp lệ.')
      }
    }

    return Promise.reject(error)
  }
)

export default api
