// src/store/site.js
import { defineStore } from 'pinia'
export const useSiteStore = defineStore('site', {
    state: () => ({
        siteList: [],
        currentSite: null, // {key,name,api}
    }),
    actions: {
        initSites(sites) { this.siteList = sites },
        setSite(site) { this.currentSite = site }
    },
    persist: true
})