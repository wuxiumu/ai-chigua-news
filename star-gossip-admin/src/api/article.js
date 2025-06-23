// src/api/article.js
import request from './request' // 注意路径！

export function getArticles(params) {
    return request.get('/api/articles', { params })
}

export function getArticle(id) {
    return request.get(`/api/article/${id}`)
}

export function saveArticle(data) {
    if (data.id) return request.put(`/api/article/${data.id}`, data)
    return request.post('/api/article', data)
}

export function updateArticle(id, data) {
    return request.put(`/api/article/${id}`, data)
}

export function delArticle(id) {
    return request.delete(`/api/article/${id}`)
}