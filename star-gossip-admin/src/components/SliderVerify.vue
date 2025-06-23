<template>
  <div class="slider-wrap" :style="{width: '320px',margin:'0 auto'}">
    <canvas ref="canvasBg" :width="320" :height="160" style="border-radius:4px;box-shadow:0 1px 6px #ddd"></canvas>
    <div class="slider-bar" @mousedown="startDrag" :class="{active: dragging}">
      <div class="slider-btn" :style="{left: sliderLeft+'px'}"></div>
      <span v-if="!verified" class="slider-text">请按住滑块，拖动完成拼图</span>
      <span v-if="verified" class="slider-text ok">验证通过！</span>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted, watch } from 'vue'
const emits = defineEmits(['success'])

const dragging = ref(false)
const sliderLeft = ref(0)
const verified = ref(false)

const canvasBg = ref(null)

function startDrag(e) {
  if (verified.value) return
  dragging.value = true
  const startX = e.clientX
  const oldLeft = sliderLeft.value

  document.onmousemove = move
  document.onmouseup = up

  function move(ev) {
    if (!dragging.value) return
    let diff = ev.clientX - startX + oldLeft
    diff = Math.max(0, Math.min(diff, 260))
    sliderLeft.value = diff
  }
  function up(ev) {
    dragging.value = false
    // 简单模拟拼图完成
    if (sliderLeft.value > 250 && sliderLeft.value < 270) {
      verified.value = true
      emits('success')
    } else {
      sliderLeft.value = 0
    }
    document.onmousemove = null
    document.onmouseup = null
  }
}

// 可选：可根据需求自定义 canvas 背景图
onMounted(() => {
  const ctx = canvasBg.value.getContext('2d')
  ctx.fillStyle = '#e7eaf0'
  ctx.fillRect(0, 0, 320, 160)
  ctx.font = "bold 40px Arial"
  ctx.fillStyle = '#c4c5c9'
  ctx.fillText("拖动滑块拼图", 30, 90)
})
</script>
<style scoped>
.slider-wrap{user-select:none;}
.slider-bar{margin-top:10px;background:#f0f3fa;border-radius:4px;position:relative;height:40px;}
.slider-bar.active{background:#b7d5f9;}
.slider-btn{position:absolute;top:0;width:60px;height:40px;background:#6cbaf7;border-radius:4px;box-shadow:0 0 5px #888;}
.slider-text{position:absolute;left:80px;top:10px;font-size:14px;color:#666;}
.slider-text.ok{color:green;}
</style>