// import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { ref } from 'vue'

// import avatarNoneAssetURL from '@/assets/avatar-none.png'
export const useAuthStore = defineStore('auth', () => {

  /*
    const clearUser = () => {
    resetIntervalToRefreshToken();
    user.value = null;
    token.value = '';
    localStorage.removeItem('token');
    axios.defaults.headers.common.Authorization = '';
  };
  */

  const user = ref(null)
  const token = ref('')

  const register = async (registerData) => {
    try {
      const response = await axios.post('auth/register', registerData)
      return response.data
    } catch (e) {
      console.error("REGISTER API ERROR:", e.response?.data)
      return false
    }
  }

  const login = async (credentials) => {
    try {
      const responseLogin = await axios.post('auth/login', credentials);

      token.value = responseLogin.data.token;
      localStorage.setItem('token', token.value);
      console.log(token.value)

      axios.defaults.headers.common.Authorization = 'Bearer ' + token.value;

      const responseUser = await axios.get('users/me');
      user.value = responseUser.data;
      //repeatRefreshToken();
      
      return user.value;
    } catch (e) {
      return false;
    }
  };
  return {
    register,
    login
  }
})
