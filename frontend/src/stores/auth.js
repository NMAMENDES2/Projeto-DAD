import { defineStore } from 'pinia'
import axios from 'axios'
import { ref, computed } from 'vue'

console.log('Auth store is loading...')

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || '')
  // Set initial status based on whether we have a token
  const authStatus = ref(token.value ? 'loading' : 'unauthenticated')

  if (token.value) {
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + token.value
  }
  axios.defaults.headers.common['Accept'] = 'application/json'

  const isAuthenticated = computed(() => !!user.value)
  const userName = computed(() => user.value?.name || '')

  async function register(registerData) {
    authStatus.value = 'loading'
    try {
      const response = await axios.post('auth/register', registerData)
      console.log('Response from register endpoint:', response.data)
      if (response.data.token) {
        token.value = response.data.token
        localStorage.setItem('token', token.value)
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + token.value
        await fetchUser()
      }
      return response.data
    } catch (e) {
      authStatus.value = 'unauthenticated'
      console.error('REGISTER API ERROR:', e.response?.data)
      return false
    }
  }

  async function login(credentials) {
    authStatus.value = 'loading'
    try {
      const responseLogin = await axios.post('auth/login', credentials)
      console.log('Response from login endpoint:', responseLogin.data)
      token.value = responseLogin.data.token
      localStorage.setItem('token', token.value)
      axios.defaults.headers.common.Authorization = 'Bearer ' + token.value
      user.value = responseLogin.data.user
      authStatus.value = 'authenticated'
      return user.value
    } catch (e) {
      logout()
      console.error('LOGIN API ERROR:', e.response?.data)
      return false
    }
  }

  async function fetchUser() {
    authStatus.value = 'loading'
    try {
      const responseUser = await axios.get('users/me')
      console.log('Response from users/me endpoint:', responseUser.data)
      // Laravel Resource wraps data in a 'data' property
      user.value = responseUser.data.data || responseUser.data
      console.log('User data set to:', user.value)
      authStatus.value = 'authenticated'
    } catch (error) {
      logout()
      console.error('Failed to fetch user on refresh', error)
    }
  }

  function logout() {
    user.value = null
    token.value = ''
    localStorage.removeItem('token')
    axios.defaults.headers.common['Authorization'] = ''
    authStatus.value = 'unauthenticated'
  }

  // Initialize: fetch user if token exists (for page refresh)
  if (token.value) {
    fetchUser()
    console.log('Fetching user on store initialization due to existing token.')
  }

  return {
    user,
    token,
    isAuthenticated,
    authStatus,
    userName,
    register,
    login,
    logout,
    fetchUser,
  }
})