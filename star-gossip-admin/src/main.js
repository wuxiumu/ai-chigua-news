// window.onerror = function (message, source, lineno, colno, error) {
//     console.error('[全局错误]', message, source, lineno, colno, error);
// };
// window.addEventListener('unhandledrejection', function (e) {
//     console.error('[Promise异常]', e);
// });

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import App from './App.vue'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import mavonEditor from 'mavon-editor'
import 'mavon-editor/dist/css/index.css'
import router from './router'
import { useSiteStore } from '@/store/site'

const app = createApp(App)
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)
app.use(ElementPlus)
app.use(mavonEditor)
app.use(pinia)
app.use(router)

// 兜底：站点未选时自动跳转到登录页并提示
const siteStore = useSiteStore()
if (!siteStore.currentSite || !siteStore.currentSite.api) {
  if (typeof window !== 'undefined') {
    // alert('请先选择站点！')
    // window.location.href = '/login'
  }
}

app.mount('#app')