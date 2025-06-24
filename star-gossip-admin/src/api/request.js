// src/api/request.js
import axios from 'axios'
import { useSiteStore } from '@/store/site'
import { useUserStore } from '@/store/user'

const getApi = () => {
    const siteStore = useSiteStore()
    const userStore = useUserStore()
    const baseURL = siteStore.currentSite?.api || ''
    if (!baseURL) {
        // 防呆：未选中站点直接抛错
        throw new Error('未选择站点或站点API未配置，请先选择站点')
    }
    const token = userStore.token || ''
    const instance = axios.create({ baseURL, timeout: 10000 })

    // 请求拦截
    instance.interceptors.request.use(config => {
        if (token) {
            config.headers.Authorization = 'Bearer ' + token
        }
        return config
    })

    // 响应拦截（可选：统一处理未授权/超时/全局错误）
    instance.interceptors.response.use(
        res => res,
        err => {
            if (err?.response?.status === 401) {
                // 自动登出
                userStore.logout()
                // 可加全局提示
                if (typeof window !== 'undefined') {
                    // alert('登录已失效，请重新登录')
                }
                // 可以加跳转到登录页逻辑
            }
            // 其它错误直接抛出
            return Promise.reject(err)
        }
    )

    return instance
}
export default getApi