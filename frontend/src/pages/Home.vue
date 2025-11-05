<script setup>
    import { cards } from '@/stores/card';
    import Card from '@/components/Card.vue';
</script>

<template>
  <div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-center mb-8">Card Gallery</h1>
    
    <!-- Displaying the cards in a responsive grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <!-- Loop through the cards data and pass the image and title as props to the Card component -->
      <Card
        v-for="(card, index) in cards"
        :key="index"
        :image="card.image"
        :title="card.title"
      />
    </div>
  </div>
</template>