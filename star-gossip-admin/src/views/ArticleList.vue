<template>
  <el-card>
    <div style="margin-bottom:10px; display:flex; align-items:center; justify-content:space-between;">
      <div style="display:flex; gap:10px; align-items:center;">
        <el-input
          v-model="search"
          placeholder="搜索标题/内容"
          style="width:200px"
          clearable
        />
        <el-select
          v-model="selectedTag"
          clearable
          placeholder="标签筛选"
          style="width:140px"
        >
          <el-option
            v-for="tag in allTags"
            :key="tag"
            :label="tag"
            :value="tag"
          />
        </el-select>
        <el-button type="primary" @click="handleSearch">搜索</el-button>
      </div>
      <el-button type="primary" @click="goNew">新建文章</el-button>
    </div>
    <el-table
        :data="list"
        v-loading="loading"
        style="width:100%;min-height:200px"
        empty-text="暂无数据或加载失败"
    >
      <el-table-column prop="title" label="标题" min-width="180"/>
      <el-table-column
          prop="tags"
          label="标签"
          min-width="200"
      >
        <template #default="scope">
          <div class="tags-cell">
            <template v-if="Array.isArray(scope.row.tags)">
              <el-tag
                  v-for="(tag, i) in scope.row.tags"
                  :key="i"
                  class="tag-item"
                  disable-transitions
              >{{ tag }}</el-tag>
            </template>
            <span v-else>-</span>
          </div>
        </template>
      </el-table-column>
      <el-table-column prop="date" label="日期" min-width="160"/>
      <el-table-column label="操作" width="140">
        <template #default="scope">
          <el-button size="small" @click="goEdit(scope.row)">编辑</el-button>
          <el-button size="small" type="danger" @click="del(scope.row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
    <div style="margin-top:18px; text-align:right;">
      <el-pagination
          :current-page="page"
          :page-size="size"
          :total="total"
          layout="total, sizes, prev, pager, next, jumper"
          :page-sizes="[10, 20, 50]"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
      />
    </div>
    <el-empty v-if="!loading && list.length === 0" description="没有找到任何文章" />
  </el-card>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getArticles, delArticle } from '@/api/article'
import { fetchSiteConfig } from '@/api/config'
import { useRouter } from 'vue-router'

const list = ref([])
const total = ref(0)
const page = ref(1)
const size = ref(10)
const search = ref('')
const selectedTag = ref('')
const allTags = ref([])
const loading = ref(false)
const router = useRouter()

function objectToArray(obj) {
  if (Array.isArray(obj)) return obj
  return Object.entries(obj || {}).map(([key, item]) => ({
    id: item.id || key,
    ...item
  }))
}

async function fetchList() {
  loading.value = true
  try {
    const res = await getArticles({
      page: page.value,
      size: size.value,
      search: search.value,
      tag: selectedTag.value
    })
    if (res.data && res.data.code === 0) {
      const data = res.data.data || {}
      // 支持对象格式和数组格式
      list.value = Array.isArray(data.list) ? data.list : objectToArray(data.list)
      total.value = data.total || 0
    } else {
      list.value = []
      total.value = 0
      ElMessage.warning(res.data?.msg || '获取数据失败')
    }
  } catch (e) {
    list.value = []
    total.value = 0
    ElMessage.error('接口请求失败！')
  } finally {
    loading.value = false
  }
}

function handleSizeChange(val) {
  size.value = val
  page.value = 1
  fetchList()
}
function handleCurrentChange(val) {
  page.value = val
  fetchList()
}
function handleSearch() {
  page.value = 1
  fetchList()
}
function handleTag() {
  page.value = 1
  fetchList()
}

function goNew() { router.push('/article/new') }
function goEdit(row) { router.push('/article/' + row.id) }
async function del(row) {
  try {
    await delArticle(row.id)
    ElMessage.success('删除成功')
    fetchList()
  } catch (e) {
    ElMessage.error('删除失败')
  }
}

async function loadTags() {
  try {
    const config = await fetchSiteConfig()
    if (config.keywords) {
      allTags.value = config.keywords.split(',').map(t => t.trim()).filter(Boolean)
    }
  } catch {
    allTags.value = []
  }
}

onMounted(() => {
  loadTags()
  fetchList()
})
</script>

<style scoped>
.tags-cell {
  max-width: 220px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: flex;
  flex-wrap: nowrap;
  gap: 4px;
}
.tag-item {
  max-width: 60px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>