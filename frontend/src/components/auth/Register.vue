<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useErrorStore } from '@/stores/error'

const router = useRouter()
const storeAuth = useAuthStore()
const storeError = useErrorStore()

const name = ref('')
const nickname = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const responseData = ref('')

const emit = defineEmits(['success'])

const register = async () => {
  console.log('Submitting register form...')
  try {
    if (password.value !== confirmPassword.value) {
      console.log("As passwords não coincidem")
      return
    }

    const response = await storeAuth.register({
      name: name.value,
      nickname: nickname.value,
      email: email.value,
      password: password.value,
      password_confirmation: confirmPassword.value
    })

    if (response) {
      console.log("Registration successful:", response)
      emit('success')
    } else {
      console.error("Registration failed:", response)
    }

  } catch (error) {
    console.error('Registration failed:', error)
  }
}
</script>

<template>
  <div class="w-full max-w-sm mx-auto overflow-hidden bg-white rounded-lg shadow-md dark:bg-gray-800">
    <div class="px-6 py-4">
      <h3 class="text-xl font-medium text-center text-gray-600 dark:text-gray-200">
        Create an Account
      </h3>

      <form @submit.prevent="register">

        <div>
          <input
            class="block w-full px-4 py-2 text-gray-700 placeholder-gray-500 bg-white border rounded-lg focus:border-blue-400 focus:ring focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none dark:bg-gray-800 dark:border-gray-600"
            id="name"
            type="text"
            placeholder="Full Name"
            v-model="name"
            required
          />
        </div>

        <div class="mt-4">
          <input
            class="block w-full px-4 py-2 text-gray-700 placeholder-gray-500 bg-white border rounded-lg focus:border-blue-400 focus:ring focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none dark:bg-gray-800 dark:border-gray-600"
            id="nickname"
            type="text"
            placeholder="Nickname"
            v-model="nickname"
            required
          />
        </div>

        <div class="mt-4">
          <input
            class="block w-full px-4 py-2 text-gray-700 placeholder-gray-500 bg-white border rounded-lg focus:border-blue-400 focus:ring focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none dark:bg-gray-800 dark:border-gray-600"
            id="email"
            type="email"
            placeholder="Email Address"
            v-model="email"
            required
          />
        </div>

        <div class="mt-4">
          <input
            class="block w-full px-4 py-2 text-gray-700 placeholder-gray-500 bg-white border rounded-lg focus:border-blue-400 focus:ring focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none dark:bg-gray-800 dark:border-gray-600"
            id="password"
            type="password"
            placeholder="Password"
            v-model="password"
            required
          />
        </div>

        <div class="mt-4">
          <input
            class="block w-full px-4 py-2 text-gray-700 placeholder-gray-500 bg-white border rounded-lg focus:border-blue-400 focus:ring focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none dark:bg-gray-800 dark:border-gray-600"
            id="confirmPassword"
            type="password"
            placeholder="Confirm Password"
            v-model="confirmPassword"
            required
          />
        </div>

        <div class="mt-4">
          <button
            class="w-full px-4 py-2 text-white bg-blue-500 rounded-lg hover:bg-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-50"
            type="submit">
            Register
          </button>
        </div>

      </form>
    </div>

    <div class="flex items-center justify-center py-4 text-center bg-gray-50 dark:bg-gray-700">
      <span class="text-sm text-gray-600 dark:text-gray-200">Already have an account?</span>
      <a href="/login" class="mx-2 text-sm font-bold text-blue-500 dark:text-blue-400 hover:underline">
        Sign In
      </a>
    </div>
  </div>
</template>
