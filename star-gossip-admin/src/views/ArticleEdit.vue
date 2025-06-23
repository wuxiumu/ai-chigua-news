<template>
  <el-card>
    <el-form :model="form">
      <el-form-item label="标题"><el-input v-model="form.title" /></el-form-item>
      <el-form-item label="摘要"><el-input v-model="form.desc" /></el-form-item>
      <el-form-item label="标签">
        <el-select v-model="form.tags" multiple>
          <el-option v-for="tag in allTags" :key="tag" :label="tag" :value="tag" />
        </el-select>
      </el-form-item>
      <el-form-item label="封面"><el-input v-model="form.cover" /></el-form-item>
      <el-form-item label="正文">
        <el-input
            type="textarea"
            :autosize="{ minRows: 10, maxRows: 20 }"
            v-model="form.content"
            placeholder="请使用 Markdown 语法编辑内容"
        />
      </el-form-item>
      <el-button type="primary" @click="save">保存</el-button>
      <el-button @click="back">返回</el-button>
    </el-form>
  </el-card>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getArticle, saveArticle,updateArticle } from '@/api/article'
import { useRouter, useRoute } from 'vue-router'
import { fetchSiteConfig } from '@/api/config' // 新增这一行，需你在api/config.js实现

const router = useRouter(), route = useRoute()
const form = ref({
  title: '',
  desc: '',
  tags: [],
  cover: '',
  content: ''
})
const allTags = ref([])
const loading = ref(false)

onMounted(async () => {
  // 获取全局 tags 列表
  try {
    const config = await fetchSiteConfig()
    if (config.keywords) {
      allTags.value = config.keywords.split(',').map(t => t.trim()).filter(Boolean)
    }
  } catch (e) {
    allTags.value = []
  }

  if (route.params.id && route.params.id !== 'new') {
    loading.value = true
    try {
      const res = await getArticle(route.params.id)
      let data = res.data
      if ('code' in data && data.code === 0) data = data.data
      data = data || {}
      Object.assign(form.value, {
        title: data.meta?.title || '',
        desc: data.meta?.desc || '',
        tags: Array.isArray(data.meta?.tags) ? data.meta.tags : [],
        cover: data.meta?.cover || '',
        content: data.content_html || '' // 或 data.content，如果你更喜欢编辑markdown源码
      })
    } catch (e) {
      ElMessage.error('加载失败')
    } finally {
      loading.value = false
    }
  }
})

async function save() {
  try {
    // 编辑模式：有 id，发 updateArticle，否则发 saveArticle
    let res
    if (route.params.id && route.params.id !== 'new') {
      res = await updateArticle(route.params.id, form.value)
    } else {
      res = await saveArticle(form.value)
    }
    if (!res.data || (res.data.code && res.data.code !== 0)) {
      ElMessage.error(res.data?.msg || '保存失败')
      return
    }
    ElMessage.success('保存成功')
    router.push('/')
  } catch (e) {
    ElMessage.error('保存失败')
  }
}
function back() { router.back() }
</script>