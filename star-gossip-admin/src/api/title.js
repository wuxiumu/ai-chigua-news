import request from './request'
export function getTitles() { return request.get('/api/titles') }
export function generateArticle(id) { return request.post(`/api/title/${id}/generate`) }
export function delTitle(id) { return request.delete(`/api/title/${id}`) }