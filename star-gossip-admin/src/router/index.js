import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '@/store/user'

// 引入布局和页面组件，避免重复懒加载，方便统一管理
import MainLayout from '@/layouts/MainLayout.vue'
import ArticleList from '@/views/ArticleList.vue'
import ArticleEdit from '@/views/ArticleEdit.vue'
import Login from '@/views/Login.vue'
import TitlePool from '@/views/TitlePool.vue'
import TitleHub from '@/views/TitleHub.vue'
import TitleHubEdit from '@/views/TitleHubEdit.vue'
import SiteConfig from '@/views/SiteConfig.vue'
const routes = [
    {
        path: '/',
        component: MainLayout,
        children: [
            { path: '', component: ArticleList },
            { path: 'article/:id', component: ArticleEdit },
            { path: 'titles', component: TitlePool }, // 新增
            { path: 'titlehub', name: 'TitleHub',component: TitleHub },
            { path: 'titlehub/edit', name: 'TitleHubEdit', component: TitleHubEdit },
            { path: 'siteconfig', name: 'SiteConfig', component: SiteConfig },
            // 其它业务页面，建议都放在这里，统一布局和权限管理
        ]
    },
    {
        path: '/login',
        component: Login
        // /login为唯一不需要鉴权的页面，其余页面建议走 layout 子路由，统一布局和权限
    }
    // 移除重复的顶级 /article/:id 路由，避免路由冲突和冗余
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// 全局前置守卫，控制权限访问
router.beforeEach((to, from, next) => {
    const user = useUserStore()
    if (to.meta.requiresAuth && !user.token) {
        // 需要鉴权但未登录，跳转登录页并记录重定向路径
        next({ path: '/login', query: { redirect: to.fullPath } })
    } else if (to.path === '/login' && user.token) {
        // 已登录访问登录页，自动跳转首页
        // next({ path: '/' })
    } else {
        next()
    }
})

export default router