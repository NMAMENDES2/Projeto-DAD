import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import Register from '@/components/auth/Register.vue'
import Login from '@/components/auth/Login.vue'
import Game from '@/components/game/Game.vue'
import Transactions from '@/pages/Transactions.vue'


const router = createRouter({
  history: createWebHistory(), 
  routes: [
    {
      path: '/',
      name: 'home',
      component: Game,
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
    {
    path: '/transactions',
    name: 'Transactions',
    component: Transactions,
    
  }
  ],
})

export default router
