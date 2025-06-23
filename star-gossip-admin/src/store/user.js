// src/store/user.js
import { defineStore } from 'pinia'

export const useUserStore = defineStore('user', {
    state: () => ({
        token: '',
        username: '',
        avatar: '',
        email: ''
    }),
    getters: {
        isLogin: (state) => !!state.token,
        // username: (state) => state.username
    },
    actions: {
        setUser(payload = {}) {
            Object.keys(this.$state).forEach(key => {
                // 目前只存token和username
                if (key === 'token' || key === 'username') {
                    // 更新 state 中的对应字段
                    this.$state[key] = payload[key] || ''
                    localStorage.setItem(key, this.$state[key])
                }
            })
        },
        logout() {
            this.$state['token'] = ''
            this.$state['username'] = ''
            this.$state['avatar'] = ''
            this.$state['email'] = ''
            localStorage.setItem('token', '')
            localStorage.setItem('username', '')
        }
    },
    persist: true
})