<template>
  <el-card>
    <el-tabs v-model="activeTab">
      <el-tab-pane label="导航栏" name="navbar">
        <ConfigNavbar v-model="config.navbar" />
      </el-tab-pane>
      <el-tab-pane label="轮播图" name="carousel">
        <ConfigCarousel v-model="config.carousel" />
      </el-tab-pane>
      <el-tab-pane label="横幅" name="banner">
        <ConfigBanner v-model="config.banner" />
      </el-tab-pane>
      <el-tab-pane label="热门文章" name="popularArticles">
        <PopularArticles v-model="config.popularArticles" />
      </el-tab-pane>
      <el-tab-pane label="热门标签" name="popularTags">
        <ConfigPopularTags v-model="config.popularTags" />
      </el-tab-pane>
      <el-tab-pane label="页脚" name="footer">
        <ConfigFooter v-model="config.footer" />
      </el-tab-pane>
      <el-tab-pane label="主题" name="theme">
        <ThemeManage />
      </el-tab-pane>
    </el-tabs>
    <div v-if="activeTab !== 'theme'" style="text-align:center;margin-top:32px">
      <el-button type="primary" @click="saveConfig" :loading="saving">保存</el-button>
      <el-button @click="loadConfig" :disabled="saving">重置</el-button>
    </div>
  </el-card>
</template>

<script setup>
import { ref, reactive,onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import getApi from '@/api/request'
import ConfigNavbar from './SiteConfig/ConfigNavbar.vue'
import ConfigCarousel from './SiteConfig/ConfigCarousel.vue'
import ConfigBanner from './SiteConfig/ConfigBanner.vue'
import ConfigPopularTags from './SiteConfig/ConfigPopularTags.vue'
import PopularArticles from './SiteConfig/PopularArticles.vue'
import ConfigFooter from './SiteConfig/ConfigFooter.vue'
import ThemeManage from './SiteConfig/ThemeManage.vue'


// === 默认配置：所有字段写全，嵌套结构绝不缺 ===
const defaultConfig = {
  navbar: {
    bgColor: 'bg-primary',
    textColor: 'text-white',
    logo: { text: '蓝色博客', href: '#home-page' },
    links: [
      { text: '首页', href: '#home-page', icon: 'fa-home' },
      { text: '技术', href: '#', icon: 'fa-code' },
      { text: '生活', href: '#', icon: 'fa-coffee' },
      { text: '关于', href: '#', icon: 'fa-user' },
      { text: '联系', href: '#', icon: 'fa-envelope' }
    ],
    search: { placeholder: '搜索文章...', action: '#' }
  },
  carousel: {
    slides: [
      // 初始可为空数组
      { image: '', title: '', subtitle: '', button: { text: '', href: '' } }
    ]
  },
  banner: {
    bgColor: 'bg-gradient-to-r from-primary to-dark',
    title: '探索技术与创意的无限可能',
    subtitle: '分享知识，连接世界',
    button: {
      text: '开始探索', href: '#news-container', bgColor: 'bg-white', textColor: 'text-primary'
    },
    image: 'https://picsum.photos/seed/blogbanner/1200/400'
  },
  popularTags: {
    title: '热门标签',
    tags: [
      { name: '', count: 0 },
      // { name: 'JavaScript', count: 24 },
      // { name: 'React', count: 18 },
      // { name: 'Vue', count: 15 }
    ]
  },
  popularArticles: {
    title: '热门文章',
    articles: [
      // { name: '', count: 0 },
      // { name: 'JavaScript', count: 24 },
      // { name: 'React', count: 18 },
      // { name: 'Vue', count: 15 }
    ]
  },
  footer: {
    bgColor: 'bg-dark',
    textColor: 'text-gray-300',
    copyright: '© 2025 蓝色博客. 保留所有权利.',
    about: '蓝色博客是一个分享技术和创意的平台，致力于为开发者和设计师提供有价值的内容和资源。',
    socialLinks: [
      { icon: 'fa-github', href: '#', title: 'GitHub' }
    ],
    links: [
      { title: '快速链接', items: [
          { text: '首页', href: '#home-page' }
        ]}
    ]
  }
}

// === 响应式全局配置对象 ===
const config = reactive(JSON.parse(JSON.stringify(defaultConfig)))
const activeTab = ref('navbar')
const saving = ref(false)

// === 智能递归修复，确保所有层级字段完整 ===
function autoFixConfig(target, template) {
  for (const key in template) {
    if (!(key in target)) {
      target[key] = JSON.parse(JSON.stringify(template[key]))
    } else if (
        template[key] && typeof template[key] === 'object' && !Array.isArray(template[key])
    ) {
      if (
          typeof target[key] !== 'object' || target[key] === null || Array.isArray(target[key])
      ) {
        target[key] = JSON.parse(JSON.stringify(template[key]))
      } else {
        autoFixConfig(target[key], template[key])
      }
    } else if (Array.isArray(template[key])) {
      if (!Array.isArray(target[key])) {
        target[key] = JSON.parse(JSON.stringify(template[key]))
      }
    }
  }
  return target
}

// === 深合并工具 ===
function deepMerge(target, source) {
  for (const key in source) {
    if (
        source[key] &&
        typeof source[key] === 'object' &&
        !Array.isArray(source[key])
    ) {
      target[key] = target[key] || {}
      deepMerge(target[key], source[key])
    } else {
      target[key] = source[key]
    }
  }
  return target
}

// === 拉取远端配置并自愈 ===
async function loadConfig() {
  try {
    const res = await getApi().get('/api/site-config')
    // 先用默认配置全量覆盖，避免历史遗留
    deepMerge(config, JSON.parse(JSON.stringify(defaultConfig)))
    // 再用接口数据合并
    deepMerge(config, res.data.data || {})
    // 最后补齐所有缺失字段
    autoFixConfig(config, defaultConfig)
    // ElMessage.success('配置已加载并自动修复')
  } catch (e) {
    ElMessage.error('获取配置失败，将加载默认配置')
    deepMerge(config, JSON.parse(JSON.stringify(defaultConfig)))
    autoFixConfig(config, defaultConfig)
  }
}

// === 保存到后端 ===
async function saveConfig() {
  saving.value = true
  try {
    await getApi().post('/api/site-config', config)
    ElMessage.success('保存成功')
  } catch (e) {
    ElMessage.error('保存失败')
  }
  saving.value = false
}

// === 页面加载时拉取配置 ===
onMounted(() => {
  loadConfig()
})
</script>