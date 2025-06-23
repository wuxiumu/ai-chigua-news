// src/api/config.js
import getApi from '@/api/request'

export async function fetchSiteConfig() {
    const res = await getApi().get('/api/config')
    return res.data?.data || {}
}