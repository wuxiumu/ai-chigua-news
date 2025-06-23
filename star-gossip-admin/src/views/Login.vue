<template>
  <div class="login-bg">
    <el-card class="login-card" shadow="hover">
      <h2 style="text-align:center;">后台登录</h2>
      <el-form :model="form" :rules="rules" ref="formRef" @keyup.enter="submit">
        <el-form-item prop="username">
          <el-input v-model="form.username" placeholder="用户名" prefix-icon="el-icon-user" />
        </el-form-item>
        <el-form-item prop="password">
          <el-input v-model="form.password" type="password" placeholder="密码" prefix-icon="el-icon-lock" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :loading="loading" style="width:100%;" @click="submit">登录</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'

import request from '@/api/request'

import { useUserStore } from '@/store/user'

const router = useRouter()
const user = useUserStore()

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

/**
 * 提交登录表单
 * 1. 校验表单
 * 2. 调用登录接口
 * 3. 登录成功后存储用户信息并跳转
 * 4. 登录失败显示错误信息
 */
async function submit() {
  formRef.value.validate(async valid => {
    if (!valid) return

    loading.value = true
    try {
      const res = await request.post('/api/login', form.value)
      if (res.data && res.data.token) {
        // 存储用户信息（token 和用户名）
        user.setUser({
          token: res.data.token,
          username: form.value.username
        })
        ElMessage.success('登录成功')

        // 跳转到 redirect 指定页面或首页
        const redirect = router.currentRoute.value.query.redirect || '/'
        await router.push(redirect)
      } else {
        // 后端返回错误信息时显示
        ElMessage.error(res.data?.msg || '用户名或密码错误')
      }
    } catch (error) {
      // 网络或服务器错误提示
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
  width: 350px;
  padding: 40px 20px 20px 20px;
}
</style>