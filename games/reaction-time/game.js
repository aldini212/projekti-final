class ReactionTimeGame {
  constructor() {
    // DOM elements
    this.startButton = document.getElementById("startButton")
    this.gameArea = document.getElementById("gameArea")
    this.resultText = document.getElementById("resultText")
    this.resultDisplay = document.getElementById("resultDisplay")
    this.difficultyIndicator = document.getElementById("difficultyIndicator")
    this.modal = document.getElementById("modal")
    this.menuButton = document.getElementById("menu")
    this.playAgainBtn = document.getElementById("playAgain")
    this.homeBtn = document.getElementById("homeBtn")

    // Display elements
    this.phaseDisplay = document.getElementById("phase")
    this.lastTimeDisplay = document.getElementById("lastTime")
    this.avgTimeDisplay = document.getElementById("avgTime")
    this.bestTimeDisplay = document.getElementById("bestTime")

    // Game state
    this.gameState = {
      isRunning: false,
      startTime: 0,
      reactionTimes: [],
      currentPhase: 0,
      totalPhases: 5,
      bestTime: Number.POSITIVE_INFINITY,
      timeout: null,
      squareSize: 100,
    }

    // Event listeners
    this.startButton.addEventListener("click", () => this.startChallenge())
    this.menuButton.addEventListener("click", () => this.goHome())
    this.homeBtn.addEventListener("click", () => this.goHome())
    this.playAgainBtn.addEventListener("click", () => this.startChallenge())
  }

  goHome() {
    window.location.href = "../../index.php"
  }

  startChallenge() {
    this.gameState.isRunning = true
    this.gameState.reactionTimes = []
    this.gameState.currentPhase = 0
    this.gameState.bestTime = Number.POSITIVE_INFINITY

    this.startButton.disabled = true
    this.startButton.textContent = "Challenge Running..."
    this.modal.classList.remove("show")
    this.resultDisplay.style.display = "none"

    // Start first phase
    this.startPhase()
  }

  startPhase() {
    this.gameState.currentPhase++
    this.phaseDisplay.textContent = `${this.gameState.currentPhase}/${this.gameState.totalPhases}`

    // Update square size based on phase (harder = smaller)
    this.gameState.squareSize = 100 - this.gameState.currentPhase * 8

    // Update difficulty indicator
    const difficulty = ["Easy", "Normal", "Hard", "Insane", "Extreme"]
    this.difficultyIndicator.textContent = `Phase ${this.gameState.currentPhase} - ${difficulty[this.gameState.currentPhase - 1]}`

    // Remove old square
    const oldSquare = document.getElementById("reactSquare")
    if (oldSquare) oldSquare.remove()

    // Random delay before showing square (varies by phase)
    const minDelay = 500 + this.gameState.currentPhase * 100
    const maxDelay = 2000 + this.gameState.currentPhase * 200
    const delay = minDelay + Math.random() * (maxDelay - minDelay)

    this.gameState.timeout = setTimeout(() => {
      if (!this.gameState.isRunning) return

      this.showSquare()

      // Auto-miss after time limit (varies by phase)
      const timeLimit = 3000 - this.gameState.currentPhase * 200
      this.gameState.timeout = setTimeout(() => {
        if (this.gameState.isRunning) {
          this.handleMiss()
        }
      }, timeLimit)
    }, delay)
  }

  showSquare() {
    const square = document.createElement("div")
    square.id = "reactSquare"
    square.className = "reaction-square"

    // Random position
    const maxX = this.gameArea.offsetWidth - this.gameState.squareSize
    const maxY = this.gameArea.offsetHeight - this.gameState.squareSize
    const x = Math.random() * maxX
    const y = Math.random() * maxY

    square.style.width = this.gameState.squareSize + "px"
    square.style.height = this.gameState.squareSize + "px"
    square.style.left = x + "px"
    square.style.top = y + "px"

    // Add animation based on phase
    const animations = ["glow", "pulse", "bounce", "flicker", "shimmer"]
    square.classList.add(animations[this.gameState.currentPhase - 1])

    square.addEventListener("click", () => this.handleClick())
    this.gameArea.appendChild(square)

    // Start counting time
    this.gameState.startTime = performance.now()
  }

  handleClick() {
    if (!this.gameState.isRunning) return

    clearTimeout(this.gameState.timeout)

    const reactionTime = Math.round(performance.now() - this.gameState.startTime)
    this.gameState.reactionTimes.push(reactionTime)

    // Update best time
    if (reactionTime < this.gameState.bestTime) {
      this.gameState.bestTime = reactionTime
    }

    // Update displays
    this.lastTimeDisplay.textContent = reactionTime + " ms"
    this.bestTimeDisplay.textContent = this.gameState.bestTime + " ms"

    // Calculate average
    const avg = Math.round(
      this.gameState.reactionTimes.reduce((a, b) => a + b, 0) / this.gameState.reactionTimes.length,
    )
    this.avgTimeDisplay.textContent = avg + " ms"

    // Color feedback based on reaction time
    const square = document.getElementById("reactSquare")
    if (square) {
      if (reactionTime < 200) {
        this.resultText.textContent = "🔥 Insane! " + reactionTime + " ms"
        this.resultText.style.color = "#ff0080"
        square.style.borderColor = "#ff0080"
      } else if (reactionTime < 300) {
        this.resultText.textContent = "⚡ Excellent! " + reactionTime + " ms"
        this.resultText.style.color = "#00ff88"
        square.style.borderColor = "#00ff88"
      } else if (reactionTime < 400) {
        this.resultText.textContent = "✓ Good! " + reactionTime + " ms"
        this.resultText.style.color = "#00ccff"
        square.style.borderColor = "#00ccff"
      } else {
        this.resultText.textContent = "✗ Slow! " + reactionTime + " ms"
        this.resultText.style.color = "#ffaa00"
        square.style.borderColor = "#ffaa00"
      }
      this.resultDisplay.style.display = "block"

      // Disappearing effect
      square.classList.add("disappear")
    }

    // Check if challenge complete
    if (this.gameState.currentPhase >= this.gameState.totalPhases) {
      setTimeout(() => this.endChallenge(), 800)
    } else {
      setTimeout(() => this.startPhase(), 1000)
    }
  }

  handleMiss() {
    this.resultText.textContent = "✗ Too Slow! Try Again"
    this.resultText.style.color = "#ff6b6b"
    this.resultDisplay.style.display = "block"

    const square = document.getElementById("reactSquare")
    if (square) {
      square.style.borderColor = "#ff6b6b"
      square.classList.add("miss")
    }

    if (this.gameState.currentPhase >= this.gameState.totalPhases) {
      setTimeout(() => this.endChallenge(), 1000)
    } else {
      setTimeout(() => this.startPhase(), 1200)
    }
  }

  endChallenge() {
    this.gameState.isRunning = false
    clearTimeout(this.gameState.timeout)

    const avg = Math.round(
      this.gameState.reactionTimes.reduce((a, b) => a + b, 0) / this.gameState.reactionTimes.length,
    )

    document.getElementById("final-avg").textContent = avg
    document.getElementById("final-best").textContent = this.gameState.bestTime
    document.getElementById("final-phases").textContent = this.gameState.totalPhases

    this.startButton.disabled = false
    this.startButton.textContent = "Start Challenge"

    this.modal.classList.add("show")
  }
}

// Initialize game when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  new ReactionTimeGame()
})
