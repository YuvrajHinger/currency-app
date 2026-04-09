<template>
  <div class="text-center">
    <v-progress-circular
      indeterminate
      :size="size"
      :width="width"
      color="primary"
    ></v-progress-circular>
    <p v-if="text" class="mt-4">{{ text }}</p>
  </div>
</template>

<script setup>
defineProps({
  size: {
    type: Number,
    default: 48,
  },
  width: {
    type: Number,
    default: 4,
  },
  text: {
    type: String,
    default: 'Loading...',
  },
})
</script>

<style scoped>
</style>
