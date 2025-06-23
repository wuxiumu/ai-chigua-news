import getApi from '@/api/request'
export function getTitles() { return getApi().get('/api/titles') }
export function generateArticle(id) { return getApi().post(`/api/title/${id}/generate`) }
export function delTitle(id) { return getApi().delete(`/api/title/${id}`) }