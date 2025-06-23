import { defineConfig } from 'vite'
import { fileURLToPath, URL } from 'node:url'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  base: '/',  // 只需这一行指定上线后的访问路径
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    proxy: {
      '/api': 'http://127.0.0.1:8080' // 仅本地开发用
    }
  },
  build: {
    chunkSizeWarningLimit: 10000  // 调高限制（比如1000k）
  }
})