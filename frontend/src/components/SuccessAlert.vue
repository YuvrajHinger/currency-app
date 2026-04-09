<template>
  <v-alert v-if="message" type="success" closable class="mb-4">
    {{ message }}
  </v-alert>
</template>

<script setup>
defineProps({
  message: {
    type: String,
    default: '',
  },
})
</script>

<style scoped>
</style>
