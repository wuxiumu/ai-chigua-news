<template>
  <el-dialog
      :model-value="modelValue"
      @update:modelValue="emit('update:modelValue', $event)"
      title="AI帮助 / 联系方式"
      width="880px"
      center
      :close-on-click-modal="false"
  >
    <div class="aihelp-main-row">
      <!-- 信息区：自动循环渲染 -->
      <div class="aihelp-info-block">
        <div
            class="aihelp-info-row"
            v-for="(item, idx) in infos"
            :key="item.label + idx"
        >
          <span class="aihelp-info-label">{{ item.label }}：</span>
          <span class="aihelp-info-value">{{ item.value }}</span>
          <el-button
              v-if="item.copy"
              size="small"
              plain
              class="aihelp-copy-btn"
              @click="copyToClipboard(item.value, item.label)"
              circle
              title="复制"
          >
            <!-- 剪贴板svg图标 -->
            <svg viewBox="0 0 20 20" width="18" height="18" style="vertical-align:middle;">
              <rect x="5" y="3" width="10" height="14" rx="2" fill="#409EFF" />
              <rect x="7" y="1" width="6" height="4" rx="1" fill="#fff"/>
            </svg>
          </el-button>
        </div>
        <el-button
            type="success"
            size="large"
            style="width:90%;margin:32px auto 0 auto;display:block;font-size:18px"
            @click="payUrl && window.open(payUrl,'_blank')"
        >
          一键打赏（支持微信支付）
        </el-button>
        <el-button
            size="default"
            style="width:70%;margin:14px auto 0 auto;display:block;font-size:16px"
            @click="emitClose"
        >关闭</el-button>
      </div>
      <!-- 图片区 -->
      <div class="aihelp-pay-block-col">
        <div class="aihelp-pay-img-wrap">
          <el-image
              :src="payWx"
              class="aihelp-pay-img"
              fit="contain"
              :preview-src-list="[payWx]"
              :initial-index="0"
              referrerpolicy="no-referrer"
          />
          <div class="aihelp-pay-label">微信收款码</div>
        </div>
        <div class="aihelp-pay-img-wrap" style="margin-top:24px;">
          <el-image
              :src="payAli"
              class="aihelp-pay-img"
              fit="contain"
              :preview-src-list="[payAli]"
              :initial-index="0"
              referrerpolicy="no-referrer"
          />
          <div class="aihelp-pay-label">支付宝收款码</div>
        </div>
      </div>
    </div>
  </el-dialog>
</template>

<script setup>
import { ElMessage } from 'element-plus'
const props = defineProps({
  modelValue: Boolean,
  infos: {
    type: Array,
    default: () => []
  },
  payWx: String,
  payAli: String,
  payUrl: String
})
const emit = defineEmits(['update:modelValue'])
function copyToClipboard(text, label) {
  navigator.clipboard.writeText(text + '').then(
      () => ElMessage.success(label + ' 已复制！'),
      () => ElMessage.error('复制失败')
  )
}
function emitClose() {
  emit('update:modelValue', false)
}
</script>

<style scoped>
.aihelp-main-row {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: stretch;
  gap: 32px;
  padding: 8px 8px 16px 8px;
  max-width: 650px;
  margin: 0 auto;
}
.aihelp-info-block {
  flex: 1;
  max-width: 360px;
  min-width: 200px;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  gap: 18px;
  font-size: 18px;
}
.aihelp-info-row {
  display: flex;
  align-items: center;
  margin-bottom: 6px;
  font-size: 18px;
  font-weight: 500;
  gap: 8px;
  word-break: break-all;
}
.aihelp-info-label {
  min-width: 82px;
  color: #3a3a3a;
  font-weight: bold;
}
.aihelp-info-value {
  flex: 1;
  color: #222;
}
.aihelp-copy-btn {
  margin-left: 5px;
  color: #409EFF;
  background: #f6f7fa;
  border-radius: 50%;
  border: none;
  transition: background .18s;
}
.aihelp-copy-btn:hover {
  background: #e6f1fb;
  color: #2177ff;
}
.aihelp-pay-block-col {
  flex: 1;
  max-width: 300px;
  min-width: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  gap: 22px;
}
.aihelp-pay-img-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
}
.aihelp-pay-img {
  width: 200px;
  height: 260px;
  border-radius: 14px;
  box-shadow: 0 2px 16px #ececec;
  object-fit: cover;
  background: #fafbfc;
  margin-bottom: 8px;
}
.aihelp-pay-label {
  font-size: 16px;
  color: #666;
  margin-top: 4px;
  text-align: center;
}
@media (max-width: 900px) {
  .aihelp-main-row { flex-direction: column; align-items: center; gap: 12px; max-width: 99vw;}
  .aihelp-info-block, .aihelp-pay-block-col { max-width: 98vw; min-width: 0;}
  .aihelp-pay-img { width: 56vw; min-width: 120px; max-width: 260px; }
}
</style>