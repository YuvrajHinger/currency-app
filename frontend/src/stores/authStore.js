import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '../services/apiClient'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('auth_token') || null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)

  const setToken = (newToken) => {
    token.value = newToken
    if (newToken) {
      localStorage.setItem('auth_token', newToken)
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
    } else {
      localStorage.removeItem('auth_token')
      delete apiClient.defaults.headers.common['Authorization']
    }
  }

  const register = async (name, email, password, passwordConfirmation) => {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.post('/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      })
      setToken(response.data.token)      
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const login = async (email, password) => {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.post('/login', {
        email,
        password,
      })
      setToken(response.data.token)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const logout = () => {
    setToken(null)
    error.value = null
  }

  // Initialize auth state from localStorage
  const initializeAuth = () => {
    if (token.value) {
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
    }
  }

  return {
    token,
    loading,
    error,
    isAuthenticated,
    setToken,
    register,
    login,
    logout,
    initializeAuth,
  }
})
