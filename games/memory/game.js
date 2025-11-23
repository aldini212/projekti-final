class MemoryGame {
  constructor() {
    this.gameBoard = document.getElementById("game-board")
    this.movesDisplay = document.getElementById("moves")
    this.timerDisplay = document.getElementById("timer")
    this.matchedDisplay = document.getElementById("matched")
    this.restartButton = document.getElementById("restart")
    this.menuButton = document.getElementById("menu")
    this.modal = document.getElementById("modal")

    // Game state
    this.cards = []
    this.hasFlippedCard = false
    this.lockBoard = false
    this.firstCard = null
    this.secondCard = null
    this.moves = 0
    this.timer = 0
    this.timerInterval = null
    this.matchedPairs = 0

    this.cardEmojis = ["🎨", "🎭", "🎪", "🎯", "🎲", "🎸", "🎤", "🎬"]

    // Event listeners
    this.restartButton.addEventListener("click", () => this.initGame())
    this.menuButton.addEventListener("click", () => (window.location.href = "../../index.php"))

    // Start the game
    this.initGame()
  }

  initGame() {
    // Reset state
    this.moves = 0
    this.timer = 0
    this.matchedPairs = 0
    this.hasFlippedCard = false
    this.lockBoard = false
    this.firstCard = null
    this.secondCard = null
    clearInterval(this.timerInterval)

    // Update displays
    this.updateMoves()
    this.updateTimer()
    this.updateMatched()

    // Clear board
    this.gameBoard.innerHTML = ""
    this.cards = []

    // Create shuffled pairs
    const gameCards = []
    this.cardEmojis.forEach((emoji) => {
      gameCards.push(emoji, emoji)
    })

    const shuffledCards = this.shuffleArray([...gameCards])

    // Create card elements
    shuffledCards.forEach((emoji, index) => {
      this.createCard(emoji, index)
    })

    // Hide modal
    this.modal.classList.remove("show")

    // Start timer
    this.startTimer()
  }

  createCard(emoji, index) {
    const card = document.createElement("div")
    card.className = "card"
    card.dataset.emoji = emoji
    card.dataset.index = index

    const front = document.createElement("div")
    front.className = "card-face card-front"
    front.textContent = emoji

    const back = document.createElement("div")
    back.className = "card-face card-back"
    back.textContent = "?"

    card.appendChild(front)
    card.appendChild(back)

    card.addEventListener("click", () => this.flipCard(card))
    this.gameBoard.appendChild(card)
    this.cards.push(card)
  }

  shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1))
      ;[array[i], array[j]] = [array[j], array[i]]
    }
    return array
  }

  flipCard(card) {
    if (this.lockBoard || card === this.firstCard || card.classList.contains("matched")) {
      return
    }

    card.classList.add("flipped")

    if (!this.hasFlippedCard) {
      this.hasFlippedCard = true
      this.firstCard = card
      return
    }

    this.secondCard = card
    this.lockBoard = true
    this.moves++
    this.updateMoves()

    this.checkForMatch()
  }

  checkForMatch() {
    const isMatch = this.firstCard.dataset.emoji === this.secondCard.dataset.emoji

    if (isMatch) {
      this.firstCard.classList.add("matched")
      this.secondCard.classList.add("matched")
      this.matchedPairs++
      this.updateMatched()
      this.resetBoard()

      if (this.matchedPairs === this.cardEmojis.length) {
        this.gameOver()
      }
    } else {
      setTimeout(() => {
        this.firstCard.classList.remove("flipped")
        this.secondCard.classList.remove("flipped")
        this.resetBoard()
      }, 1000)
    }
  }

  resetBoard() {
    ;[this.hasFlippedCard, this.lockBoard] = [false, false]
    ;[this.firstCard, this.secondCard] = [null, null]
  }

  startTimer() {
    clearInterval(this.timerInterval)
    this.timerInterval = setInterval(() => {
      this.timer++
      this.updateTimer()
    }, 1000)
  }

  updateMoves() {
    this.movesDisplay.textContent = this.moves
  }

  updateTimer() {
    this.timerDisplay.textContent = this.timer + "s"
  }

  updateMatched() {
    this.matchedDisplay.textContent = `${this.matchedPairs}/${this.cardEmojis.length}`
  }

  gameOver() {
    clearInterval(this.timerInterval)
    document.getElementById("final-moves").textContent = this.moves
    document.getElementById("final-timer").textContent = this.timer
    this.modal.classList.add("show")
  }
}

// Initialize game when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  new MemoryGame()
})
