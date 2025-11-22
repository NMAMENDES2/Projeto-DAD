<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useErrorStore } from '@/stores/error'
import { useAuthStore } from '@/stores/auth'
import ErrorMessage from '@/components/common/ErrorMessage.vue';

import logoUrl from '@/assets/kirkification.png'

const router = useRouter()
const storeAuth = useAuthStore()
const storeError = useErrorStore()

const emit = defineEmits(['success'])

const email = ref('')
const password = ref('')
const responseData = ref('')

const submit = async () => {
    console.log('Submitting login form...')
    
    const user = await storeAuth.login({
        email: email.value,
        password: password.value
    })
    
    console.log("Login result:", user)
    
    if (user && user.name) {
        responseData.value = user.name
        console.log('Login successful! User name:', responseData.value)
        
        // Emit success event to parent
        emit('success')
        
        // Small delay to ensure auth state is updated
        setTimeout(() => {
            router.push("/")
        }, 100)
    } else {
        console.log('Login failed')
    }
}
</script>

<template>
    <div class="w-full max-w-sm mx-auto overflow-hidden bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="px-6 py-4">
            <div class="flex justify-center mx-auto">
                <img class="w-auto h-12" :src="logoUrl" alt="">
            </div>

            <h3 class="mt-3 text-xl font-medium text-center text-gray-600 dark:text-gray-200">Welcome Back</h3>

            <p class="mt-1 text-center text-gray-500 dark:text-gray-400">Login or create account</p>

            <form @submit.prevent="submit">

                <div class="w-full mt-4">
                    <input
                        class="block w-full px-4 py-2 mt-2 text-gray-700 placeholder-gray-500 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 focus:border-blue-400 dark:focus:border-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring focus:ring-blue-300"
                        id="email" type="email" placeholder="Email Address" aria-label="Email Address"
                        v-model="email" />
                    <ErrorMessage :errorMessage="storeError.fieldMessage('email')"></ErrorMessage>
                </div>

                <div class="w-full mt-4">
                    <input
                        class="block w-full px-4 py-2 mt-2 text-gray-700 placeholder-gray-500 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 focus:border-blue-400 dark:focus:border-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring focus:ring-blue-300"
                        id="password" type="password" placeholder="Password" aria-label="Password" v-model="password" />
                    <ErrorMessage :errorMessage="storeError.fieldMessage('password')"></ErrorMessage>
                </div>

                <div class="flex items-center justify-between mt-4">
                    <button
                        type="submit"
                        class="px-6 py-2 text-sm font-medium tracking-wide text-white capitalize transition-colors duration-300 transform bg-blue-500 rounded-lg hover:bg-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-50">
                        Sign In
                    </button>
                </div>
            </form>
        </div>

        <div class="flex items-center justify-center py-4 text-center bg-gray-50 dark:bg-gray-700">
            <span class="text-sm text-gray-600 dark:text-gray-200">Don't have an account? </span>

            <RouterLink to="/register" class="mx-2 text-sm font-bold text-blue-500 dark:text-blue-400 hover:underline">Register</RouterLink>
        </div>
    </div>
</template>