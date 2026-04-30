import axios from 'axios'
import router from '@/router'
import { useNotificationStore } from '@/stores/notification.store'

const api = axios.create({
  baseURL: import.meta.env.PROD 
    ? 'https://bundau.onrender.com/api' 
    : (import.meta.env.VITE_API_URL || 'http://localhost:8000/api'),
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

// Helper to translate common Laravel error messages
function translateError(msg) {
  if (!msg) return 'Đã có lỗi xảy ra'
  
  let translated = msg.toLowerCase()
  
  // Map field names
  const fieldMap = {
    'email': 'Email',
    'password': 'Mật khẩu',
    'name': 'Tên',
    'price': 'Giá tiền',
    'description': 'Mô tả',
    'category': 'Phân loại',
    'quantity': 'Số lượng',
    'role': 'Chức vụ',
    'is_available': 'Trạng thái phục vụ'
  }

  // Common patterns
  if (translated.includes('is required') || translated.includes('field is required')) {
    const field = Object.keys(fieldMap).find(k => translated.includes(k))
    return `${fieldMap[field] || 'Trường này'} không được để trống.`
  }
  if (translated.includes('must be a number')) {
    const field = Object.keys(fieldMap).find(k => translated.includes(k))
    return `${fieldMap[field] || 'Trường này'} phải là một con số.`
  }
  if (translated.includes('already been taken')) {
    const field = Object.keys(fieldMap).find(k => translated.includes(k))
    return `${fieldMap[field] || 'Thông tin này'} đã tồn tại trong hệ thống.`
  }
  if (translated.includes('selected') && translated.includes('invalid')) {
    return 'Lựa chọn không hợp lệ.'
  }
  if (translated.includes('must be at least')) {
    return 'Giá trị quá nhỏ so với yêu cầu.';
  }
  if (translated.includes('must be a date after or equal to')) {
    return 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.';
  }
  if (translated.includes('must be a date before or equal to')) {
    return 'Ngày không được vượt quá thời điểm hiện tại.';
  }
  if (translated.includes('unauthenticated')) {
    return 'Phiên đăng nhập đã hết hạn.'
  }

  // Default translation for specific common backend messages
  const directMap = {
    'unauthorized': 'Bạn không có quyền thực hiện hành động này.',
    'forbidden': 'Truy cập bị từ chối.',
    'not found': 'Không tìm thấy dữ liệu yêu cầu.',
    'the given data was invalid.': 'Dữ liệu không hợp lệ, vui lòng kiểm tra lại.',
    'order already processed': 'Đơn hàng đã được xử lý ở một tab khác.',
    'order already has the status': 'Trạng thái đơn hàng đã được cập nhật trước đó.',
    'the order already has the status': 'Trạng thái đơn hàng đã được cập nhật trước đó.',
    'csrf token mismatch': 'Lỗi bảo mật (CSRF), vui lòng tải lại trang.',
    'network error': 'Lỗi kết nối mạng, vui lòng kiểm tra lại.',
    'timeout': 'Hết thời gian chờ phản hồi từ máy chủ.'
  }

  return directMap[translated] || msg
}

// Response interceptor: handle global errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status
    const notification = useNotificationStore()

    if (status === 401) {
      localStorage.removeItem('auth')
      if (router.currentRoute.value.name !== 'login' && router.currentRoute.value.name !== 'menu') {
        notification.error('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.')
        router.push('/login')
      }
    }

    if (status === 403) {
      notification.error('Bạn không có quyền truy cập khu vực này.')
      router.push('/403')
    }

    if (status === 422) {
      const errors = error.response?.data?.errors
      if (errors) {
        const firstError = Object.values(errors)[0][0]
        notification.error(translateError(firstError))
      } else {
        notification.error(translateError(error.response?.data?.message) || 'Dữ liệu không hợp lệ.')
      }
    } else if (status >= 500) {
      notification.error('Lỗi máy chủ, vui lòng thử lại sau.')
    } else if (status !== 401 && status !== 403) {
      // For other errors like 404, 400
      const msg = error.response?.data?.message || error.message
      notification.error(translateError(msg))
    }

    return Promise.reject(error)
  }
)

export default api
