<template>
  <el-card>
    <div style="margin-bottom: 18px;display:flex;align-items:center;gap:18px;">
      <el-select v-model="selectedYear" placeholder="选择年份" @change="updateCalendar" style="width:120px;">
        <el-option
            v-for="y in yearOptions"
            :key="y"
            :value="y"
            :label="y+'年'"
        />
      </el-select>
      <span>（本月在最前，共12个月）</span>
      <el-button @click="refreshFiles" type="primary">刷新采集文件</el-button>
    </div>
    <div class="year-calendar">
      <div
          class="month"
          v-for="(month, i) in last12Months"
          :key="month.label"
      >
        <div class="month-label">
          {{ month.label }}
          <span v-if="month.year === nowYear" style="color:#67c23a;">[今年]</span>
          <span v-else style="color:#999;">[{{ month.year }}年]</span>
        </div>
        <div class="days-grid">
          <div v-for="dayObj in month.days" :key="dayObj.date" class="day-cell">
            <el-button
                :type="fileMap[dayObj.date] ? 'success' : 'info'"
                size="large"
                class="day-btn"
                @click="onClickDate(dayObj.date)"
            >
              {{ dayObj.date.slice(-2) }}
            </el-button>
            <div class="weekday">{{ dayObj.weekday }}</div>
          </div>
        </div>
      </div>
    </div>
    <div style="margin-top:24px;color:#888;">
      <el-tag type="success">绿色</el-tag>：已采集可编辑
      <el-tag type="info" style="margin-left:8px;">灰色</el-tag>：未采集，点按钮自动采集后进入编辑
    </div>
  </el-card>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import request from '@/api/request'
import dayjs from 'dayjs'
import { ElMessage } from 'element-plus'

// 当前年（供标记今年）
const nowYear = dayjs().year()

// 年份选择下拉（近10年）
const yearOptions = []
for(let y=nowYear; y>=nowYear-10; y--) yearOptions.push(y)

const selectedYear = ref(nowYear)
const fileList = ref([])
const fileMap = ref({})

// 切换年份/初始化时刷新文件（如需要可去掉watch）
watch(selectedYear, () => { refreshFiles() })

async function refreshFiles() {
  const res = await request.get('/api/titlehub/files')
  fileList.value = res.data.data || []
  fileMap.value = {}
  for (const d of fileList.value) fileMap.value[d] = true
}

// 动态计算当前起点
const last12Months = computed(() => {
  // 用selectedYear和本月为起点
  let thisMonth
  if (selectedYear.value == nowYear) {
    thisMonth = dayjs().startOf('month')
  } else {
    // 选择其他年时，默认从12月
    thisMonth = dayjs(`${selectedYear.value}-12-01`).startOf('month')
  }
  const months = []
  let base = thisMonth
  for (let i = 0; i < 12; i++) {
    const label = base.format('YYYY年MM月')
    const year = base.year()
    const days = []
    const daysInMonth = base.daysInMonth()
    for (let d = 1; d <= daysInMonth; d++) {
      const dateObj = base.date(d)
      const dateStr = dateObj.format('YYYY-MM-DD')
      days.push({
        date: dateStr,
        weekday: ['日','一','二','三','四','五','六'][dateObj.day()]
      })
    }
    months.push({ label, year, days })
    base = base.subtract(1, 'month')
  }
  return months // 注意顺序: 本月在最前
})

function updateCalendar() {
  // 切换年份时自动刷新
  refreshFiles()
}

const router = useRouter()
async function onClickDate(dateStr) {
  if (!dateStr) return
  if (fileMap.value[dateStr]) {
    router.push({ name: 'TitleHubEdit', query: { date: dateStr } })
  } else {
    const res = await request.get('/api/titlehub/fetch?date=' + dateStr)
    if (res.data.code === 0) {
      ElMessage.success('采集成功！')
      await refreshFiles()
      router.push({ name: 'TitleHubEdit', query: { date: dateStr } })
    } else {
      ElMessage.error(res.data.msg || '采集失败')
    }
  }
}
onMounted(refreshFiles)
</script>

<style scoped>
.year-calendar {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  justify-content: center;
}
.month {
  width: 22%;
  min-width: 260px;
  margin-bottom: 18px;
  background: #fafbfc;
  border-radius: 8px;
  padding: 10px 10px 20px 10px;
  box-shadow: 0 1px 3px #eaeaea;
  display: inline-block;
  vertical-align: top;
}
.month-label {
  font-weight: bold;
  text-align: center;
  margin-bottom: 10px;
  font-size: 18px;
  color: #555;
}
.days-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px 6px;
}
.day-cell {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 48px;
  margin-bottom: 5px;
}
.day-btn {
  width: 40px;
  height: 40px;
  padding: 0;
  font-size: 17px;
}
.weekday {
  font-size: 12px;
  color: #888;
  margin-top: 2px;
}
</style>