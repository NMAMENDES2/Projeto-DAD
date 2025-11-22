import { defineStore } from 'pinia'
import axios from 'axios'
import { ref, computed } from 'vue'
import { useErrorStore } from '@/stores/error'


const avatarNoneAssetURL = '@/avatar-empty.png'

export const useAuthStore = defineStore('auth', () => {
  const storeError = useErrorStore()
  const user = ref(null)

  const userName = computed(() => user.value?.name || '')
  const userNickname = computed(() => user.value?.nickname || '')
  const userFirstLastName = computed(() => {
    const names = userName.value.trim().split(' ')
    const firstName = names[0] ?? ''
    const lastName = names.length > 1 ? names[names.length - 1] : ''
    return (firstName + ' ' + lastName).trim()
  })
  const userEmail = computed(() => user.value?.email || '')
  const userId = computed(() => user.value?.id || null)
  const userType = computed(() => user.value?.type || '')
  const userBrainCoins = computed(() => user.value?.coins_balance || 0)
  const userPhotoUrl = computed(() => {
    const photoFile = user.value ? (user.value.photo_filename ?? '') : ''
    if (photoFile) {
      return `${axios.defaults.baseURL}/image/${photoFile}`;
    }
    return avatarNoneAssetURL
  })

  const token = ref(localStorage.getItem('token') || '')
  // Set initial status based on whether we have a token
  const authStatus = ref(token.value ? 'loading' : 'unauthenticated')

  if (token.value) {
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + token.value
  }
  axios.defaults.headers.common['Accept'] = 'application/json'

  const isAuthenticated = computed(() => !!user.value)

  const clearUser = () => {
    resetIntervalToRefreshToken();
    user.value = null;
    token.value = '';
    localStorage.removeItem('token');
    axios.defaults.headers.common.Authorization = '';
  }

  // In your auth store login function:
  async function login(credentials) {
    storeError.resetMessages()
    authStatus.value = 'loading'

    console.log('1. Starting login with credentials:', credentials)

    try {
      const responseLogin = await axios.post('auth/login', credentials)
      console.log('2. Login response:', responseLogin.data)

      token.value = responseLogin.data.token
      localStorage.setItem('token', token.value)
      console.log('3. Token set:', token.value)

      axios.defaults.headers.common.Authorization = 'Bearer ' + token.value

      console.log('4. Fetching user data...')
      const responseUser = await axios.get('users/me')
      console.log('5. User data received:', responseUser.data)

      user.value = responseUser.data.data // ??? data.data é insano
      
      authStatus.value = 'authenticated' 
      console.log('6. User set in store:', user.value)

      repeatRefreshToken()
      return user.value
    } catch (e) {
      console.error('Login error:', e)
      console.error('Error response:', e.response?.data)

      clearUser()
      storeError.setErrorMessages(
        e.response?.data?.message || 'Login failed',
        e.response?.data?.errors || [],
        e.response?.status || 500,
        'Authentication Error!'
      )
      return false
    }
  }

  const logout = async () => {
    storeError.resetMessages()
    try {
      await axios.post('auth/logout')
      clearUser();
      return true;
    } catch (e) {
      storeError.setErrorMessages(
        e.response.data.message,
        [], // ????
        e.response.status,
        'Authentication Error!'
      );
      return false;
    }
  };

  let intervalToRefreshToken = null;

  const resetIntervalToRefreshToken = () => {
    if (intervalToRefreshToken) {
      clearInterval(intervalToRefreshToken)
    }
    intervalToRefreshToken = null
  }

  const repeatRefreshToken = () => {
    if (intervalToRefreshToken) {
      clearInterval(intervalToRefreshToken)
    }
    intervalToRefreshToken = setInterval(
      async () => {
        try {
          const response = await axios.post('auth/refreshtoken')
          token.value = response.data.token
          axios.defaults.headers.common.Authorization = 'Bearer ' + token.value
          return true
        } catch (e) {
          clearUser()
          storeError.setErrorMessages(
            e.response.data.message,
            e.response.data.errors,
            e.response.status,
            'Authentication Error!'
          )
          return false
        }
      },
      1000 * 60 * 110 // Renova token a cada 110 minutos
    )
    return intervalToRefreshToken
  }

  async function register(registerData) {
    storeError.resetMessages()
    try {
      const response = await axios.post('auth/register', registerData)
      return response.data;
    } catch (e) {
      storeError.setErrorMessages(
        e.response?.data.message || 'Registration failed.',
        e.response?.data.errors || [],
        e.response?.status || 500,
        'Registration Error!'
      );
      return false;
    }
  };
  const restoreToken = async function () {
    const storedToken = localStorage.getItem('token');
    if (storedToken) {
      authStatus.value = 'loading'
      try {
        token.value = storedToken;
        axios.defaults.headers.common.Authorization = 'Bearer ' + token.value;
        const responseUser = await axios.get('users/me');
        user.value = responseUser.data.data;
        authStatus.value = 'authenticated'
        repeatRefreshToken();
        return true;
      } catch (e) {
        console.error('Error restoring token:', e.response?.data || e.message);
        clearUser();
        authStatus.value = 'unauthenticated'
        return false;
      }
    }
    authStatus.value = 'unauthenticated'
    return false;
  };

  return {
    user,
    token,
    isAuthenticated,
    authStatus,
    userName,
    register,
    login,
    logout,
    userNickname,
    userFirstLastName,
    userEmail,
    userId,
    userType,
    userBrainCoins,
    userPhotoUrl,
    restoreToken,
  }
})