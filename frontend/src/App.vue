<template>
  <v-app>
    <!-- App Bar -->
    <v-app-bar app color="primary" dark>
      <!-- ✅ Hamburger -->
      <v-app-bar-nav-icon @click="drawer = !drawer" />

      <v-app-bar-title>Currency Conversion Rates</v-app-bar-title>

      <v-spacer></v-spacer>

      <!-- User Menu -->
      <template v-if="authStore.isAuthenticated">
        <v-menu>
          <template #activator="{ props }">
            <v-btn icon v-bind="props">
              <v-icon>mdi-account</v-icon>
            </v-btn>
          </template>

          <v-list>            
            <v-divider></v-divider>

            <v-list-item @click="handleLogout">
              <v-list-item-title>Logout</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-app-bar>

    <!-- Sidebar -->
    <v-navigation-drawer
      v-if="authStore.isAuthenticated"
      v-model="drawer"
      app
      :temporary="display.smAndDown.value"
      :permanent="display.mdAndUp.value"
    >
      <v-list nav>
        <v-list-item
          v-for="item in menuItems"
          :key="item.to"
          :to="item.to"
          :active="$route.path === item.to"
          link
          @click="handleMenuClick"
        >
          <template #prepend>
            <v-icon>{{ item.icon }}</v-icon>
          </template>

          <v-list-item-title>
            {{ item.title }}
          </v-list-item-title>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <!-- Main -->
    <v-main>
      <RouterView />
    </v-main>
  </v-app>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useDisplay } from 'vuetify'
import { useAuthStore } from './stores/authStore'

const router = useRouter()
const authStore = useAuthStore()
const display = useDisplay()

const drawer = ref(false)

// Menu items
const menuItems = [
  {
    title: 'Currency Converter',
    to: '/converter',
    icon: 'mdi-cash-multiple',
  },
  {
    title: 'Reports',
    to: '/reports',
    icon: 'mdi-file-chart',
  },
]

// Logout
const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}

// Auto close drawer on mobile
const handleMenuClick = () => {
  if (display.smAndDown.value) {
    drawer.value = false
  }
}
</script>

<style scoped>
</style>