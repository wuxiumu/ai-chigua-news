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
        <span class="user-edit-link" @click="showUserEditModal = true">
          {{ user.username || '未登录' }}
        </span>
        <el-button v-if="user.token" size="small" @click="logout" style="margin-left: 10px;">退出</el-button>
      </div>
    </el-header>
    <el-main>
      <router-view/>
    </el-main>
  </el-container>
  <el-dialog
      v-model="showUserEditModal"
      title="修改账号信息"
      width="400px"
      center
      :close-on-click-modal="false"
  >
    <el-form :model="userEditForm" :rules="userEditRules" ref="userEditRef" label-width="80px">
      <el-form-item label="新账号" prop="username">
        <el-input v-model="userEditForm.username" autocomplete="off" />
      </el-form-item>
      <el-form-item label="新密码" prop="password">
        <el-input type="password" v-model="userEditForm.password" autocomplete="off" show-password />
      </el-form-item>
      <el-form-item label="确认密码" prop="confirm">
        <el-input type="password" v-model="userEditForm.confirm" autocomplete="off" show-password />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="submitUserEdit">保存</el-button>
        <el-button @click="showUserEditModal = false">取消</el-button>
      </el-form-item>
    </el-form>
  </el-dialog>
  <AiHelpDialog
      v-model="showAiHelpModal"
      :infos="aiInfos"
      :payWx="engineer.payWx"
      :payAli="engineer.payAli"
      :payUrl="engineer.payUrl"
  />
</template>

<script setup>
import { ElMessage } from 'element-plus'
import { ref, reactive,computed, onMounted, onBeforeUnmount } from 'vue'
import { useUserStore } from '@/store/user'
import { useRouter } from 'vue-router'
import { User } from '@element-plus/icons-vue'
import { useSiteStore } from '@/store/site'
import getApi from '@/api/request' // 你的 axios 封装
import AiHelpDialog from './AiHelpDialog.vue'

const showAiHelpModal = ref(false)

const aiInfos = [
  { label: '工程师', value: 'Aric', copy: true },
  { label: '电话', value: '18903676153', copy: true },
  { label: '邮箱', value: 'wuxiumu@163.com', copy: true },
  { label: '微信', value: 'qingbao199101', copy: true },
  { label: '技能栈', value: 'Vue,Go,Java,PHP', copy: false },
  { label: '一句话介绍', value: '十年码农，AI应用实践派', copy: false },
  { label: '兴趣爱好', value: '骑行、摄影、看科技八卦', copy: false },
  { label: '个人宣言', value: '代码是桥梁，AI是未来', copy: false },
  { label: '星座', value: '摩羯座♑️', copy: false },
  { label: '座右铭', value: 'Stay hungry, stay foolish.', copy: false }
]

const engineer = {
  payWx: 'https://archive.biliimg.com/bfs/archive/6c98caa50579e4df2ce124c5c296fed953ef0a0a.jpg',
  payAli: 'https://archive.biliimg.com/bfs/archive/0978ae41412bf85354a38a67f4ae9423ff19b1e4.jpg',
  payUrl: 'https://pay.gua.hk/'
}


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
  { label: '配置设置', type: 'route', to: '/siteconfig' },
  { label: '需要帮助', type: 'action', action: () => showAiHelpModal.value = true },
  { label: '官网', type: 'link', href: 'https://wuxiumu.github.io/' }
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
  // 用户信息不存在
  if (!user.token) {
    router.push('/login')
  }
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

const showUserEditModal = ref(false)
const userEditRef = ref()
const userEditForm = reactive({
  username: user.username,
  password: '',
  confirm: ''
})
const userEditRules = {
  username: [
    { required: true, message: '账号名不能为空', trigger: 'blur' }
  ],
  password: [
    { required: true, message: '密码不能为空', trigger: 'blur' },
    { min: 6, message: '密码长度不能少于6位', trigger: 'blur' }
  ],
  confirm: [
    { required: true, message: '请确认密码', trigger: 'blur' },
    { validator: (rule, value) => value === userEditForm.password, message: '两次密码不一致', trigger: 'blur' }
  ]
}

function submitUserEdit() {
  userEditRef.value.validate(async valid => {
    if (!valid) return
    try {
      const res = await getApi().post('/api/password', {
        username: userEditForm.username,
        password: userEditForm.password
      })
      if (res.data.code === 0) {
        ElMessage.success('修改成功，请重新登录')
        user.logout()
        showUserEditModal.value = false
        router.push('/login')
      } else {
        ElMessage.error(res.data.msg || '修改失败')
      }
    } catch (e) {
      ElMessage.error('网络异常')
    }
  })
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