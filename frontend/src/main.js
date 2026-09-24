import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './styles/main.css'

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => {})
  })
}

createApp(App).use(router).mount('#app')