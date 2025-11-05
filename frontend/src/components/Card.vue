<script setup>

defineProps({
    image: String,
    title: String,
})
</script>

<template>
    <div class="max-w-sm rounded-lg overflow-hidden shadow-lg transition-transform transform hover:scale-105">
        <!-- A imagem agora vai preencher o card sem ser ampliada demais -->
        <img :src="image" :alt="title" class="w-full h-48 object-contain" />
        <div class="p-4">
            <!-- Título da carta -->
            <h3 class="text-xl font-semibold text-gray-800">{{ title }}</h3>
        </div>
    </div>
</template>