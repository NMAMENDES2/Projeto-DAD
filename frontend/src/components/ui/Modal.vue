<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 z-40 flex justify-center items-center" @click.self="close">
    <div class="bg-white rounded-lg shadow-xl p-6 z-50 w-full max-w-md">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">{{ title }}</h3>
        <button @click="close" class="text-gray-500 hover:text-gray-800">&times;</button>
      </div>
      <div>
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    required: true,
  },
  title: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close'])

const close = () => {
  emit('close')
}
</script>
