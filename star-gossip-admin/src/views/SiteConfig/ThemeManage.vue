<template>
  <el-card>
    <div style="margin-bottom:18px;font-size:18px;font-weight:bold;">
      多主题管理
      <el-button type="primary" size="small" @click="addTheme" style="float:right;">新建主题</el-button>
    </div>
    <draggable
        v-model="themeList"
        item-key="order"
        animation="200"
        handle=".drag-handle"
        style="display: flex; flex-wrap: wrap; gap: 18px; min-height: 90px;"
        @end="fixOrder"
    >
      <template #item="{ element: theme, index }">
        <el-card :key="theme.order" shadow="hover" style="width:260px;position:relative;">
          <span class="drag-handle" style="cursor:move;position:absolute;left:10px;top:10px;font-size:18px;">☰</span>
          <div style="text-align:center;margin-bottom:10px;">
            <img referrerpolicy="no-referrer" v-if="theme.preview" :src="theme.preview" alt="预览" style="height:48px;max-width:100%;border-radius:6px;box-shadow:0 2px 8px #eee"/>
            <div v-else style="color:#aaa;height:48px;line-height:48px;">无预览图</div>
          </div>
          <el-form :model="theme" label-width="60px" size="small">
            <el-form-item label="主题名">
              <el-input v-model="theme.name" />
            </el-form-item>
            <el-form-item label="作者">
              <el-input v-model="theme.author" />
            </el-form-item>
            <el-form-item label="描述">
              <el-input v-model="theme.description" />
            </el-form-item>
            <el-form-item label="版本">
              <el-input v-model="theme.version" />
            </el-form-item>
            <el-form-item label="主题路径">
              <el-input v-model="theme.theme_path" />
            </el-form-item>
            <el-form-item label="预览图">
              <el-input v-model="theme.preview" placeholder="图片URL" />
            </el-form-item>
            <el-form-item label="使用">
              <el-switch
                v-model="theme.is_used"
                :active-value="1"
                :inactive-value="0"
                @change="() => setOnlyUsed(index)"
              /> 启用
            </el-form-item>
            <el-form-item>
              <el-button type="danger" size="small" @click="removeTheme(index)" :disabled="theme.is_used===1">删除</el-button>
            </el-form-item>
          </el-form>
        </el-card>
      </template>
    </draggable>
    <div style="margin-top:28px;text-align:center;">
      <el-button type="primary" @click="saveAll" :loading="saving">保存全部</el-button>
      <el-button @click="reload" :disabled="saving">重置</el-button>
    </div>
  </el-card>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import { ElMessage } from 'element-plus'
import getApi from '@/api/request'
import draggable from 'vuedraggable'

const themeList = ref([])
const saving = ref(false)

// 读取数据，接口数据是 {code:0, data:[...]}
async function reload() {
  try {
    const res = await getApi().get('/api/site-theme')
    if (Array.isArray(res.data.data)) {
      themeList.value = res.data.data.map((t, idx) => ({ ...t, order: idx + 1 }))
      // 确保只有一个主题is_used==1，没有则设置第一个为启用
      let usedCount = themeList.value.filter(t => t.is_used == 1).length
      if (usedCount !== 1 && themeList.value.length) {
        themeList.value.forEach((t, idx) => t.is_used = idx === 0 ? 1 : 0)
      }
    } else {
      themeList.value = []
    }
    ElMessage.success('已加载最新主题')
  } catch {
    ElMessage.error('主题加载失败')
  }
}
reload()

// 拖拽后修正order字段
function fixOrder() {
  themeList.value.forEach((t, idx) => t.order = idx + 1)
}

// 只允许一个主题启用
function setOnlyUsed(idx) {
  themeList.value.forEach((t, i) => { t.is_used = i === idx ? 1 : 0 })
}

// 新建主题
function addTheme() {
  themeList.value.push({
    order: themeList.value.length + 1,
    name: '',
    description: '',
    author: '',
    theme_path: '',
    version: '',
    preview: '',
    is_used: 0
  })
  // 自动滚动到新卡片
  nextTick(() => {
    // 默认新建主题不启用
  })
}

// 删除时不能删当前启用项
function removeTheme(idx) {
  if (themeList.value[idx].is_used === 1) {
    ElMessage.warning('不能删除当前启用主题，请切换后再删')
    return
  }
  themeList.value.splice(idx, 1)
  fixOrder()
}

// 保存所有
async function saveAll() {
  // 校验，至少有一个启用
  if (themeList.value.filter(t => t.is_used == 1).length !== 1) {
    ElMessage.error('必须有且仅有一个主题为启用状态')
    return
  }
  saving.value = true
  try {
    // 只传 data 数组即可，接口要和你约定
    await getApi().post('/api/site-theme', themeList.value)
    ElMessage.success('主题列表已保存')
    reload()
  } catch {
    ElMessage.error('保存失败')
  }
  saving.value = false
}
</script>

<style scoped>
/* 可选美化 */
.draggable-fade-enter-active, .draggable-fade-leave-active {
  transition: all .25s;
}
.draggable-fade-enter, .draggable-fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>