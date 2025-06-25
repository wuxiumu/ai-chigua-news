<template>
  <el-form :model="carousel" label-width="90px">
    <el-form-item label="轮播列表">
      <el-table :data="carousel.slides" style="width:100%">
        <el-table-column prop="image" label="图片">
          <template #default="{row}">
            <el-input v-model="row.image" placeholder="图片URL" />
          </template>
        </el-table-column>
        <el-table-column prop="title" label="标题">
          <template #default="{row}">
            <el-input v-model="row.title" />
          </template>
        </el-table-column>
        <el-table-column prop="subtitle" label="副标题">
          <template #default="{row}">
            <el-input v-model="row.subtitle" />
          </template>
        </el-table-column>
        <el-table-column label="按钮文字">
          <template #default="{row}">
            <el-input v-model="row.button.text" />
          </template>
        </el-table-column>
        <el-table-column label="按钮链接">
          <template #default="{row}">
            <el-input v-model="row.button.href" />
          </template>
        </el-table-column>
        <el-table-column label="操作">
          <template #default="{ $index }">
            <el-button type="danger" size="small" @click="removeSlide($index)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-button size="small" style="margin-top:8px" @click="addSlide">添加轮播</el-button>
    </el-form-item>
  </el-form>
</template>

<script setup>

/**
 * 作为表单子组件，支持 v-model 绑定父组件对象（如 config.carousel）
 * 这样全部配置一起存储、统一保存和重置，风格与其他配置组件一致！
 */
// const props = defineProps({
//   modelValue: {
//     type: Object,
//     required: true
//   }
// })
const props = defineProps({ modelValue: Object })
const emit = defineEmits(['update:modelValue'])

/**
 * 直接对 props.modelValue 操作，不需复制一份
 */
const carousel = props.modelValue

function addSlide() {
  carousel.slides = carousel.slides || []
  carousel.slides.push({
    image: '',
    title: '',
    subtitle: '',
    button: { text: '', href: '' }
  })
}
function removeSlide(idx) {
  carousel.slides.splice(idx, 1)
}
</script>