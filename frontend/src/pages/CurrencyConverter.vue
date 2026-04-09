<template>
  <v-container class="py-8">
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-card-title class="text-h4 font-weight-bold">
            <v-icon left>mdi-cash-multiple</v-icon>
            Currency Rates
          </v-card-title>

          <v-card-text>
            <!-- Alerts -->
            <ErrorAlert v-if="errorMessage" :message="errorMessage" />
            <SuccessAlert v-if="successMessage" :message="successMessage" />

            <!-- Currency Selection -->
            <v-row class="mt-2">
              <v-col cols="12" md="9">
                <v-autocomplete
                  v-model="selectedCurrencies"
                  label="Select up to 5 currencies"
                  :items="currencies"
                  item-title="code"
                  item-value="code"
                  variant="outlined"
                  multiple
                  chips
                  closable-chips
                  :loading="loadingCurrencies"
                  :rules="currencyRules"
                ></v-autocomplete>
              </v-col>

              <v-col cols="12" md="3">
                <v-btn
                  block
                  color="primary"
                  size="large"
                  @click="fetchRates"
                  :loading="loadingRates"
                  :disabled="!isSelectionValid || loadingRates"
                  class="mt-1"
                >
                  Get Rates
                </v-btn>
              </v-col>
            </v-row>

            <!-- Results -->
            <v-row v-if="ratesResult" class="mt-6">
              <v-col cols="12">
                <v-card class="pa-4">
                  <v-card-title>Rates</v-card-title>

                  <v-table>
                    <thead>
                      <tr>
                        <th class="text-center">Currency</th>
                        <th class="text-center">Rate</th>
                      </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in ratesResult" :key="item.currency">
                          <td>{{ item.currency }}</td>
                          <td>{{ item.rate }}</td>
                        </tr>
                    </tbody>
                  </v-table>
                </v-card>
              </v-col>
            </v-row>

            <!-- Loader -->
            <v-row v-if="loadingRates" class="mt-6">
              <v-col cols="12" class="text-center">
                <LoadingSpinner text="Fetching rates..." />
              </v-col>
            </v-row>

          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { currencyService } from '../services/currencyService'
import ErrorAlert from '../components/ErrorAlert.vue'
import SuccessAlert from '../components/SuccessAlert.vue'
import LoadingSpinner from '../components/LoadingSpinner.vue'

// State
const selectedCurrencies = ref([])
const currencies = ref([])
const ratesResult = ref(null)

const loadingCurrencies = ref(false)
const loadingRates = ref(false)

const errorMessage = ref('')
const successMessage = ref('')

// Validation Rules
const currencyRules = [
  (v) => (v && v.length > 0) || 'Select at least 1 currency',
  (v) => (v.length <= 5) || 'You can select up to 5 currencies only'
]

// Computed
const isSelectionValid = computed(() => {
  return (
    selectedCurrencies.value.length > 0 &&
    selectedCurrencies.value.length <= 5
  )
})

// Fetch currencies list
const fetchCurrencies = async () => {
  loadingCurrencies.value = true
  errorMessage.value = ''

  try {
    const response = await currencyService.getCurrencies()
    const data = response.currencies || response.data || {} 
    currencies.value = Object.keys(data).map((code) => ({
      code: code,
      name: data[code]
    }))    
  } catch (error) {
    errorMessage.value = error.message || 'Failed to load currencies'
  } finally {
    loadingCurrencies.value = false
  }
}

// Fetch rates
const fetchRates = async () => {
  if (!isSelectionValid.value) return

  loadingRates.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const result = await currencyService.getRates(selectedCurrencies.value)
    const data = result.data || result
    const quotes = data.quotes || {}
    const source = data.source || ''

    ratesResult.value = Object.keys(quotes).map((key) => {
      return {
        currency: key.replace(source, ''), // USDINR -> INR
        rate: quotes[key]
      }
    })
    successMessage.value = 'Rates fetched successfully!'
  } catch (error) {
    ratesResult.value = null
    errorMessage.value = error.message || 'Failed to fetch rates'
  } finally {
    loadingRates.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchCurrencies()
})
</script>

<style scoped>
</style>