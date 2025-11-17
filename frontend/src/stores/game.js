import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useGameStore = defineStore('game', () => {
  const cards = []
  const suits = ['c', 'o', 'e', 'p']
  const ranks = ['1','2','3','4','5','6','7','11','12','13']

  const cardPoints = { 1: 11, 7: 10, 13: 4, 11: 3, 12: 2 }

  suits.forEach(suit => {
    ranks.forEach(rank => {
      cards.push({
        image: `/cards/${suit}${rank}.png`,
        title: `${rank} de ${suit === 'c' ? 'Copas' : suit === 'o' ? 'Ouros' : suit === 'e' ? 'Espadas' : 'Paus'}`,
        suit,
        rank: Number(rank),
        points: cardPoints[rank] || 0
      })
    })
  })

  const faceDownCard = { image: '/cards/semFace.png', title: 'Carta virada para baixo' }

  const trump = ref(suits[Math.floor(Math.random() * suits.length)])
  const player1 = ref([])
  const player2 = ref([])
  const remainingDeck = ref([])
  const board = ref([]) 
  const currentTurn = ref(1)
  const player1Points = ref(0)
  const player2Points = ref(0)
  const player1Won = ref([])
  const player2Won = ref([])
  const lastTrickWinner = ref(null)
  const winner = ref(null)

  const deal = (num) => {
    const shuffled = [...cards].sort(() => 0.5 - Math.random())
    player1.value = shuffled.slice(0,num)
    player2.value = shuffled.slice(num,num*2)
    remainingDeck.value = shuffled.slice(num*2)
    board.value = []
    player1Won.value = []
    player2Won.value = []
    lastTrickWinner.value = null
    player1Points.value = 0
    player2Points.value = 0
    winner.value = null
  }

  const playCard = (player, index) => {
    if (currentTurn.value !== player) return { success: false, message: "Not your turn" }

    let card
    if (player === 1) {
      card = player1.value.splice(index,1)[0]
      currentTurn.value = 2
    } else {
      card = player2.value.splice(index,1)[0]
      currentTurn.value = 1
    }

    if (card) board.value.push({...card, playedBy: player})

    if (board.value.length === 2) {
      evaluateTrick()
      checkGameWinner()
    }

    return { success: true }
  }

  const evaluateTrick = () => {
    const [c1, c2] = board.value
    let winnerPlayer

    if (c1.suit === c2.suit) {
      winnerPlayer = c1.rank > c2.rank ? c1.playedBy : c2.playedBy
    } else if (c2.suit === trump.value) {
      winnerPlayer = c2.playedBy
    } else if (c1.suit === trump.value) {
      winnerPlayer = c1.playedBy
    } else {
      winnerPlayer = c1.playedBy
    }

    lastTrickWinner.value = winnerPlayer
    const trickPoints = c1.points + c2.points

    if (winnerPlayer === 1) {
      player1Points.value += trickPoints
      player1Won.value.push(c1,c2)
    } else {
      player2Points.value += trickPoints
      player2Won.value.push(c1,c2)
    }

    // Draw cards from stock if available
    if (remainingDeck.value.length > 0) {
      if (winnerPlayer === 1) {
        player1.value.push(remainingDeck.value.shift())
        player2.value.push(remainingDeck.value.shift())
      } else {
        player2.value.push(remainingDeck.value.shift())
        player1.value.push(remainingDeck.value.shift())
      }
    }

    board.value = []
  }

  const checkGameWinner = () => {
    if (player1Points.value >= 61) winner.value = 1
    else if (player2Points.value >= 61) winner.value = 2
  }

  return {
    cards,
    faceDownCard,
    player1,
    player2,
    remainingDeck,
    board,
    currentTurn,
    player1Points,
    player2Points,
    player1Won,
    player2Won,
    lastTrickWinner,
    winner,
    trump,
    deal,
    playCard,
    evaluateTrick,
    checkGameWinner
  }
})
