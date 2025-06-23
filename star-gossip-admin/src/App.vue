<template>
  <router-view/>
</template>

<script setup>
import { useSiteStore } from '@/store/site'
import { useRouter } from 'vue-router'
import { onMounted } from 'vue'

const siteStore = useSiteStore()
const router = useRouter()

onMounted(() => {
  if (!siteStore.currentSite || !siteStore.currentSite.api) {
    alert('请先选择站点！')
    router.replace('/login')
  }
})
</script>