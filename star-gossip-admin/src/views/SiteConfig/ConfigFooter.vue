<template>
  <el-form :model="footer" label-width="90px">
    <el-form-item label="背景色">
      <el-input v-model="footer.bgColor" />
    </el-form-item>
    <el-form-item label="文字色">
      <el-input v-model="footer.textColor" />
    </el-form-item>
    <el-form-item label="版权">
      <el-input v-model="footer.copyright" />
    </el-form-item>
    <el-form-item label="关于区块">
      <el-input type="textarea" v-model="footer.about" />
    </el-form-item>
    <el-form-item label="社交链接">
      <el-table :data="footer.socialLinks" style="width:100%">
        <el-table-column prop="icon" label="图标">
          <template #default="{row}">
            <el-input v-model="row.icon" />
          </template>
        </el-table-column>
        <el-table-column prop="href" label="链接">
          <template #default="{row}">
            <el-input v-model="row.href" />
          </template>
        </el-table-column>
        <el-table-column prop="title" label="标题">
          <template #default="{row}">
            <el-input v-model="row.title" />
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
    <el-form-item label="底部链接区块">
      <div v-for="(section, idx) in footer.links" :key="idx" style="margin-bottom:10px;">
        <el-input v-model="section.title" placeholder="区块标题" style="width:180px;margin-right:10px" />
        <el-button size="small" type="danger" @click="removeSection(idx)">删除区块</el-button>
        <el-table :data="section.items" size="small" style="width:80%">
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
          <el-table-column label="操作">
            <template #default="{ $index }">
              <el-button type="danger" size="small" @click="section.items.splice($index,1)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
        <el-button size="small" @click="addItem(idx)">添加链接</el-button>
      </div>
      <el-button size="small" style="margin-top:8px" @click="addSection">添加区块</el-button>
    </el-form-item>
  </el-form>
</template>
<script setup>
const props = defineProps({ modelValue: Object })
const footer = props.modelValue
function addLink() { footer.socialLinks.push({ icon: '', href: '', title: '' }) }
function removeLink(idx) { footer.socialLinks.splice(idx, 1) }
function addSection() { footer.links.push({ title: '', items: [] }) }
function removeSection(idx) { footer.links.splice(idx, 1) }
function addItem(sectionIdx) { footer.links[sectionIdx].items.push({ text: '', href: '' }) }
</script>