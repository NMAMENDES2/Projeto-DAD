import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import axios from 'axios'

const apiDomain = import.meta.env.VITE_API_DOMAIN
axios.defaults.baseURL = `http://${apiDomain}/api/`

console.log('API domain:', apiDomain)

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')
