<template>
  <el-form :model="navbar" label-width="90px">
    <el-form-item label="背景色">
      <el-input v-model="navbar.bgColor" />
    </el-form-item>
    <el-form-item label="文字色">
      <el-input v-model="navbar.textColor" />
    </el-form-item>
    <el-form-item label="Logo文字">
      <el-input v-model="navbar.logo.text" />
    </el-form-item>
    <el-form-item label="Logo链接">
      <el-input v-model="navbar.logo.href" />
    </el-form-item>
    <el-form-item label="导航链接">
      <el-table :data="navbar.links" style="width:100%">
        <el-table-column prop="text" label="文字">
          <template #default="{row}">
            <el-input v-model="row.text" />
          </template>
        </el-table-column>
        <el-table-column prop="href" label="链接">
          <template #default="{row}">
            <el-input v-model="row.href" />
          </template>
        </el-table-column>
        <el-table-column prop="icon" label="图标">
          <template #default="{row}">
            <el-input v-model="row.icon" />
          </template>
        </el-table-column>
        <el-table-column label="操作">
          <template #default="{ $index }">
            <el-button type="danger" size="small" @click="removeLink($index)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-button size="small" style="margin-top:8px" @click="addLink">添加</el-button>
    </el-form-item>
    <el-form-item label="搜索占位">
      <el-input v-model="navbar.search.placeholder" />
    </el-form-item>
    <el-form-item label="搜索action">
      <el-input v-model="navbar.search.action" />
    </el-form-item>
  </el-form>
</template>
<script setup>
const props = defineProps({ modelValue: Object })
const emit = defineEmits(['update:modelValue'])
const navbar = props.modelValue
function addLink() { navbar.links.push({ text: '', href: '', icon: '' }) }
function removeLink(idx) { navbar.links.splice(idx, 1) }
</script>