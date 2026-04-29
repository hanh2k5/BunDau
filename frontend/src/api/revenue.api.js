import api from './axios'

export const revenueApi = {
  today() {
    return api.get('/revenue/today')
  },

  summary() {
    return api.get('/revenue/summary')
  },

  byDate(date) {
    return api.get('/revenue/by-date', { params: { date } })
  },

  byRange(from, to) {
    return api.get('/revenue/by-range', { params: { from, to } })
  },
}
