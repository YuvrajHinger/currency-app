<template>
  <v-container class="py-8" fluid>
    <!-- Create Report -->
    <v-card class="mb-6">
      <v-card-title class="text-h4 font-weight-bold">
        <v-icon left>mdi-file-document-plus</v-icon>
        Create Report
      </v-card-title>

      <v-card-text>
        <ErrorAlert v-if="submissionError" :message="submissionError" />
        <SuccessAlert v-if="submissionSuccess" :message="submissionSuccess" />

        <v-form @submit.prevent="submitReport">
          <v-row>
            <!-- Currency Dropdown -->
            <v-col cols="12" md="4">
              <v-autocomplete
                v-model="reportForm.currency"
                label="Select Currency"
                :items="currencies"
                item-title="name"
                item-value="code"
                variant="outlined"
                :loading="loadingCurrencies"                
              />
            </v-col>

            <!-- Range + Interval Combo -->
            <v-col cols="12" md="6">
              <v-select
                v-model="reportForm.selectedOption"
                label="Select Report Type"
                :items="reportOptions"
                item-title="label"
                return-object
                variant="outlined"
                :rules="[(v) => !!v || 'Required']"
              />
            </v-col>

            <!-- Submit -->
            <v-col cols="12">
              <v-btn
                type="submit"
                color="primary"
                size="large"
                :loading="submittingReport"
              >
                Submit Report
              </v-btn>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
    </v-card>

    <!-- Reports Table -->
    <v-card>
      <v-card-title class="text-h4 font-weight-bold">
        <v-icon left>mdi-file-chart</v-icon>
        Reports
      </v-card-title>

      <v-card-text>
        <ErrorAlert v-if="loadError" :message="loadError" />

        <v-row v-if="loadingReports">
          <v-col class="text-center">
            <LoadingSpinner text="Loading reports..." />
          </v-col>
        </v-row>

        <template v-else>         

          <v-data-table
            :headers="headers"
            :items="filteredReports"
            :items-per-page="itemsPerPage"
          >
            <template #item.created_at="{ item }">
              {{ formatDateTime(item.created_at) }}
            </template>
            <template #item.status="{ item }">
              <v-chip :color="getStatusColor(item.status)" size="small">
                {{ item.status }}
              </v-chip>
            </template>

            <template #item.actions="{ item }">
              <v-btn icon="mdi-eye" @click="viewReportDetails(item)" />
            </template>
          </v-data-table>
        </template>
      </v-card-text>
    </v-card>

    <!-- Report Modal -->
    <v-dialog v-model="showReportDialog" max-width="900px">
      <v-card v-if="reportDetails">
        <v-card-title>
          {{ reportDetails.currency }} Report
        </v-card-title>

        <v-card-text>
          <LoadingSpinner v-if="loadingDetails" />

          <template v-else>
            <v-row>
              <v-col cols="6"><b>Range:</b> {{ reportDetails.range }}</v-col>
              <v-col cols="6"><b>Interval:</b> {{ reportDetails.interval }}</v-col>
              <v-col cols="6"><b>Status:</b> {{ reportDetails.status }}</v-col>
              <v-col cols="6"><b>Created:</b> {{ formatDate(reportDetails.created_at) }}</v-col>
            </v-row>

            <!-- Chart -->
            <canvas ref="chartCanvas" class="mt-6"></canvas>

            <!-- Data Table -->
            <v-table class="mt-6">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Rate</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in reportDetails.data" :key="row.id">
                  <td>{{ formatDate(row.date) }}</td>
                  <td>{{ row.rate }}</td>
                </tr>
              </tbody>
            </v-table>
          </template>
        </v-card-text>

        <v-card-actions>
          <v-spacer />
          <v-btn @click="showReportDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Chart from 'chart.js/auto'
import { reportService } from '../services/reportService'
import { currencyService } from '../services/currencyService'
import ErrorAlert from '../components/ErrorAlert.vue'
import SuccessAlert from '../components/SuccessAlert.vue'
import LoadingSpinner from '../components/LoadingSpinner.vue'

// Form
const reportForm = ref({
  currency: '',
  selectedOption: null,
})

const reportOptions = [
  { label: 'One Year - Monthly', range: '1y', interval: 'monthly' },
  { label: 'Six Months - Weekly', range: '6m', interval: 'weekly' },
  { label: 'One Month - Daily', range: '1m', interval: 'daily' },
]

// Currency list
const currencies = ref([])
const loadingCurrencies = ref(false)

// State
const reports = ref([])
const loadingReports = ref(false)
const loadError = ref('')
const submittingReport = ref(false)
const submissionError = ref('')
const submissionSuccess = ref('')
const itemsPerPage = ref(10)

// Modal
const showReportDialog = ref(false)
const reportDetails = ref(null)
const loadingDetails = ref(false)
let chart = null
const chartCanvas = ref(null)

// Headers
const headers = [
  { title: 'Currency', key: 'currency' , align: 'center'},
  { title: 'Range', key: 'range'  , align: 'center'},
  { title: 'Interval', key: 'interval'  , align: 'center'},
  { title: 'Status', key: 'status'  , align: 'center'},
  { title: 'Created', key: 'created_at'  , align: 'center'},
  { title: 'Actions', key: 'actions',  align: 'center'},
]

// Computed
const filteredReports = computed(() => {
  return reports.value  
})

// Fetch currencies
const fetchCurrencies = async () => {
  loadingCurrencies.value = true
  try {
    const res = await currencyService.getCurrencies()
    const data = res.currencies || res.data || {}

    currencies.value = Object.keys(data).map(code => ({
      code,
      name: `${code} - ${data[code]}`,
    }))
  } catch (e) {
    loadError.value = e.message
  } finally {
    loadingCurrencies.value = false
  }
}

// Fetch reports
const fetchReports = async () => {
  loadingReports.value = true
  try {
    const res = await reportService.getReports()
    reports.value = res
  } catch (e) {
    loadError.value = e.message
  } finally {
    loadingReports.value = false
  }
}

// Submit
const submitReport = async () => {
  if (!reportForm.value.currency || !reportForm.value.selectedOption) return

  submittingReport.value = true

  try {
    await reportService.submitReport({
      currency: reportForm.value.currency,
      range: reportForm.value.selectedOption.range,
      interval: reportForm.value.selectedOption.interval,
    })

    submissionSuccess.value = 'Report created!'
    reportForm.value = { currency: '', selectedOption: null }

    fetchReports()
  } catch (e) {
    submissionError.value = e.message
  } finally {
    submittingReport.value = false
  }
}

// Details
const viewReportDetails = async (report) => {
  showReportDialog.value = true
  loadingDetails.value = true

  try {
    const res = await reportService.getReportDetails(report.id)
    reportDetails.value = res

    setTimeout(initChart, 100)
  } finally {
    loadingDetails.value = false
  }
}

// Chart
const initChart = () => {
  if (!reportDetails.value?.data) return

  if (chart) chart.destroy()

  const ctx = chartCanvas.value.getContext('2d')

  chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: reportDetails.value.data.map(d => formatDate(d.date)),
      datasets: [
        {
          label: 'Rate',
          data: reportDetails.value.data.map(d => d.rate),
        },
      ],
    },
  })
}

// Utils
const formatDate = (d) => new Date(d).toLocaleDateString()
const formatDateTime = (d) => new Date(d).toLocaleString(undefined, {
  dateStyle: 'medium',
  timeStyle: 'short',
})

const getStatusColor = (s) => ({
  pending: 'warning',
  completed: 'success',
}[s] || 'grey')

// Init
onMounted(() => {
  fetchReports()
  fetchCurrencies()
})
</script>