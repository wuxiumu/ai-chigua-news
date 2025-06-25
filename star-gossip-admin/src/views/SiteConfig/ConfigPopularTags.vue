<template>
  <el-form :model="popularTags" label-width="90px">
    <el-form-item label="区块标题">
      <el-input v-model="popularTags.title" />
    </el-form-item>
    <el-form-item label="热门标签">
      <el-table :data="popularTags.tags" style="width:100%">
        <el-table-column prop="name" label="标签">
          <template #default="{row}">
            <el-input v-model="row.name" />
          </template>
        </el-table-column>
        <el-table-column prop="count" label="计数">
          <template #default="{row}">
            <el-input-number v-model="row.count" :min="0" />
          </template>
        </el-table-column>
        <el-table-column label="操作">
          <template #default="{ $index }">
            <el-button type="danger" size="small" @click="removeTag($index)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-button size="small" style="margin-top:8px" @click="addTag">添加标签</el-button>
    </el-form-item>
  </el-form>
</template>
<script setup>
const props = defineProps({ modelValue: Object })
const popularTags = props.modelValue
function addTag() { popularTags.tags.push({ name: '', count: 1 }) }
function removeTag(idx) { popularTags.tags.splice(idx, 1) }
</script>