<script setup>
import { onMounted, ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { revenueApi } from '@/api/revenue.api'
import { formatCurrency, formatShortDate } from '@/utils/format'
import AppLoading from '@/components/common/AppLoading.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import AppDateInput from '@/components/common/AppDateInput.vue'

const router = useRouter()
const loading = ref(true)
const summary = ref(null)
const todayData = ref(null)
const rangeData = ref(null)

const fromDate = ref('')
const toDate = ref('')
const rangeLoading = ref(false)
const activeRange = ref('')

watch([fromDate, toDate], () => {
  // If dates change, we assume it's custom unless it matches a preset
  // For simplicity, we just clear the active range when date inputs are used manually
  // We will let setPresetRange set the activeRange directly.
})

// Pagination for daily breakdown
const currentPage = ref(1)
const itemsPerPage = 7

const paginatedDaily = computed(() => {
  if (!rangeData.value?.daily) return []
  const start = (currentPage.value - 1) * itemsPerPage
  return rangeData.value.daily.slice(start, start + itemsPerPage)
})

const totalPages = computed(() => {
  if (!rangeData.value?.daily) return 1
  return Math.ceil(rangeData.value.daily.length / itemsPerPage) || 1
})

onMounted(async () => {
  try {
    const [summaryRes, todayRes] = await Promise.all([
      revenueApi.summary(),
      revenueApi.today(),
    ])
    summary.value = summaryRes.data.data
    todayData.value = todayRes.data.data
  } catch {
    // handled by interceptor
  } finally {
    loading.value = false
  }
})

async function fetchRange() {
  if (!fromDate.value || !toDate.value) return

  rangeLoading.value = true
  currentPage.value = 1 // Reset pagination on new search
  try {
    const { data } = await revenueApi.byRange(fromDate.value, toDate.value)
    rangeData.value = data.data
  } catch {
    // handled by interceptor
  } finally {
    rangeLoading.value = false
  }
}

function setPresetRange(type) {
  activeRange.value = type
  // Use sv-SE locale to get YYYY-MM-DD in local time
  const today = new Date().toLocaleDateString('sv-SE')
  
  if (type === 'today') {
    fromDate.value = today
    toDate.value = today
  } else if (type === 'yesterday') {
    const yesterdayDate = new Date()
    yesterdayDate.setDate(yesterdayDate.getDate() - 1)
    fromDate.value = yesterdayDate.toLocaleDateString('sv-SE')
    toDate.value = yesterdayDate.toLocaleDateString('sv-SE')
  } else if (type === 'last7') {
    const last7Date = new Date()
    last7Date.setDate(last7Date.getDate() - 7)
    fromDate.value = last7Date.toLocaleDateString('sv-SE')
    toDate.value = today
  } else if (type === 'month') {
    const last30 = new Date()
    last30.setDate(last30.getDate() - 30)
    fromDate.value = last30.toLocaleDateString('sv-SE')
    toDate.value = today
  } else if (type === 'all') {
    fromDate.value = '2026-04-30'
    toDate.value = today
  }
  
  fetchRange()
}
</script>

<template>
  <div>
    <!-- Page header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-slate-800 tracking-tight">📊 Thống kê doanh thu</h2>
      <p class="text-sm text-slate-500 mt-1 font-medium">Tổng hợp doanh thu quán</p>
    </div>

    <AppLoading v-if="loading" size="lg" />

    <div v-else class="space-y-6">
      <!-- Stats: Apple style -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <button 
          @click="setPresetRange('today')"
          class="stat-card group text-left transition-all hover:scale-[1.02] active:scale-[0.98] hover:shadow-xl hover:shadow-primary-500/10 border-transparent hover:border-primary-100"
        >
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 group-hover:text-primary-500 transition-colors">Hôm nay</p>
          <p class="font-black text-2xl text-slate-900 tracking-tight mb-2">
            {{ formatCurrency(todayData?.total_revenue || 0) }}
          </p>
          <div class="flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-[11px] font-bold text-emerald-600">{{ todayData?.total_orders || 0 }} đơn thành công</span>
          </div>
        </button>

        <div class="stat-card">
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Tổng thu</p>
          <p class="font-black text-2xl text-slate-900 tracking-tight mb-2">
            {{ formatCurrency(summary?.all_time?.total_revenue || 0) }}
          </p>
          <p class="text-[11px] font-bold text-slate-500">{{ summary?.all_time?.total_orders || 0 }} đơn từ trước đến nay</p>
        </div>

        <div class="stat-card">
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Trung bình</p>
          <p class="font-black text-2xl text-slate-900 tracking-tight mb-2">
            {{ formatCurrency(summary?.all_time?.average_per_order || 0) }}
          </p>
          <p class="text-[11px] font-bold text-slate-500">Mỗi đơn hàng thành công</p>
        </div>

        <button 
          @click="router.push({ name: 'orders', query: { status: 'pending' } })"
          class="stat-card group text-left transition-all hover:scale-[1.02] active:scale-[0.98] hover:shadow-xl hover:shadow-orange-500/10 border-transparent hover:border-orange-100"
        >
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 group-hover:text-orange-500 transition-colors">Đang chờ</p>
          <p class="font-black text-3xl text-orange-500 tracking-tight mb-2">
            {{ summary?.pending_orders || 0 }}
          </p>
          <p class="text-[11px] font-bold text-slate-500">Đơn hàng chưa hoàn thành</p>
        </button>
      </div>

      <!-- Date range filter -->
      <div class="glass p-5 sm:p-8 rounded-[2.5rem] border-white/40 shadow-2xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
          <div>
            <h3 class="text-lg font-black text-slate-800 tracking-widest uppercase">🔍 Phân tích dữ liệu</h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">Chọn khoảng thời gian để xem chi tiết</p>
          </div>
          <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1 -mx-2 px-2 sm:mx-0 sm:px-0">
            <button 
              v-for="preset in [
                { id: 'today', label: 'Hôm nay' },
                { id: 'yesterday', label: 'Hôm qua' },
                { id: 'last7', label: '7 ngày' },
                { id: 'month', label: 'Tháng' },
                { id: 'all', label: 'Từ trước đến nay' }
              ]"
              :key="preset.id"
              @click="setPresetRange(preset.id)"
              class="flex-shrink-0 px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-sm active:scale-95"
              :class="activeRange === preset.id
                ? 'bg-primary-600 text-white shadow-md shadow-primary-500/20'
                : 'bg-white text-slate-600 border border-slate-200 hover:border-primary-400 hover:text-primary-600 hover:bg-slate-50'"
            >
              {{ preset.label }}
            </button>
          </div>
        </div>
        <div class="flex flex-wrap items-end gap-4">
          <AppDateInput
            v-model="fromDate"
            label="Từ ngày"
            @change="activeRange = ''"
          />
          <AppDateInput
            v-model="toDate"
            label="Đến ngày"
            @change="activeRange = ''"
          />
          <button
            class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-bold transition-all cursor-pointer disabled:opacity-50 shadow-md shadow-primary-500/20"
            :disabled="!fromDate || !toDate || rangeLoading"
            @click="fetchRange"
          >
            {{ rangeLoading ? 'Đang tải...' : 'Xem doanh thu' }}
          </button>
        </div>

        <!-- Range results -->
        <div v-if="rangeData" class="mt-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white/60 rounded-xl p-4 border border-white/80 shadow-sm">
              <span class="text-sm font-semibold text-slate-500">Tổng doanh thu</span>
              <p class="text-xl font-bold text-primary-600 mt-1">
                {{ formatCurrency(rangeData.total_revenue) }}
              </p>
            </div>
            <div class="bg-white/60 rounded-xl p-4 border border-white/80 shadow-sm">
              <span class="text-sm font-semibold text-slate-500">Số đơn</span>
              <p class="text-xl font-bold text-emerald-600 mt-1">
                {{ rangeData.total_orders }}
              </p>
            </div>
            <div class="bg-white/60 rounded-xl p-4 border border-white/80 shadow-sm">
              <span class="text-sm font-semibold text-slate-500">TB / đơn</span>
              <p class="text-xl font-bold text-accent-600 mt-1">
                {{ formatCurrency(rangeData.average_per_order) }}
              </p>
            </div>
          </div>

          <!-- Daily breakdown -->
          <div v-if="rangeData.daily?.length" class="space-y-2">
            <h4 class="text-sm font-bold text-slate-600 mb-2">Chi tiết theo ngày</h4>
            <div
              v-for="day in paginatedDaily"
              :key="day.date"
              @click="router.push({ name: 'orders', query: { date: day.date } })"
              class="flex items-center justify-between bg-white/60 rounded-xl px-4 py-3 border border-white/80 shadow-sm hover:border-primary-400 hover:bg-white transition-all cursor-pointer group"
              title="Bấm để xem danh sách đơn hàng ngày này"
            >
              <span class="text-sm font-semibold text-slate-700 group-hover:text-primary-600">{{ formatShortDate(day.date) }}</span>
              <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-slate-500">{{ day.total_orders }} đơn</span>
                <span class="text-sm font-bold text-primary-600 flex items-center gap-1">
                  {{ formatCurrency(day.total_revenue) }}
                  <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </span>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="mt-4 glass rounded-2xl p-2 shadow-sm">
              <AppPagination
                v-model:currentPage="currentPage"
                :total-pages="totalPages"
                :total-items="rangeData.daily.length"
                :items-per-page="itemsPerPage"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
