import axios, { AxiosInstance } from 'axios'
import rateLimit from 'axios-rate-limit'

// Настройка базового URL и глобальных параметров axios
const axiosInstance: AxiosInstance = axios.create({
    baseURL: 'http://localhost:8080/api/'
    // Другие глобальные настройки, например, заголовки
})

// Применение ограничения на частоту запросов (10 запросов в секунду)
const throttledInstance = rateLimit(axiosInstance as any, {
    maxRequests: 10,
    perMilliseconds: 1000
}) as AxiosInstance

export default throttledInstance
