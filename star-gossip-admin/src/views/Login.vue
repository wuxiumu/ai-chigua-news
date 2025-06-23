<template>
  <div class="login-bg">
    <el-card class="login-card" shadow="hover">
      <h2 style="text-align:center;">后台登录</h2>
      <el-form :model="form" :rules="rules" ref="formRef" @keyup.enter="submit">
        <el-form-item>
          <el-select v-model="selectedSiteKey" style="width: 100%;" placeholder="请选择站点">
            <el-option
                v-for="site in siteStore.siteList"
                :key="site.key"
                :label="site.name + ' (' + site.api + ')'"
                :value="site.key"
            />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-input v-model="tempApiUrl" placeholder="或输入临时站点 API 地址" />
          <el-button type="primary" style="margin-left:8px;" @click="addTempSite">添加</el-button>
        </el-form-item>
        <el-form-item prop="username">
          <el-input v-model="form.username" placeholder="用户名" prefix-icon="el-icon-user" />
        </el-form-item>
        <el-form-item prop="password">
          <el-input v-model="form.password" type="password" placeholder="密码" prefix-icon="el-icon-lock" />
        </el-form-item>
        <SliderVerify v-if="showSlider" @success="handleSliderSuccess" />
        <div v-if="showSlider" style="height:16px"></div>
        <el-form-item>
          <el-button type="primary" :loading="loading" style="width:100%;" @click="submit">登录</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'

import getApi from '@/api/request'
import { useSiteStore } from '@/store/site'
import { useUserStore } from '@/store/user'
import SliderVerify from '@/components/SliderVerify.vue'
const showSlider = ref(false)
const sliderVerified = ref(false)
function handleSliderSuccess() {
  sliderVerified.value = true
  showSlider.value = false
}
function resetSlider() {
  sliderVerified.value = false
  showSlider.value = true
}

const router = useRouter()
const siteStore = useSiteStore()
// const userStore = useUserStore()

// 初始化站点列表（只做一次）
if (!siteStore.siteList.length) {
  const envSites = JSON.parse(import.meta.env.VITE_SITE_LIST || '[]')
  siteStore.initSites(envSites)
}

// 当前选中站点key
const selectedSiteKey = ref('')  // 默认空，必须手动选
const tempApiUrl = ref('')

// 只保持一个当前站点
watch(selectedSiteKey, val => {
  if (val) {
    const site = siteStore.siteList.find(s => s.key === val)
    if (site) siteStore.setSite(site)
    // 不再自动 logout
    // 可选：弹窗提示
    ElMessage.info('站点已切换，如需登录请重新输入账号密码')
  } else {
    siteStore.setSite(null)
    // 不再自动 logout
  }
})

// 添加临时站点
function addTempSite() {
  const url = tempApiUrl.value.trim()
  if (!/^https?:\/\//.test(url)) {
    ElMessage.error('请输入合法 API 地址')
    return
  }
  const key = 'temp_' + Date.now()
  const name = '临时站点'
  const site = { key, name, api: url }
  siteStore.siteList.push(site)
  selectedSiteKey.value = key   // 触发 watch，currentSite 自动同步
  tempApiUrl.value = ''
  ElMessage.success('已切换到临时站点')
}

// 表单数据
const form = ref({
  username: '',
  password: ''
})
const formRef = ref(null)
const loading = ref(false)

// 表单校验规则
const rules = {
  username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
  password: [{ required: true, message: '请输入密码', trigger: 'blur' }]
}

// 登录
async function submit() {
  formRef.value.validate(async valid => {
    if (!valid) return
    if (!siteStore.currentSite || !siteStore.currentSite.api) {
      ElMessage.error('请选择站点或输入API')
      return
    }
    // 只要需要滑块验证且未通过
    if (showSlider.value && !sliderVerified.value) {
      ElMessage.error('请完成滑块验证')
      return
    }
    loading.value = true
    try {
      const res = await getApi().post('/api/login', form.value)
      if (res.data && res.data.token) {
        useUserStore().setUser({
          token: res.data.token,
          username: form.value.username,
          avatar: res.data.avatar || '',
          email: res.data.email || ''
        })
        ElMessage.success('登录成功')
        const redirect = router.currentRoute.value.query.redirect || '/'
        await router.push(redirect)
      } else {
        // 登录失败：需要滑块验证
        resetSlider()
        ElMessage.error(res.data?.msg || '用户名或密码错误')
      }
    } catch (error) {
      // 错误详细信息打印
      console.log(error)
      ElMessage.error('登录失败，请检查网络')
    } finally {
      loading.value = false
    }
  })
}
</script>

<style scoped>
.login-bg {
  min-height: 100vh;
  background: linear-gradient(120deg, #a6c1ee 0%, #fbc2eb 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}
.login-card {
  width: 380px;
  padding: 40px 20px 20px 20px;
}
</style>