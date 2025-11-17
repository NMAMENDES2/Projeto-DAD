import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useGameStore = defineStore('game', () => {
    const cards = []
    const suits = ['c', 'o', 'e', 'p']
    const ranks = ['1', '2', '3', '4', '5', '6', '7', '11', '12', '13']

    suits.forEach(suit => {
        ranks.forEach(rank => {
            cards.push({
                image: `/cards/${suit}${rank}.png`,
                title: `${rank} de ${suit === 'c' ? 'Copas' : suit === 'o' ? 'Ouros' : suit === 'e' ? 'Espadas' : 'Paus'}`
            })
        })
    })

    const faceDownCard = { image: '/cards/semFace.png', title: 'Carta virada para baixo' }

    const player1 = ref([])
    const player2 = ref([])
    const remainingDeck = ref([])
    const board = ref([]) 

    const deal = (num) => {
        const shuffled = [...cards].sort(() => 0.5 - Math.random())

        player1.value = shuffled.slice(0, num)
        player2.value = shuffled.slice(num, num * 2)
        remainingDeck.value = shuffled.slice(num * 2)

        board.value = []

        console.log('Player 1:', player1.value)
        console.log('Player 2:', player2.value)
        console.log('Remaining Deck:', remainingDeck.value)
    }

    const playCard = (player, index) => {
    let card
    if (player === 1) {
      card = player1.value.splice(index, 1)[0]
    } else if (player === 2) {
      card = player2.value.splice(index, 1)[0]
    }
    if (card) board.value.push(card)
  }

    return {
        cards,
        faceDownCard,
        player1,
        player2,
        remainingDeck,
        deal,
        playCard,
        board,
    }
})
