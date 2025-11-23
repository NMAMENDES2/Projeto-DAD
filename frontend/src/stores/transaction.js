import { defineStore } from 'pinia'
import axios from 'axios'
import { ref } from 'vue'
import { useErrorStore } from '@/stores/error'
import { useAuthStore } from '@/stores/auth'

export const useTransactionStore = defineStore('transaction', () => {
  const storeError = useErrorStore()
  const authStore = useAuthStore()
  
  const transactions = ref([])
  const loading = ref(false)

async function purchaseCoins(purchaseData) {
  storeError.resetMessages()
  loading.value = true
  
  console.log('Sending to API:', purchaseData) // Debug log
  
  try {
    const response = await axios.post('coins/purchase', purchaseData)
    
    console.log('API Response:', response.data) // Debug log
    
    // Update user's coin balance after successful purchase
    if (authStore.user) {
      await authStore.restoreToken()
    }
    
    return response.data
  } catch (e) {
    console.error('Purchase API Error:', e.response?.data) // Debug log
    
    storeError.setErrorMessages(
      e.response?.data?.message || 'Purchase failed.',
      e.response?.data?.errors || [],
      e.response?.status || 500,
      'Purchase Error!'
    )
    throw e
  } finally {
    loading.value = false
  }
}

  async function fetchTransactions() {
    loading.value = true
    
    try {
      const response = await axios.get('coins/transactions')
      transactions.value = response.data.data || response.data
      return transactions.value
    } catch (e) {
      storeError.setErrorMessages(
        e.response?.data?.message || 'Failed to fetch transactions.',
        e.response?.data?.errors || [],
        e.response?.status || 500,
        'Transaction Error!'
      )
      return []
    } finally {
      loading.value = false
    }
  }

  return {
    transactions,
    loading,
    purchaseCoins,
    fetchTransactions
  }
})