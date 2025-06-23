<template>
  <el-card>
    <!-- 文件管理区：直接显示当前文件名和快捷操作 -->
    <div style="display:flex;gap:10px;align-items:center;margin-bottom:18px;">
      <el-date-picker
          v-model="curFile"
          type="date"
          format="YYYY-MM-DD"
          value-format="YYYY-MM-DD"
          :disabled-date="disabledFuture"
          placeholder="选择日期"
          style="width: 180px"
          @change="onDateChange"
      />
      <el-button @click="gotoPrevDay">前一天</el-button>
      <el-button @click="gotoNextDay" :disabled="isToday(curFile)">后一天</el-button>
      <el-button @click="goCalendar" type="primary">返回日历</el-button>
      <el-button @click="fetchFiles">刷新</el-button>
      <el-button v-if="curFile" type="danger" @click="deleteFile">删除文件</el-button>
    </div>

    <!-- 文件内容区 -->
    <div v-if="curFile">
      <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;">
        <el-input v-model="newTitle" placeholder="新增标题" style="width:240px" @keyup.enter="addTitle"/>
        <el-button type="primary" @click="addTitle">添加标题</el-button>
        <el-button @click="fetchList">刷新</el-button>
      </div>
      <el-table :data="list" v-loading="loading" style="width:100%">
        <el-table-column prop="title" label="标题" min-width="220">
          <template #default="scope">
            <el-input
                v-if="editRow === scope.$index"
                v-model="editTitle"
                size="small"
                style="width:90%"
                @keyup.enter="saveEdit(scope.row, scope.$index)"
                @blur="saveEdit(scope.row, scope.$index)"
            />
            <span v-else>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100">
          <template #default="scope">
            <el-tag :type="scope.row.status==='已生成'?'success':scope.row.status==='失败'?'danger':'info'">
              {{ scope.row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="gen_at" label="生成时间" width="160"/>
        <el-table-column label="操作" width="250">
          <template #default="scope">
            <el-button size="small" @click="aiGen(scope.row, scope.$index)">AI生成</el-button>
            <el-button size="small" @click="startEdit(scope.row, scope.$index)">编辑</el-button>
            <el-button size="small" type="danger" @click="delTitle(scope.row, scope.$index)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <el-empty v-else description="请先选择一个标题池文件" />

    <!-- AI生成内容弹窗 -->
    <el-dialog v-model="showContent" width="60%">
      <template #header>生成内容预览</template>
      <pre style="white-space: pre-wrap; font-family: inherit;">{{ content }}</pre>
    </el-dialog>

    <!-- 重命名弹窗 -->
    <el-dialog v-model="renameFileDialog" width="340px" title="重命名文件">
      <el-input v-model="renameTo" placeholder="请输入新文件名，如 newname" />
      <template #footer>
        <el-button @click="renameFileDialog = false">取消</el-button>
        <el-button type="primary" @click="renameFile">确认重命名</el-button>
      </template>
    </el-dialog>
  </el-card>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import getApi from '@/api/request'
import { ElMessage, ElMessageBox } from 'element-plus'
import dayjs from 'dayjs'

const request = getApi()

// 跳转回日历页
function goCalendar() {
  router.push({ name: 'TitleHub' })
}

function disabledFuture(date) {
  return date.getTime() > new Date().setHours(23,59,59,999)
}
function isToday(date) {
  return dayjs(date).isSame(dayjs(), 'day')
}

// 切换日期
function onDateChange(val) {
  if (!val) return
  if (val > dayjs().format('YYYY-MM-DD')) return // 不允许未来
  router.push({ name: 'TitleHubEdit', query: { date: val } })
}

// 前一天
async function gotoPrevDay() {
  if (!curFile.value) return
  const prevDay = dayjs(curFile.value).subtract(1, 'day').format('YYYY-MM-DD')
  await gotoFileOrFetch(prevDay)
}
// 后一天
async function gotoNextDay() {
  if (!curFile.value) return
  const nextDay = dayjs(curFile.value).add(1, 'day').format('YYYY-MM-DD')
  if (dayjs(nextDay).isAfter(dayjs(), 'day')) return // 不允许未来
  await gotoFileOrFetch(nextDay)
}

// 跳转或采集
async function gotoFileOrFetch(ymd) {
  if (dayjs(ymd).isAfter(dayjs(), 'day')) return
  try {
    const res = await request.get('/api/titlehub/files')
    const files = (res.data?.data || []).map(f => f.replace(/\.md$/, ''))
    if (files.includes(ymd)) {
      router.push({ name: 'TitleHubEdit', query: { date: ymd } })
    } else {
      // 没有则采集
      const r = await request.get('/api/titlehub/fetch?date=' + ymd)
      if (r.data.code === 0) {
        ElMessage.success('采集成功')
        router.push({ name: 'TitleHubEdit', query: { date: ymd } })
      } else {
        ElMessage.error(r.data.msg || '采集失败')
      }
    }
  } catch (err) {
    ElMessage.error('跳转失败：' + (err?.response?.data?.msg || err.message || '未知错误'))
    console.error('gotoFileOrFetch error:', err)
  }
}

const route = useRoute()
const router = useRouter()
const fileList = ref([])
const curFile = ref('')
const newFileName = ref('')
const renameFileDialog = ref(false)
const renameTo = ref('')

const list = ref([])
const loading = ref(false)
const newTitle = ref('')
const showContent = ref(false)
const content = ref('')
const editRow = ref(-1)
const editTitle = ref('')

// 自动同步 query.date 到 curFile
onMounted(() => {
  const ymd = route.query.date
  if (ymd) curFile.value = ymd
  fetchFiles()
})
watch(() => route.query.date, val => {
  if (val) {
    curFile.value = val
    fetchList()
  }
})

/** ★ 文件操作区 */
async function fetchFiles() {
  try {
    loading.value = true
    const res = await request.get('/api/titlehub/files')
    fileList.value = (res.data?.data || []).map(f => f.replace(/\.md$/, ''))
    // 不自动切 curFile，直接用 query.date
    await fetchList()
  } catch (err) {
    fileList.value = []
    curFile.value = ''
    list.value = []
    ElMessage.error('文件列表加载失败，请检查服务端日志')
    console.error('fetchFiles error:', err)
  } finally {
    loading.value = false
  }
}

async function deleteFile() {
  if (!curFile.value) return
  ElMessageBox.confirm(`确定要删除文件【${curFile.value}】？此操作不可恢复。`, '警告', { type: 'warning' })
      .then(async () => {
        try {
          await request.delete(`/api/titlehub/file/${curFile.value}`)
          ElMessage.success('删除成功')
          curFile.value = ''
          // 跳回上级（日历页）
          router.push({ name: 'TitleHubCalendar' })
        } catch (err) {
          ElMessage.error('删除失败：' + (err?.response?.data?.msg || err.message || '未知错误'))
          console.error('deleteFile error:', err)
        }
      })
}

async function renameFile() {
  if (!curFile.value || !renameTo.value.trim()) return
  try {
    await request.put(`/api/titlehub/file/${curFile.value}`, { newname: renameTo.value.trim() })
    ElMessage.success('重命名成功')
    // 跳转新文件
    router.replace({ name: 'TitleHubEdit', query: { date: renameTo.value.trim() } })
    renameFileDialog.value = false
    renameTo.value = ''
    await fetchFiles()
  } catch (err) {
    ElMessage.error('重命名失败：' + (err?.response?.data?.msg || err.message || '未知错误'))
    console.error('renameFile error:', err)
  }
}

/** ★ 标题行操作区 */
async function fetchList() {
  if (!curFile.value) { list.value = []; return }
  loading.value = true
  try {
    const res = await request.get(`/api/titlehub/file/${curFile.value}`)
    if (res.data && res.data.code === 0) {
      list.value = Array.isArray(res.data.data) ? res.data.data : []
    } else {
      list.value = []
      ElMessage.warning('加载内容失败：' + (res.data?.msg || '未知错误'))
    }
  } catch (err) {
    list.value = []
    ElMessage.error('加载内容失败：' + (err?.response?.data?.msg || err.message || '未知错误'))
    console.error('fetchList error:', err)
  } finally {
    loading.value = false
  }
}
async function addTitle() {
  if (!newTitle.value.trim()) return
  try {
    await request.post(`/api/titlehub/file/${curFile.value}/line`, { title: newTitle.value })
    newTitle.value = ''
    await fetchList()
  } catch (err) {
    ElMessage.error('添加标题失败：' + (err?.response?.data?.msg || err.message || '未知错误'))
    console.error('addTitle error:', err)
  }
}
async function delTitle(row, idx) {
  try {
    await request.delete(`/api/titlehub/file/${curFile.value}/line/${row.line_no}`)
    await fetchList()
  } catch (err) {
    ElMessage.error('删除标题失败：' + (err?.response?.data?.msg || err.message || '未知错误'))
    console.error('delTitle error:', err)
  }
}
function startEdit(row, idx) {
  editRow.value = idx
  editTitle.value = row.title
}
async function saveEdit(row, idx) {
  if (editTitle.value.trim() && editTitle.value !== row.title) {
    try {
      await request.put(`/api/titlehub/file/${curFile.value}/line/${row.line_no}`, { title: editTitle.value })
      await fetchList()
    } catch (err) {
      ElMessage.error('编辑失败：' + (err?.response?.data?.msg || err.message || '未知错误'))
      console.error('saveEdit error:', err)
    }
  }
  editRow.value = -1
}
async function aiGen(row, idx) {
  loading.value = true
  try {
    const res = await request.post(`/api/titlehub/file/${curFile.value}/line/${row.line_no}/generate`)
    if (res.data && res.data.code === 0) {
      content.value = res.data.data.content
      showContent.value = true
      await fetchList()
    } else {
      ElMessage.error('AI生成失败：' + (res.data?.msg || '未知错误'))
      console.error('aiGen failed:', res)
    }
  } catch (err) {
    ElMessage.error('AI生成失败：' + (err?.response?.data?.msg || err.message || '未知错误'))
    console.error('aiGen error:', err)
  } finally {
    loading.value = false
  }
}
</script>