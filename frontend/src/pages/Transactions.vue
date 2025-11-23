<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header com saldo -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-6 text-gray-900">My Transactions</h1>
      <div class="bg-linear-to-br from-purple-600 to-purple-800 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex justify-between items-center">
          <div class="flex flex-col">
            <span class="text-sm opacity-90 mb-2">Current Balance</span>
            <span class="text-5xl font-bold">{{ authStore.userBrainCoins }} 🪙</span>
          </div>
          <Button 
            @click="showPurchaseModal = true" 
            variant="secondary"
            size="lg"
            class="bg-white text-purple-700 hover:bg-gray-100 font-semibold"
          >
            Purchase Coins
          </Button>
        </div>
      </div>
    </div>

    <!-- Histórico de transações -->
    <div class="bg-white rounded-xl shadow-sm">
      <div class="p-6 border-b">
        <h2 class="text-2xl font-semibold text-gray-900">History</h2>
      </div>

      <!-- Loading -->
      <div v-if="transactionStore.loading" class="text-center py-12 text-gray-500">
        Loading transactions...
      </div>

      <!-- Lista vazia -->
      <div v-else-if="transactionStore.transactions.length === 0" class="text-center py-12 text-gray-500">
        <p>You don't have any transactions yet.</p>
      </div>

      <!-- Tabela de transações -->
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 border-b-2">Date</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 border-b-2">Type</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 border-b-2">Description</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 border-b-2">Coins</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 border-b-2">Balance</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="transaction in transactionStore.transactions" 
              :key="transaction.id"
              :class="[
                'border-b',
                transaction.coins > 0 ? 'bg-green-50' : 'bg-red-50'
              ]"
            >
              <td class="px-6 py-4">{{ formatDate(transaction.transaction_datetime) }}</td>
              <td class="px-6 py-4">
                <span 
                  :class="[
                    'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium',
                    getTypeClass(transaction.coin_transaction_type)
                  ]"
                >
                  {{ transaction.coin_transaction_type?.name || transaction.type }}
                </span>
              </td>
              <td class="px-6 py-4 text-gray-700">{{ getTransactionDescription(transaction) }}</td>
              <td class="px-6 py-4 font-semibold text-lg">
                <span :class="transaction.coins > 0 ? 'text-green-600' : 'text-red-600'">
                  {{ transaction.coins > 0 ? '+' : '' }}{{ transaction.coins }} 🪙
                </span>
              </td>
              <td class="px-6 py-4 text-gray-700">{{ transaction.brain_coins || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal de compra -->
    <PurchaseModal 
      :show="showPurchaseModal" 
      @close="showPurchaseModal = false"
      @purchase-success="handlePurchaseSuccess"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTransactionStore } from '@/stores/transaction'
import { Button } from '@/components/ui/button'
import PurchaseModal from '@/components/transaction/PurchaseModal.vue'

const authStore = useAuthStore()
const transactionStore = useTransactionStore()
const showPurchaseModal = ref(false)

// Formatar data
const formatDate = (datetime) => {
  const date = new Date(datetime)
  return date.toLocaleString('en-US', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Classe CSS para tipo de transação
const getTypeClass = (type) => {
  const name = type?.name?.toLowerCase() || ''
  if (name.includes('purchase') || name.includes('compra')) {
    return 'bg-green-100 text-green-800'
  }
  if (name.includes('bonus')) {
    return 'bg-blue-100 text-blue-800'
  }
  if (name.includes('fee') || name.includes('taxa')) {
    return 'bg-yellow-100 text-yellow-800'
  }
  if (name.includes('win') || name.includes('vitória')) {
    return 'bg-green-100 text-green-800'
  }
  return 'bg-gray-100 text-gray-800'
}

// Descrição da transação
const getTransactionDescription = (transaction) => {
  const type = transaction.coin_transaction_type?.name || transaction.type
  
  if (type.includes('purchase') || type.includes('Coin purchase')) {
    return `Purchase of ${transaction.coins} coins`
  }
  if (type.includes('Game fee')) {
    return `Game entry fee #${transaction.game_id}`
  }
  if (type.includes('Game win')) {
    return `Win in game #${transaction.game_id}`
  }
  if (type.includes('Bonus')) {
    return 'Registration bonus'
  }
  return type
}

// Após compra bem sucedida
const handlePurchaseSuccess = async () => {
  showPurchaseModal.value = false
  await authStore.restoreToken() // Refresh user balance
  await transactionStore.fetchTransactions() // Refresh transaction list
}

onMounted(async () => {
  await transactionStore.fetchTransactions()
})
</script>