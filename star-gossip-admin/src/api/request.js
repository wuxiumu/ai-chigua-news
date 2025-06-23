// src/api/request.js
// import axios from '@/utils/axios'
import axios from 'axios'
import { useUserStore } from '@/store/user'

const instance = axios.create({
    // baseURL: 'http://localhost:8080', // 可有可无
    baseURL: import.meta.env.VITE_API_URL,
    timeout: 10000
})

instance.interceptors.request.use(
    config => {
        const user = useUserStore()
        if (user.token) {
            config.headers['Authorization'] = 'Bearer ' + user.token
        }
        return config
    },
    error => Promise.reject(error)
)

export default instance