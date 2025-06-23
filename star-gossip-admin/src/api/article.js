// src/api/article.js
import getApi from '@/api/request'

export function getArticles(params) {
    return getApi().get('/api/articles', { params })
}

export function getArticle(id) {
    return getApi().get(`/api/article/${id}`)
}

export function saveArticle(data) {
    if (data.id) return getApi().put(`/api/article/${data.id}`, data)
    return getApi().post('/api/article', data)
}

export function updateArticle(id, data) {
    return getApi().put(`/api/article/${id}`, data)
}

export function delArticle(id) {
    return getApi().delete(`/api/article/${id}`)
}