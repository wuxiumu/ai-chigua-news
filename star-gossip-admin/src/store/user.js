// src/store/user.js
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUserStore = defineStore('user', () => {
    const token = ref(localStorage.getItem('token') || '')
    const username = ref(localStorage.getItem('username') || '')

    function setUser(data) {
        token.value = data.token
        username.value = data.username
        localStorage.setItem('token', data.token)
        localStorage.setItem('username', data.username || '')
    }

    function logout() {
        token.value = ''
        username.value = ''
        localStorage.removeItem('token')
        localStorage.removeItem('username')
    }

    return { token, username, setUser, logout }
})