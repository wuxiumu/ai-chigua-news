window.onerror = function (message, source, lineno, colno, error) {
    alert('前端报错: ' + message + '\n' + source + ':' + lineno + ':' + colno);
    console.error('[全局错误]', message, source, lineno, colno, error);
};
window.addEventListener('unhandledrejection', function (e) {
    alert('Promise错误: ' + (e.reason && e.reason.stack ? e.reason.stack : e.reason));
    console.error('[Promise异常]', e);
});

import { createApp } from 'vue'
import App from './App.vue'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import mavonEditor from 'mavon-editor'
import 'mavon-editor/dist/css/index.css'
import { createPinia } from 'pinia'
import router from './router'

const app = createApp(App)
app.use(ElementPlus)
app.use(mavonEditor)
app.use(createPinia())
app.use(router)
app.mount('#app')