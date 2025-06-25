<script setup>
import { ElMessage } from 'element-plus'

const props = defineProps({ modelValue: Object })
const popularArticles = props.modelValue

if (!popularArticles) {
  ElMessage.error('【热门文章配置】数据未传入，组件无法工作。')
  throw new Error('热门文章配置未传入')
}
if (!Array.isArray(popularArticles.articles)) {
  ElMessage.warning('【热门文章配置】articles 数据异常，已自动重置为空数组。')
  popularArticles.articles = []
}

function addArticle() {
  try {
    if (!Array.isArray(popularArticles.articles)) {
      popularArticles.articles = []
      ElMessage.warning('【热门文章】列表异常，已自动修复！')
    }
    popularArticles.articles.push({
      id: Date.now().toString(36) + Math.random().toString(16).slice(2),
      title: '',
      excerpt: '',
      author: '',
      readTime: '',
      views: '',
      url: '',
      cover: ''
    })
  } catch (err) {
    ElMessage.error('添加文章失败，请检查数据结构！')
  }
}
function removeArticle(idx) {
  try {
    if (!Array.isArray(popularArticles.articles)) {
      ElMessage.error('删除失败，热门文章列表不存在。')
      return
    }
    if (idx < 0 || idx >= popularArticles.articles.length) {
      ElMessage.error('删除失败，索引越界！')
      return
    }
    popularArticles.articles.splice(idx, 1)
  } catch (err) {
    ElMessage.error('删除失败，请检查数据结构！')
  }
}
</script>

<template>
  <el-form :model="popularArticles" label-width="90px">
    <el-form-item label="区块标题">
      <el-input v-model="popularArticles.title" placeholder="如：热门文章" />
    </el-form-item>
    <el-form-item label="热门文章">
      <div v-if="Array.isArray(popularArticles.articles)">
        <div
            v-for="(row, $index) in popularArticles.articles"
            :key="row.id"
            class="article-row"
            style="display:flex;align-items:center;gap:8px;margin-bottom:6px;"
        >
          <el-input v-model="row.title" placeholder="标题" style="width:160px" />
          <el-input v-model="row.excerpt" placeholder="摘要" style="width:180px" />
          <el-input v-model="row.author" placeholder="作者" style="width:90px" />
          <el-input v-model="row.readTime" placeholder="阅读时间" style="width:90px" />
          <el-input v-model="row.views" placeholder="阅读数" style="width:90px" />
          <el-input v-model="row.url" placeholder="文章链接URL" style="width:160px" />
          <el-input v-model="row.cover" placeholder="预览图URL" style="width:140px" />
          <img referrerpolicy="no-referrer" v-if="row.cover" :src="row.cover" style="height:36px;" />
          <el-button type="danger" size="small" @click="removeArticle($index)">删除</el-button>
        </div>
      </div>
      <div
          v-else
          style="color:red;margin:12px 0;"
      >热门文章列表数据异常，已自动修复为[]，请重试！</div>
      <el-button size="small" style="margin-top:8px" @click="addArticle">添加文章</el-button>
    </el-form-item>
  </el-form>
</template>

<style scoped>
.article-row {
  transition: background 0.15s;
}
.article-row:hover {
  background: #f7f9fc;
}
</style>