import apiClient from './apiClient'

export const reportService = {
  submitReport: async (reportData) => {
    try {
      const response = await apiClient.post('/reports', reportData)
      return response.data
    } catch (error) {
      throw {
        status: error.response?.status,
        message: error.response?.data?.message || 'Failed to submit report',
        data: error.response?.data,
      }
    }
  },

  getReports: async (page = 1, limit = 10) => {
    try {
      const response = await apiClient.get('/reports', {
        params: {
          page,
          limit,
        },
      })
      return response.data
    } catch (error) {
      throw {
        status: error.response?.status,
        message: error.response?.data?.message || 'Failed to fetch reports',
        data: error.response?.data,
      }
    }
  },

  getReportDetails: async (reportId) => {
    try {
      const response = await apiClient.get(`/reports/${reportId}`)
      return response.data
    } catch (error) {
      throw {
        status: error.response?.status,
        message:
          error.response?.status === 403
            ? 'You do not have permission to view this report'
            : error.response?.status === 404
              ? 'Report not found'
              : error.response?.data?.message || 'Failed to fetch report details',
        data: error.response?.data,
      }
    }
  },
}
