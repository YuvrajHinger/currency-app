<template>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="6" lg="4">
        <v-card class="pa-8">
          <v-card-title class="text-center text-h5 mb-6">
            Login
          </v-card-title>

          <ErrorAlert v-if="errorMessage" :message="errorMessage" />

          <v-form @submit.prevent="handleLogin">
            <v-text-field
              v-model="email"
              label="Email"
              type="email"
              required
              :rules="emailRules"
              class="mb-4"
              variant="outlined"
              prepend-inner-icon="mdi-email"
            ></v-text-field>

            <v-text-field
              v-model="password"
              label="Password"
              type="password"
              required
              :rules="passwordRules"
              class="mb-4"
              variant="outlined"
              prepend-inner-icon="mdi-lock"
            ></v-text-field>

            <v-btn
              block
              type="submit"
              color="primary"
              size="large"
              :loading="loading"
              :disabled="!isFormValid || loading"
            >
              Login
            </v-btn>
          </v-form>

          <v-divider class="my-4"></v-divider>

          <v-card-text class="text-center">
            Don't have an account?
            <router-link to="/register" class="text-decoration-none">
              Register here
            </router-link>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'
import ErrorAlert from '../components/ErrorAlert.vue'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

const emailRules = [
  (v) => !!v || 'Email is required',
  (v) => /.+@.+\..+/.test(v) || 'Email must be valid',
]

const passwordRules = [(v) => !!v || 'Password is required', (v) => v.length >= 6 || 'Password must be at least 6 characters']

const isFormValid = computed(() => {
  return (
    email.value &&
    password.value &&
    emailRules.every((rule) => rule(email.value) === true) &&
    passwordRules.every((rule) => rule(password.value) === true)
  )
})

const handleLogin = async () => {
  if (!isFormValid.value) return

  loading.value = true
  errorMessage.value = ''

  try {
    await authStore.login(email.value, password.value)
    router.push('/converter')
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      error.response?.data?.error ||
      'Login failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
</style>
