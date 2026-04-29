/**
 * Format a number as Vietnamese currency (VNĐ).
 * @param {number} amount
 * @returns {string}
 */
export function formatCurrency(amount) {
  if (amount == null) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

/**
 * Format an ISO date string to Vietnamese-friendly format (DD/MM/YYYY HH:mm).
 * @param {string} dateStr
 * @returns {string}
 */
export function formatDate(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  
  const d = String(date.getDate()).padStart(2, '0')
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const y = date.getFullYear()
  const hh = String(date.getHours()).padStart(2, '0')
  const mm = String(date.getMinutes()).padStart(2, '0')
  
  return `${d}/${m}/${y} ${hh}:${mm}`
}

/**
 * Format a short date (DD/MM/YYYY).
 * @param {string} dateStr
 * @returns {string}
 */
export function formatShortDate(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  
  const d = String(date.getDate()).padStart(2, '0')
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const y = date.getFullYear()
  
  return `${d}/${m}/${y}`
}

/**
 * Get status label in Vietnamese.
 * @param {string} status
 * @returns {string}
 */
export function getStatusLabel(status) {
  const labels = {
    pending: 'Chờ thanh toán',
    done: 'Đã thanh toán',
    cancelled: 'Đã hủy',
  }
  return labels[status] || status
}

/**
 * Get status color class.
 * @param {string} status
 * @returns {string}
 */
export function getStatusColor(status) {
  const colors = {
    pending: 'text-amber-400 bg-amber-400/10 border-amber-400/30',
    done: 'text-emerald-400 bg-emerald-400/10 border-emerald-400/30',
    cancelled: 'text-red-400 bg-red-400/10 border-red-400/30',
  }
  return colors[status] || 'text-gray-400 bg-gray-400/10'
}

/**
 * Remove Vietnamese accents/diacritics from a string for searching.
 * @param {string} str
 * @returns {string}
 */
export function removeAccents(str) {
  if (!str) return ''
  return str
    .toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd')
    .replace(/Đ/g, 'D')
}
