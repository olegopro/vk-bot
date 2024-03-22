import axios from 'axios'
import axiosThrottle from 'axios-request-throttle'

// Настройка базового URL и глобальных параметров axios
const axiosInstance = axios.create({
    baseURL: 'http://localhost:8080/api/'
    // Другие глобальные настройки, например, заголовки
})

// Применение ограничения на частоту запросов
axiosThrottle.use(axiosInstance, { requestsPerSecond: 10 })

export default axiosInstance
