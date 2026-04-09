import apiClient from './apiClient'

export const currencyService = {
  getCurrencies: async () => {
    try {
      const response = await apiClient.get('/currencies')
      return response.data
    } catch (error) {
      throw {
        status: error.response?.status,
        message: error.response?.data?.message || 'Failed to fetch currencies',
        data: error.response?.data,
      }
    }
  },

  getRates: async (currencies) => {
    try {
      const response = await apiClient.post('/rates', {
        currencies: currencies
      })
      return response.data
    } catch (error) {
      throw {
        status: error.response?.status,
        message: error.response?.data?.message || 'Failed to fetch conversion rates',
        data: error.response?.data,
      }
    }
  },
}
