<template>
  <el-form :model="banner" label-width="90px">
    <el-form-item label="背景色">
      <el-input v-model="banner.bgColor" />
    </el-form-item>
    <el-form-item label="标题">
      <el-input v-model="banner.title" />
    </el-form-item>
    <el-form-item label="副标题">
      <el-input v-model="banner.subtitle" />
    </el-form-item>
    <el-form-item label="按钮文字">
      <el-input v-model="banner.button.text" />
    </el-form-item>
    <el-form-item label="按钮链接">
      <el-input v-model="banner.button.href" />
    </el-form-item>
    <el-form-item label="按钮背景色">
      <el-input v-model="banner.button.bgColor" />
    </el-form-item>
    <el-form-item label="按钮文字色">
      <el-input v-model="banner.button.textColor" />
    </el-form-item>
    <el-form-item label="横幅图片">
      <el-input v-model="banner.image" />
    </el-form-item>
  </el-form>
</template>
<script setup>
const props = defineProps({ modelValue: Object })
const banner = props.modelValue
</script>