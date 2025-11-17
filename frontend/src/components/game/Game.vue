<template>
  <div class="min-h-screen bg-neutral-100 flex flex-col items-center p-6">
    <h1 class="text-3xl font-bold mb-6">Bisca - Game</h1>

    <!-- Deal Button -->
    <Button @click="dealCards" variant="default" class="mb-12">
      Deal Cards
    </Button>

    <!-- Player 1 Hand -->
    <div class="w-full max-w-4xl mb-12">
      <h2 class="text-xl font-semibold mb-2 text-center">Player 1</h2>
      <div class="flex justify-center space-x-4">
        <Card
          v-for="(card, index) in game.player1"
          :key="index"
          class="w-24 h-32 flex items-center justify-center shadow-lg rounded-lg cursor-pointer hover:scale-105 transition-transform"
          @click="playCard(1, index)"
        >
          <CardContent class="p-0 flex items-center justify-center">
            <img :src="card.image" :alt="card.title" class="w-auto h-full object-contain" />
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Board -->
    <div class="w-full max-w-4xl mb-12">
      <h2 class="text-xl font-semibold mb-2 text-center">Board</h2>
      <div class="flex justify-center space-x-6">
        <Card
          v-for="(card, index) in game.board"
          :key="index"
          class="w-24 h-32 flex items-center justify-center shadow-lg rounded-lg"
        >
          <CardContent class="p-0 flex items-center justify-center">
            <img :src="card.image" :alt="card.title" class="w-auto h-full object-contain" />
          </CardContent>
        </Card>
        <div v-if="game.board.length === 0" class="w-24 h-32 flex items-center justify-center border-2 border-dashed rounded-lg text-gray-400">
          Empty
        </div>
      </div>
    </div>

    <!-- Player 2 Hand -->
    <div class="w-full max-w-4xl">
      <h2 class="text-xl font-semibold mb-2 text-center">Player 2</h2>
      <div class="flex justify-center space-x-4">
        <Card
          v-for="(card, index) in game.player2"
          :key="index"
          class="w-24 h-32 flex items-center justify-center shadow-lg rounded-lg cursor-pointer hover:scale-105 transition-transform"
          @click="playCard(2, index)"
        >
          <CardContent class="p-0 flex items-center justify-center">
            <img :src="card.image" :alt="card.title" class="w-auto h-full object-contain" />
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useGameStore } from "@/stores/game"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"

const game = useGameStore()

const dealCards = () => {
  game.deal(3) // deals 3 cards to both players
}

const playCard = (player, index) => {
  game.playCard(player, index)
}
</script>
