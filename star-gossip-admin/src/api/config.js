// src/api/config.js
import request from './request'
export async function fetchSiteConfig() {
    const res = await request.get('/api/config')
    return res.data?.data || {}
}