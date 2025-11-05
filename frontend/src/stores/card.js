export const cards = []

const suits = ['c', 'o', 'e', 'p']  // Copas, Ouros, Espadas, Paus
const ranks = ['1', '2', '3', '4', '5', '6', '7', '11', '12', '13']  // Excluding 8, 9, and 10

suits.forEach(suit => {
  ranks.forEach(rank => {
    cards.push({
      image: `../public/cards/${suit}${rank}.png`, // image path
      title: `${rank} de ${suit === 'c' ? 'Copas' : suit === 'o' ? 'Ouros' : suit === 'e' ? 'Espadas' : 'Paus'}`
    })
  })
})

cards.push({
    image: '../public/cards/semFace.png',
    title: 'Carta virada para baixo'
})