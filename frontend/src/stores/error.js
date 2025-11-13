import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useErrorStore = defineStore('error', () => {
  const fieldMessages = ref({})
  const generalMessage = ref('')

  function setMessage(field, message) {
    if (field === 'general' || field === 'register') {
      generalMessage.value = message
    } else {
      fieldMessages.value = { ...fieldMessages.value, [field]: message }
    }
  }

  function fieldMessage(field) {
    return fieldMessages.value[field] || ''
  }

  function setErrorMessages(message, errors = [], status = 500, title = '') {
    generalMessage.value = message || title || ''
    if (errors && typeof errors === 'object') {
      try {
        fieldMessages.value = { ...errors }
      } catch (e) {
      }
    }
  }

  function resetMessages() {
    fieldMessages.value = {}
    generalMessage.value = ''
  }

  return {
    fieldMessages,
    generalMessage,
    setMessage,
    fieldMessage,
    setErrorMessages,
    resetMessages,
  }
})
