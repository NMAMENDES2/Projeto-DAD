<template>
  <div class="min-h-screen bg-neutral-100 flex flex-col items-center p-6 overflow-hidden">

    <Modal :show="showAuthModal" @close="showAuthModal = false" :title="authMode === 'login' ? 'Login' : 'Register'">
      <div v-if="authMode === 'login'">
        <Login @success="showAuthModal = false" />
        <p class="text-center mt-4">
          Don't have an account? 
          <button @click="authMode = 'register'" class="text-blue-500 hover:underline">Register</button>
        </p>
      </div>
      <div v-else>
        <Register @success="showAuthModal = false" />
        <p class="text-center mt-4">
          Already have an account? 
          <button @click="authMode = 'login'" class="text-blue-500 hover:underline">Login</button>
        </p>
      </div>
    </Modal>

    <div class="w-full">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-3xl font-bold">Bisca - Game vs Bot</h1>
        
        <!-- Updated authentication section -->
        <div v-if="authStore.authStatus === 'loading'" class="flex items-center gap-4">
          <span class="text-gray-500">Loading...</span>
        </div>
        <div v-else-if="authStore.isAuthenticated" class="flex items-center gap-4">
          <span>Welcome, {{ authStore.userName }}</span>
          <Button @click="authStore.logout()" variant="destructive">Logout</Button>
        </div>
        <div v-else>
          <Button @click="showAuthModal = true">Login / Register</Button>
        </div>
      </div>

      <div v-if="warning" class="mb-4 text-red-600 font-semibold">
        {{ warning }}
      </div>

      <!-- Game Mode Selection & Deal Button -->
      <div class="flex gap-4 mb-8 justify-center">
        <Button 
          @click="dealCards(3)" 
          variant="outline"
          :disabled="isGameActive"
        >
          Deal Bisca de 3
        </Button>
        <Button 
          @click="dealCards(9)" 
          variant="default"
          :disabled="isGameActive"
        >
          Deal Bisca de 9
        </Button>
      </div>

      <!-- Score Display -->
      <div class="flex gap-8 mb-8 text-lg font-semibold justify-center">
        <div class="text-center">
          <div class="text-blue-600">You: {{ game.player1Points }} pts</div>
          <div class="text-sm text-gray-600">Marks: {{ game.player1Marks }}</div>
        </div>
        <div class="text-center">
          <div class="text-red-600">Bot: {{ game.player2Points }} pts</div>
          <div class="text-sm text-gray-600">Marks: {{ game.player2Marks }}</div>
        </div>
      </div>

      <!-- Winner/Turn Display -->
      <div v-if="game.winner" class="mb-4 text-2xl font-bold text-green-600 text-center">
        {{ game.winner === 1 ? 'You Win!' : game.winner === 2 ? 'Bot Wins!' : 'Draw!' }}
      </div>
      <div v-else-if="game.currentTurn" class="mb-4 text-lg font-semibold text-center">
        {{ game.currentTurn === 1 ? '🟢 Your Turn' : '🤖 Bot is thinking...' }}
      </div>

      <!-- Bot Hand (face down) -->
      <div class="w-full max-w-4xl mb-12 mx-auto">
        <h2 class="text-xl font-semibold mb-2 text-center">Bot (Player 2)</h2>
        <div class="flex justify-center space-x-4">
          <Card v-for="(card, index) in game.player2" :key="index"
            class="w-24 h-32 flex items-center justify-center shadow-lg rounded-lg">
            <CardContent class="p-0 flex items-center justify-center">
              <img :src="game.faceDownCard.image" alt="Hidden card" class="w-auto h-full object-contain" />
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Board -->
      <div class="w-full max-w-4xl mb-12 mx-auto">
        <h2 class="text-xl font-semibold mb-2 text-center">Board</h2>

        <div class="flex justify-center space-x-6">
          <Card v-for="(card, index) in game.board" :key="index"
            class="w-24 h-32 flex items-center justify-center shadow-lg rounded-lg">
            <CardContent class="p-0 flex items-center justify-center">
              <img :src="card.image" :alt="card.title" class="w-auto h-full object-contain" />
            </CardContent>
          </Card>

          <div v-if="game.board.length === 0"
            class="w-24 h-32 flex items-center justify-center border-2 border-dashed rounded-lg text-gray-400">
            Empty
          </div>
        </div>
      </div>

      <!-- Player Hand (You) -->
      <div class="w-full max-w-4xl mx-auto">
        <h2 class="text-xl font-semibold mb-2 text-center">Your Hand (Player 1)</h2>
        <div class="flex justify-center space-x-4">
          <Card v-for="(card, index) in game.player1" :key="index"
            class="w-24 h-32 flex items-center justify-center shadow-lg rounded-lg cursor-pointer hover:scale-105 transition-transform"
            :class="{ 'opacity-50': game.currentTurn !== 1 || game.waitingForDraw }"
            @click="playCard(1, index)">
            <CardContent class="p-0 flex items-center justify-center">
              <img :src="card.image" :alt="card.title" class="w-auto h-full object-contain" />
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- DECK + TRUMP -->
      <div class="absolute right-12 top-1/2 -translate-y-1/2 flex flex-col items-center space-y-4">

        <!-- FACE-DOWN CARD (Deck) -->
        <Card 
          v-if="game.remainingDeck.length > 0"
          class="w-24 h-32 flex items-center justify-center shadow-lg rounded-lg cursor-pointer hover:scale-105 transition-transform"
          :class="{ 'ring-4 ring-yellow-400': game.waitingForDraw && game.currentTurn === 1 }"
          @click="drawCard()"
        >
          <CardContent class="p-0 flex items-center justify-center">
            <img
              :src="game.faceDownCard.image"
              alt="Deck - Click to draw"
              class="w-auto h-full object-contain"
            />
          </CardContent>
        </Card>

        <!-- Remaining cards counter -->
        <div v-if="game.remainingDeck.length > 0" class="text-sm font-semibold text-gray-600">
          {{ game.remainingDeck.length }} cards
        </div>

        <!-- TRUMP CARD -->
        <Card
          v-if="game.trump"
          class="w-24 h-32 flex items-center justify-center shadow-lg rounded-lg"
        >
          <CardContent class="p-0 flex items-center justify-center">
            <img
              :src="game.trump.image"
              :alt="game.trump.title"
              class="w-auto h-full object-contain"
            />
          </CardContent>
        </Card>
        <div v-if="game.trump" class="text-xs font-semibold text-gray-600">
          Trump
        </div>

      </div>
    </div>
  </div>
