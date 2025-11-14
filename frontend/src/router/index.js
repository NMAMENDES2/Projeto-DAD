import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import Register from '@/components/auth/Register.vue'
import Login from '@/components/auth/Login.vue'

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
    },
    {
      path: '/login',
      name: 'login',
      component: Login,
    },
  ],
})

export default router
