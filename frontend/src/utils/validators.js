/**
 * Validate email format.
 * @param {string} email
 * @returns {boolean}
 */
export function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

/**
 * Validate minimum string length.
 * @param {string} value
 * @param {number} min
 * @returns {boolean}
 */
export function minLength(value, min) {
  return value && value.length >= min
}

/**
 * Validate a value is not empty.
 * @param {*} value
 * @returns {boolean}
 */
export function isRequired(value) {
  if (typeof value === 'string') return value.trim().length > 0
  if (Array.isArray(value)) return value.length > 0
  return value != null
}

/**
 * Validate a number is non-negative.
 * @param {number} value
 * @returns {boolean}
 */
export function isNonNegative(value) {
  return typeof value === 'number' && value >= 0
}
