<template>
  <div class="transactions-page">
    <!-- Header com saldo -->
    <div class="balance-section">
      <h1>As Minhas Transações</h1>
      <div class="balance-card">
        <div class="balance-info">
          <span class="balance-label">Saldo Atual</span>
          <span class="balance-amount">{{ balance }} 🪙</span>
        </div>
        <button @click="showPurchaseModal = true" class="btn-primary">
          Comprar Moedas
        </button>
      </div>
    </div>

    <!-- Histórico de transações -->
    <div class="transactions-section">
      <h2>Histórico</h2>
      
      <!-- Loading -->
      <div v-if="loading" class="loading">
        A carregar transações...
      </div>

      <!-- Lista vazia -->
      <div v-else-if="transactions.length === 0" class="empty-state">
        <p>Ainda não tens transações.</p>
      </div>

      <!-- Tabela de transações -->
      <div v-else class="transactions-table">
        <table>
          <thead>
            <tr>
              <th>Data</th>
              <th>Tipo</th>
              <th>Descrição</th>
              <th>Moedas</th>
              <th>Saldo</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="transaction in transactions" 
              :key="transaction.id"
              :class="transaction.coins > 0 ? 'positive' : 'negative'"
            >
              <td>{{ formatDate(transaction.transaction_datetime) }}</td>
              <td>
                <span class="badge" :class="getTypeClass(transaction.coin_transaction_type)">
                  {{ transaction.coin_transaction_type?.name || transaction.type }}
                </span>
              </td>
              <td>{{ getTransactionDescription(transaction) }}</td>
              <td class="coins-cell">
                {{ transaction.coins > 0 ? '+' : '' }}{{ transaction.coins }} 🪙
              </td>
              <td>{{ transaction.brain_coins || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal de compra -->
    <PurchaseModal 
      v-if="showPurchaseModal" 
      @close="showPurchaseModal = false"
      @purchase-success="handlePurchaseSuccess"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import PurchaseModal from '@/components/PurchaseModal.vue'

const router = useRouter()
const balance = ref(0)
const transactions = ref([])
const loading = ref(false)
const showPurchaseModal = ref(false)

// Carregar saldo
const fetchBalance = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await fetch('http://localhost:8000/api/coins/balance', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      balance.value = data.balance
    }
  } catch (error) {
    console.error('Erro ao carregar saldo:', error)
  }
}

// Carregar transações
const fetchTransactions = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await fetch('http://localhost:8000/api/coins/transactions', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      transactions.value = data.transactions
    }
  } catch (error) {
    console.error('Erro ao carregar transações:', error)
  } finally {
    loading.value = false
  }
}

// Formatar data
const formatDate = (datetime) => {
  const date = new Date(datetime)
  return date.toLocaleString('pt-PT', {
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
  if (name.includes('purchase') || name.includes('compra')) return 'badge-success'
  if (name.includes('bonus')) return 'badge-info'
  if (name.includes('fee') || name.includes('taxa')) return 'badge-warning'
  if (name.includes('win') || name.includes('vitória')) return 'badge-success'
  return 'badge-default'
}

// Descrição da transação
const getTransactionDescription = (transaction) => {
  const type = transaction.coin_transaction_type?.name || transaction.type
  
  if (type.includes('purchase') || type.includes('Coin purchase')) {
    return `Compra de ${transaction.coins} moedas`
  }
  if (type.includes('Game fee')) {
    return `Taxa de entrada no jogo #${transaction.game_id}`
  }
  if (type.includes('Game win')) {
    return `Vitória no jogo #${transaction.game_id}`
  }
  if (type.includes('Bonus')) {
    return 'Bónus de registo'
  }
  return type
}

// Após compra bem sucedida
const handlePurchaseSuccess = () => {
  showPurchaseModal.value = false
  fetchBalance()
  fetchTransactions()
}

onMounted(() => {
  fetchBalance()
  fetchTransactions()
})
</script>

<style scoped>
.transactions-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.balance-section {
  margin-bottom: 3rem;
}

.balance-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  padding: 2rem;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.balance-info {
  display: flex;
  flex-direction: column;
}

.balance-label {
  font-size: 0.9rem;
  opacity: 0.9;
  margin-bottom: 0.5rem;
}

.balance-amount {
  font-size: 2.5rem;
  font-weight: bold;
}

.btn-primary {
  background: white;
  color: #667eea;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s;
}

.btn-primary:hover {
  transform: scale(1.05);
}

.transactions-section h2 {
  margin-bottom: 1.5rem;
  color: #2d3748;
}

.transactions-table {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background: #f7fafc;
}

th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #4a5568;
  border-bottom: 2px solid #e2e8f0;
}

td {
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

tr.positive td {
  background: #f0fff4;
}

tr.negative td {
  background: #fff5f5;
}

.coins-cell {
  font-weight: 600;
  font-size: 1.1rem;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
}

.badge-success {
  background: #c6f6d5;
  color: #22543d;
}

.badge-warning {
  background: #feebc8;
  color: #744210;
}

.badge-info {
  background: #bee3f8;
  color: #2c5282;
}

.badge-default {
  background: #e2e8f0;
  color: #4a5568;
}

.loading, .empty-state {
  text-align: center;
  padding: 3rem;
  color: #718096;
}
</style>
