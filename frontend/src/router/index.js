import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/authStore'
import Login from '../pages/Login.vue'
import Register from '../pages/Register.vue'
import CurrencyConverter from '../pages/CurrencyConverter.vue'
import Reports from '../pages/Reports.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresAuth: false },
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { requiresAuth: false },
  },
  {
    path: '/converter',
    name: 'CurrencyConverter',
    component: CurrencyConverter,
    meta: { requiresAuth: true },
  },
  {
    path: '/reports',
    name: 'Reports',
    component: Reports,
    meta: { requiresAuth: true },
  },
  {
    path: '/',
    redirect: '/converter',
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/converter',
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// Global navigation guard for authentication
router.beforeEach((to, from) => {
  const authStore = useAuthStore()
  const requiresAuth = to.meta.requiresAuth

  // If route requires auth and user is not logged in
  if (requiresAuth && !authStore.isAuthenticated) {
    return '/login'
  }

  // Prevent logged-in users from going to login page
  if (!requiresAuth && authStore.isAuthenticated && to.path === '/login') {
    return '/converter'
  }

  // Ensure token is set (optional but good)
  authStore.initializeAuth()

  // Allow navigation
  return true
})

export default router
