import axios, { AxiosInstance } from 'axios'
import rateLimit from 'axios-rate-limit'
import type { RateLimitedAxiosInstance } from 'axios-rate-limit'

// Настройка базового URL и глобальных параметров axios
const axiosInstance: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_URL
  // Другие глобальные настройки, например, заголовки
})

// Применение ограничения на частоту запросов (10 запросов в секунду)
const throttledInstance: RateLimitedAxiosInstance = rateLimit(axiosInstance, {
  maxRequests: 10,
  perMilliseconds: 1000
})
export default throttledInstance
