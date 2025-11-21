<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Comprar Brain Coins</h2>
        <button @click="$emit('close')" class="close-btn">&times;</button>
      </div>

      <div class="modal-body">
        <p class="info-text">1€ = 10 🪙 Brain Coins</p>

        <form @submit.prevent="handleSubmit">
          <!-- Valor em euros -->
          <div class="form-group">
            <label>Valor (€)</label>
            <input 
              v-model.number="form.euros" 
              type="number" 
              min="1" 
              max="99" 
              required
              placeholder="Insira o valor entre 1€ e 99€"
            />
            <span class="coins-preview">
              = {{ form.euros * 10 }} Brain Coins 🪙
            </span>
          </div>

          <!-- Tipo de pagamento -->
          <div class="form-group">
            <label>Método de Pagamento</label>
            <select v-model="form.payment_type" required>
              <option value="">Selecione...</option>
              <option value="MBWAY">MB WAY</option>
              <option value="PAYPAL">PayPal</option>
              <option value="IBAN">IBAN</option>
              <option value="MB">Multibanco</option>
              <option value="VISA">VISA</option>
            </select>
          </div>

          <!-- Referência de pagamento -->
          <div class="form-group">
            <label>Referência</label>
            <input 
              v-model="form.payment_reference" 
              type="text" 
              required
              :placeholder="getPlaceholder()"
            />
            <small class="helper-text">{{ getHelperText() }}</small>
          </div>

          <!-- Erros -->
          <div v-if="error" class="error-message">
            {{ error }}
          </div>

          <!-- Botões -->
          <div class="modal-actions">
            <button type="button" @click="$emit('close')" class="btn-secondary">
              Cancelar
            </button>
            <button type="submit" :disabled="loading" class="btn-primary">
              {{ loading ? 'A processar...' : 'Comprar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const emit = defineEmits(['close', 'purchase-success'])

const form = reactive({
  euros: 5,
  payment_type: '',
  payment_reference: ''
})

const loading = ref(false)
const error = ref('')

const getPlaceholder = () => {
  const placeholders = {
    MBWAY: '912345678',
    PAYPAL: 'email@example.com',
    IBAN: 'PT50000000000000000000000',
    MB: '12345-123456789',
    VISA: '4111111111111111'
  }
  return placeholders[form.payment_type] || 'Insira a referência'
}

const getHelperText = () => {
  const helpers = {
    MBWAY: 'Número de telefone (9 dígitos)',
    PAYPAL: 'Email da conta PayPal',
    IBAN: 'IBAN com 25 caracteres',
    MB: 'Entidade-Referência',
    VISA: 'Número do cartão VISA (16 dígitos)'
  }
  return helpers[form.payment_type] || ''
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''

  try {
    const token = localStorage.getItem('token')
    const response = await fetch('http://localhost:8000/api/coins/purchase', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(form)
    })

    const data = await response.json()

    if (response.ok) {
      emit('purchase-success', data)
    } else {
      error.value = data.message || 'Erro ao processar compra'
    }
  } catch (err) {
    error.value = 'Erro de conexão com o servidor'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 16px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 2rem;
  cursor: pointer;
  color: #718096;
  line-height: 1;
}

.modal-body {
  padding: 1.5rem;
}

.info-text {
  background: #edf2f7;
  padding: 0.75rem;
  border-radius: 8px;
  text-align: center;
  margin-bottom: 1.5rem;
  font-weight: 600;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2d3748;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
}

.coins-preview {
  display: block;
  margin-top: 0.5rem;
  color: #667eea;
  font-weight: 600;
}

.helper-text {
  display: block;
  margin-top: 0.25rem;
  color: #718096;
  font-size: 0.85rem;
}

.error-message {
  background: #fff5f5;
  color: #c53030;
  padding: 0.75rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

.btn-secondary, .btn-primary {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary {
  background: #e2e8f0;
  color: #4a5568;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
