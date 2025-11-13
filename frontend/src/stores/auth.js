// import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'

// import avatarNoneAssetURL from '@/assets/avatar-none.png'

export const useAuthStore = defineStore('auth', () => {

  const register = async (registerData) => {
    try {
      const response = await axios.post('auth/register', registerData)
      return response.data
    } catch (e) {
      return false
    }
  }
  return { 
    register
  }
})
