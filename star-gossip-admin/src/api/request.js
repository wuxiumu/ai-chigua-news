// src/api/request.js
import axios from 'axios'
import router from '@/router'           // 路由实例，根据实际路径调整
import { useUserStore } from '@/store/user'

const instance = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '/api',
    timeout: 10000
})

// 请求拦截器，自动加 token
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

// 响应拦截器，自动处理未授权
instance.interceptors.response.use(
    response => {
        // 支持后端直接返回 code=401 的情况
        if (response.data && response.data.code === 401) {
            handleUnauthorized()
            return Promise.reject(new Error('未授权，请重新登录'))
        }
        return response
    },
    error => {
        // 支持后端 http 401 状态码
        if (error.response && error.response.status === 401) {
            handleUnauthorized()
        }
        return Promise.reject(error)
    }
)

// 未授权处理逻辑
function handleUnauthorized() {
    const user = useUserStore()
    if (user.logout) {
        user.logout()
    } else {
        user.token = ''
    }
    // 跳转登录页（避免重复跳转，防死循环）
    if (router.currentRoute.value.path !== '/login') {
        router.replace({ path: '/login' })
    }
}

export default instance