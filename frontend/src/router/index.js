import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'

// Lazy-loaded page components
const LoginPage = () => import('@/pages/auth/LoginPage.vue')
const MenuPage = () => import('@/pages/staff/MenuPage.vue')
const CartPage = () => import('@/pages/staff/CartPage.vue')
const ProductsPage = () => import('@/pages/admin/ProductsPage.vue')
const OrdersPage = () => import('@/pages/admin/OrdersPage.vue')
const RevenuePage = () => import('@/pages/admin/RevenuePage.vue')
const StaffPage = () => import('@/pages/admin/StaffPage.vue')
const NotFound = () => import('@/pages/errors/NotFound.vue')
const Forbidden = () => import('@/pages/errors/Forbidden.vue')
const AppLayout = () => import('@/components/layout/AppLayout.vue')

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginPage,
    meta: { guest: true },
  },
  {
    path: '/',
    component: AppLayout,
    children: [
      {
        path: '',
        name: 'home',
        component: MenuPage,
      },
      {
        path: 'menu',
        name: 'menu',
        component: MenuPage,
      },
      {
        path: 'cart',
        name: 'cart',
        component: CartPage,
        meta: { requiresAuth: true, roles: ['admin', 'staff'] },
      },
      {
        path: 'products',
        name: 'products',
        component: ProductsPage,
        meta: { requiresAuth: true, roles: ['admin'] },
      },
      {
        path: 'orders',
        name: 'orders',
        component: OrdersPage,
        meta: { requiresAuth: true, roles: ['admin', 'staff'] },
      },
      {
        path: 'revenue',
        name: 'revenue',
        component: RevenuePage,
        meta: { requiresAuth: true, roles: ['admin'] },
      },
      {
        path: 'staff',
        name: 'staff',
        component: StaffPage,
        meta: { requiresAuth: true, roles: ['admin'] },
      },
    ],
  },
  {
    path: '/404',
    name: 'not-found',
    component: NotFound,
  },
  {
    path: '/403',
    name: 'forbidden',
    component: Forbidden,
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/404',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guards
router.beforeEach((to) => {
  const auth = useAuthStore()

  // Redirect authenticated users away from login
  if (to.meta.guest && auth.isLoggedIn) {
    return { name: 'menu' }
  }

  // Redirect unauthenticated users to login
  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return { name: 'login' }
  }

  // Check role-based access
  if (to.meta.roles && !to.meta.roles.includes(auth.user?.role)) {
    return { path: '/403' }
  }
})

export default router
