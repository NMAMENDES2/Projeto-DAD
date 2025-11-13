import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import Register from '@/components/auth/Register.vue'

const router = createRouter({
  history: createWebHistory(), 
  routes: [
    {
      path: '/teste',
      name: 'home',
      component: Home,
    },
    {
      path: '/register',
      name: 'register',
      component: Register,
    }
  ],
})

export default router
