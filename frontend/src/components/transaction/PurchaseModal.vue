<script setup>
import { ref, computed } from 'vue'
import { useTransactionStore } from '@/stores/transaction'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'
import { X } from 'lucide-react'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['close', 'purchase-success'])

const transactionStore = useTransactionStore()
const authStore = useAuthStore()

const form = ref({
  euros: '',
  type: 'MBWAY',
  reference: ''
})

const error = ref('')

const coinsAmount = computed(() => {
  const euros = parseFloat(form.value.euros) || 0
  return Math.floor(euros * 10)
})

const closeModal = () => {
  form.value = {
    euros: '',
    type: 'MBWAY',
    reference: ''
  }
  error.value = ''
  emit('close')
}

const handleSubmit = async () => {
  error.value = ''

  console.log('Button clicked!') // Debug
  console.log('Form data:', form.value)

  if (!form.value.euros || parseFloat(form.value.euros) <= 0) {
    error.value = 'Please enter a valid amount'
    return
  }

  if (!form.value.reference || form.value.reference.trim() === '') {
    error.value = 'Please enter a payment reference'
    return
  }

  try {
    const data = await transactionStore.purchaseCoins({
      value: parseInt(form.value.euros),
      type: form.value.type,
      reference: form.value.reference
    })

    console.log(data)

    emit('purchase-success', data)
    closeModal()
  } catch (err) {
    error.value = err.response?.data?.message || 'Error processing purchase'
  }
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50" @click.self="closeModal">
    <div class="bg-white rounded-2xl w-[90%] max-w-[500px] shadow-2xl" @click.stop>
      <!-- Header -->
      <div class="flex justify-between items-center p-6 border-b">
        <h2 class="text-2xl font-semibold text-gray-900">Purchase Brain Coins</h2>
        <button 
          @click="closeModal" 
          class="text-gray-400 hover:text-gray-600 transition-colors"
          type="button"
        >
          <X :size="28" />
        </button>
      </div>

      <!-- Body -->
      <div class="p-6 space-y-6">
        <!-- Info Banner -->
        <div class="bg-gray-100 rounded-lg p-3 text-center">
          <p class="font-semibold text-gray-900">1 Euro = 10 Brain Coins</p>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 text-red-600 rounded-lg p-3">
          {{ error }}
        </div>

        <!-- Form Fields -->
        <div class="space-y-4">
          <!-- Amount Input -->
          <div class="space-y-2">
            <label for="euros" class="block text-base font-semibold text-gray-900">
              Amount (Euros)
            </label>
            <input
              id="euros"
              v-model="form.euros"
              type="number"
              placeholder="10.00"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg text-base focus:outline-none focus:border-purple-500 transition-colors"
            />
            <span v-if="form.euros" class="block text-sm font-semibold text-purple-600">
              = {{ coinsAmount }} Brain Coins
            </span>
          </div>

          <!-- Payment Type Select -->
          <div class="space-y-2">
            <label for="type" class="block text-base font-semibold text-gray-900">
              Payment Method
            </label>
            <select
              id="type"
              v-model="form.type"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg text-base focus:outline-none focus:border-purple-500 transition-colors"
            >
              <option value="MBWAY">MB WAY</option>
              <option value="PAYPAL">PayPal</option>
              <option value="IBAN">IBAN</option>
              <option value="MB">Multibanco</option>
              <option value="VISA">Visa</option>
            </select>
          </div>

          <!-- Reference Input -->
          <div class="space-y-2">
            <label for="reference" class="block text-base font-semibold text-gray-900">
              Payment Reference
            </label>
            <input
              id="reference"
              v-model="form.reference"
              type="text"
              placeholder="Enter your payment reference"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg text-base focus:outline-none focus:border-purple-500 transition-colors"
            />
            <span class="block text-sm text-gray-500">
              This will be used to confirm your payment
            </span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 justify-end pt-2">
          <Button
            variant="outline"
            @click="closeModal"
            :disabled="transactionStore.loading"
            class="px-6 py-2"
          >
            Cancel
          </Button>
          <Button
            @click="handleSubmit"
            :disabled="transactionStore.loading"
            class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white"
          >
            {{ transactionStore.loading ? 'Processing...' : 'Purchase Coins' }}
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>