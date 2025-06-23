<template>
  <el-container style="min-height:100vh">
    <el-header height="60px" class="topbar">
      <span class="site-title" @click="showSiteModal = true">{{ currentSiteTitle }}</span>
      <nav class="topbar-nav">
        <template v-for="item in navMenus" :key="item.label">
          <!-- 路由跳转 -->
          <router-link
              v-if="item.type === 'route'"
              :to="item.to"
              class="nav-link"
          >{{ item.label }}</router-link>
          <!-- 外链 -->
          <a
              v-else-if="item.type === 'link'"
              :href="item.href"
              target="_blank"
              rel="noopener"
              class="nav-link"
          >{{ item.label }}</a>
          <!-- 自定义函数 -->
          <a
              v-else-if="item.type === 'action'"
              href="javascript:void(0)"
              class="nav-link"
              @click="item.action"
          >{{ item.label }}</a>

          <!-- 站点弹窗 -->
          <el-dialog
              v-model="showSiteModal"
              title="所有可用站点"
              width="600px"
              center
              :close-on-click-modal="false"
          >
            <el-table :data="siteStore.siteList" border style="width:100%">
              <el-table-column prop="name" label="名称"/>
              <!-- 这里改成自定义内容 -->
              <el-table-column prop="api" label="API 地址">
                <template #default="{ row }">
                  <a
                      :href="row.api"
                      target="_blank"
                      rel="noopener"
                      style="color: #409EFF; text-decoration: underline; word-break: break-all;"
                      @click.stop
                  >{{ row.api }}</a>
                </template>
              </el-table-column>
              <el-table-column label="操作">
                <template #default="{row}">
                  <el-button
                      type="primary"
                      size="small"
                      @click="selectSite(row.key)"
                      :disabled="row.key === siteStore.currentSite?.key"
                  >切换</el-button>
                </template>
              </el-table-column>
            </el-table>
            <div style="margin-top:20px;text-align:center;">
              <el-button type="info" @click="showSiteModal = false">关闭</el-button>
            </div>
          </el-dialog>
        </template>
      </nav>
      <div class="topbar-info">
        <span>{{ nowStr }}</span>
        <span>{{ weekStr }}</span>
        <el-icon style="margin-left:12px"><User /></el-icon>
        <span>{{ user.username || '未登录' }}</span>
        <el-button v-if="user.token" size="small" @click="logout" style="margin-left: 10px;">退出</el-button>
      </div>
    </el-header>
    <el-main>
      <router-view/>
    </el-main>
  </el-container>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useUserStore } from '@/store/user'
import { useRouter } from 'vue-router'
import { User } from '@element-plus/icons-vue'
import { useSiteStore } from '@/store/site'

const siteStore = useSiteStore()
const showSiteModal = ref(false)

const currentSiteTitle = computed(() =>
    siteStore.currentSite
        ? `${siteStore.currentSite.name} (AI新闻管理后台)`
        : '请选择站点'
)

function selectSite(key) {
  const site = siteStore.siteList.find(s => s.key === key)
  if (site) siteStore.setSite(site)
  showSiteModal.value = false
}

const router = useRouter()

const user = useUserStore()
// 导航菜单数组：type支持 route/link/action
const navMenus = [
  { label: '文章管理', type: 'route', to: '/' },
  { label: '历史热点', type: 'route', to: '/titlehub' },
  { label: '标题管理', type: 'route', to: '/titles' },
  { label: 'AI帮助', type: 'action', action: () => alert('AI帮助弹窗！') },
  { label: '官网', type: 'link', href: 'https://yourdomain.com' }
]

// 实时时间+星期
const nowStr = ref('')
const weekStr = ref('')

function updateNow() {
  const now = new Date()
  const y = now.getFullYear()
  const m = String(now.getMonth() + 1).padStart(2, '0')
  const d = String(now.getDate()).padStart(2, '0')
  const H = String(now.getHours()).padStart(2, '0')
  const i = String(now.getMinutes()).padStart(2, '0')
  const s = String(now.getSeconds()).padStart(2, '0')
  nowStr.value = `${y}-${m}-${d} ${H}:${i}:${s}`
  const weekNames = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六']
  weekStr.value = weekNames[now.getDay()]
}

let timer = null
onMounted(() => {
  updateNow()
  timer = setInterval(updateNow, 1000)
})
onBeforeUnmount(() => {
  if (timer) clearInterval(timer)
})

function logout() {
  user.logout()
  router.push('/login')
}
</script>

<style scoped>
.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #2d8cf0;
  color: #fff;
  padding: 0 24px;
}
.topbar-info {
  display: flex;
  align-items: center;
  gap: 14px;
  font-size: 15px;
}
.topbar-nav {
  display: inline-block;
  margin-left: 32px;
}
.nav-link {
  color: #fff;
  margin-right: 18px;
  text-decoration: none;
  font-size: 15px;
  transition: opacity 0.2s;
}
.nav-link.router-link-exact-active {
  text-decoration: underline;
  font-weight: bold;
  opacity: 1;
}
.nav-link:hover {
  opacity: 0.8;
}
.site-title {
  cursor: pointer;
  font-weight: bold;
  padding: 4px 10px;
  border-radius: 4px;
  transition: background .2s;
}
.site-title:hover {
  background: #f2f2f2;
}
</style>