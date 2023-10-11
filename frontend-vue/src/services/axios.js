import axios from 'axios'

const instance = axios.create({
    baseURL: 'http://localhost:8080'
})

// Здесь можно добавить interceptors, если нужно
// instance.interceptors.request.use(...)
// instance.interceptors.response.use(...)

export default instance