</template>


<script setup>
import { useGameStore } from "@/stores/game"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { ref, computed, onMounted } from "vue"
import { useAuthStore } from "@/stores/auth"
import Login from "@/components/auth/Login.vue"
import Register from "@/components/auth/Register.vue"
import Modal from "@/components/ui/Modal.vue"

const authStore = useAuthStore()
const game = useGameStore()
const warning = ref("")

const showAuthModal = ref(false)
const authMode = ref('login') // 'login' or 'register'

onMounted(async () => {
  await authStore.restoreToken()
})

// Check if game is currently active
const isGameActive = computed(() => {
  return game.player1.length > 0 || game.player2.length > 0 || game.remainingDeck.length > 0
})

const dealCards = (num) => {
  game.deal(num)
}

const playCard = (player, index) => {
  const result = game.playCard(player, index)
  if (!result.success) {
    warning.value = result.message
    setTimeout(() => {
      warning.value = ""
    }, 2000);
  }
}

const drawCard = () => {
  // Only human player (player 1) can manually draw
  if (game.currentTurn !== 1) {
    warning.value = "Not your turn to draw!"
    setTimeout(() => {
      warning.value = ""
    }, 2000);
    return
  }
  
  const result = game.drawCards(1)
  if (!result.success) {
    warning.value = result.message
    setTimeout(() => {
      warning.value = ""
    }, 2000);
  }
}
</script>