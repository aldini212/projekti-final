class GameService {
  constructor(gameName) {
    this.gameName = gameName
    this.load()
  }

  load() {
    const saved = localStorage.getItem(`gamehub_${this.gameName}`)
    if (saved) {
      this.progress = JSON.parse(saved)
    } else {
      this.progress = {
        level: 1,
        xp: 0,
        totalXp: 0,
      }
    }
  }

  save() {
    localStorage.setItem(`gamehub_${this.gameName}`, JSON.stringify(this.progress))
  }

  addXp(amount) {
    this.progress.xp += amount
    this.progress.totalXp += amount

    const xpPerLevel = 100
    let levelsGained = 0

    while (this.progress.xp >= xpPerLevel) {
      this.progress.level++
      this.progress.xp -= xpPerLevel
      levelsGained++
    }

    this.save()
    return { levelsGained, totalXp: this.progress.totalXp }
  }

  getProgress() {
    const xpPerLevel = 100
    const xpToNextLevel = xpPerLevel - this.progress.xp
    const progressPercentage = Math.round((this.progress.xp / xpPerLevel) * 100)

    return {
      level: this.progress.level,
      xp: this.progress.xp,
      totalXp: this.progress.totalXp,
      xpToNextLevel,
      progressPercentage,
    }
  }
}

// Leaderboard functionality
class Leaderboard {
  constructor() {
    this.storageKey = 'wordScrambleLeaderboard';
    this.leaderboard = this.loadLeaderboard();
  }

  loadLeaderboard() {
    const saved = localStorage.getItem(this.storageKey);
    return saved ? JSON.parse(saved) : {
      easy: [],
      medium: [],
      hard: []
    };
  }

  saveLeaderboard() {
    localStorage.setItem(this.storageKey, JSON.stringify(this.leaderboard));
  }

  addScore(name, score, difficulty) {
    if (!this.leaderboard[difficulty]) return;
    
    this.leaderboard[difficulty].push({ name, score, date: new Date().toISOString() });
    // Sort by score (descending) and keep top 10
    this.leaderboard[difficulty].sort((a, b) => b.score - a.score).splice(10);
    this.saveLeaderboard();
  }

  getTopScores(difficulty, count = 10) {
    if (!this.leaderboard[difficulty]) return [];
    return this.leaderboard[difficulty].slice(0, count);
  }
}

// Game configuration
const DIFFICULTY_LEVELS = {
  easy: {
    timePerWord: 60,
    xpMultiplier: 1,
    words: [
      { word: "cat", hint: "A small domesticated carnivorous mammal" },
      { word: "dog", hint: "A domesticated carnivorous mammal" },
      { word: "sun", hint: "The star around which the earth orbits" },
      { word: "car", hint: "A road vehicle with an engine" },
      { word: "hat", hint: "A shaped covering for the head" },
      { word: "pen", hint: "An instrument for writing with ink" },
      { word: "cup", hint: "A small bowl-shaped container for drinking" },
      { word: "red", hint: "A color at the end of the spectrum" },
      { word: "big", hint: "Of considerable size or extent" },
      { word: "run", hint: "Move at a speed faster than walking" },
    ],
  },
  medium: {
    timePerWord: 45,
    xpMultiplier: 1.5,
    words: [
      { word: "javascript", hint: "A popular programming language for web development" },
      { word: "hangman", hint: "A word guessing game" },
      { word: "keyboard", hint: "Input device for computers" },
      { word: "elephant", hint: "A large mammal with a trunk" },
      { word: "mountain", hint: "A large natural elevation of the earth's surface" },
      { word: "guitar", hint: "A musical instrument with strings" },
      { word: "dolphin", hint: "A highly intelligent marine mammal" },
      { word: "library", hint: "A place where books are kept for reading" },
      { word: "rainbow", hint: "A spectrum of light appearing in the sky" },
      { word: "puzzle", hint: "A game designed to test ingenuity or knowledge" },
    ],
  },
  hard: {
    timePerWord: 30,
    xpMultiplier: 2,
    words: [
      { word: "extravaganza", hint: "A lavish or spectacular show or event" },
      { word: "kaleidoscope", hint: "A constantly changing pattern or sequence" },
      { word: "quintessential", hint: "Representing the most perfect or typical example" },
      { word: "xylophone", hint: "A musical instrument with wooden bars" },
      { word: "juxtaposition", hint: "The fact of two things being seen or placed close together" },
      { word: "zephyr", hint: "A gentle, mild breeze" },
      { word: "quixotic", hint: "Extremely idealistic; unrealistic and impractical" },
      { word: "ephemeral", hint: "Lasting for a very short time" },
      { word: "serendipity", hint: "The occurrence of events by chance in a happy or beneficial way" },
      { word: "ubiquitous", hint: "Present, appearing, or found everywhere" },
    ],
  },
}

document.addEventListener("DOMContentLoaded", () => {
  // DOM Elements
  const scrambledWordEl = document.getElementById("scrambled-word")
  const hintEl = document.getElementById("hint")
  const guessInput = document.getElementById("guess")
  const submitBtn = document.getElementById("submit")
  const restartBtn = document.getElementById("restart")
  const messageEl = document.getElementById("message")
  const scoreEl = document.getElementById("score")
  const timerEl = document.getElementById("timer")
  const correctEl = document.getElementById("correct")
  const incorrectEl = document.getElementById("incorrect")
  const levelEl = document.getElementById("level")
  const currentXpEl = document.getElementById("currentXp")
  const nextLevelXpEl = document.getElementById("nextLevelXp")
  const xpProgressEl = document.getElementById("xpProgress")
  const xpTextEl = document.getElementById("xpText")
  const difficultyButtons = document.querySelectorAll("[data-difficulty]")

  const wordScrambleService = new GameService("word-scramble")

  function updateXpUI() {
    const p = wordScrambleService.getProgress()
    if (levelEl) levelEl.textContent = p.level
    if (currentXpEl) currentXpEl.textContent = p.xp
    if (nextLevelXpEl) nextLevelXpEl.textContent = p.xpToNextLevel
    if (xpProgressEl) xpProgressEl.style.width = `${p.progressPercentage}%`
    if (xpTextEl) xpTextEl.textContent = `${p.progressPercentage}%`
  }

  updateXpUI()

  // Game state
  let currentWord = ""
  let score = 0
  let timeLeft = 60
  let timer
  let correctCount = 0
  let incorrectCount = 0
  let gameOver = false
  let currentDifficulty = "easy"
  let wordBank = [...DIFFICULTY_LEVELS[currentDifficulty].words]
  let currentRound = 0
  const TOTAL_ROUNDS = 10

  // Show message helper
  function showMessage(text, type) {
    messageEl.textContent = text
    messageEl.className = `message message-show ${type}`
    setTimeout(() => {
      messageEl.classList.remove("message-show")
    }, 3000)
  }

  // Scramble word function
  function scrambleWord(word) {
    const letters = word.split("")
    for (let i = letters.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1))
      ;[letters[i], letters[j]] = [letters[j], letters[i]]
    }
    return letters.join(" ").toUpperCase()
  }

  // Set difficulty
  function setDifficulty(difficulty) {
    if (!DIFFICULTY_LEVELS[difficulty]) return

    currentDifficulty = difficulty
    timeLeft = DIFFICULTY_LEVELS[difficulty].timePerWord
    wordBank = [...DIFFICULTY_LEVELS[difficulty].words]

    // Update active button styling
    difficultyButtons.forEach((btn) => {
      btn.classList.toggle("active", btn.dataset.difficulty === difficulty)
    })

    showMessage(`Difficulty: ${difficulty.toUpperCase()}`, "info")
    resetGame()
  }

  // Update timer display
  function updateTimer() {
    timeLeft--
    timerEl.textContent = timeLeft

    if (timeLeft <= 0) {
      clearInterval(timer)
      endGame()
    }
  }

  // Get new word
  function getNewWord() {
    currentRound++

    if (currentRound > TOTAL_ROUNDS) {
      endGame()
      return
    }

    if (wordBank.length === 0) {
      wordBank = [...DIFFICULTY_LEVELS[currentDifficulty].words]
    }

    const randomIndex = Math.floor(Math.random() * wordBank.length)
    currentWord = wordBank[randomIndex]
    wordBank.splice(randomIndex, 1)

    const scrambled = scrambleWord(currentWord.word)
    scrambledWordEl.textContent = scrambled
    hintEl.textContent = currentWord.hint

    timeLeft = DIFFICULTY_LEVELS[currentDifficulty].timePerWord
    timerEl.textContent = timeLeft

    clearInterval(timer)
    timer = setInterval(updateTimer, 1000)

    document.getElementById("round-display").textContent = `Round ${currentRound} of ${TOTAL_ROUNDS}`
    guessInput.focus()
  }

  // Reset game
  function resetGame() {
    clearInterval(timer)
    gameOver = false
    guessInput.value = ""
    guessInput.disabled = false
    submitBtn.disabled = false
    messageEl.textContent = ""
    getNewWord()
  }

  // Start game
  function startGame() {
    score = 0
    correctCount = 0
    incorrectCount = 0
    currentRound = 0
    gameOver = false

    scoreEl.textContent = "0"
    correctEl.textContent = "0"
    incorrectEl.textContent = "0"
    messageEl.textContent = ""

    guessInput.disabled = false
    submitBtn.disabled = false

    getNewWord()
  }

  // Check guess
  function checkGuess() {
    if (gameOver) return

    const guess = guessInput.value.trim().toLowerCase()

    if (!guess) {
      showMessage("Please enter a guess!", "error")
      return
    }

    const basePoints = 10 * DIFFICULTY_LEVELS[currentDifficulty].xpMultiplier
    const timeBonus = Math.floor(timeLeft / 5)
    const pointsEarned = basePoints + timeBonus

    if (guess === currentWord.word.toLowerCase()) {
      score += pointsEarned
      correctCount++
      scoreEl.textContent = score
      correctEl.textContent = correctCount

      showMessage(`Correct! +${pointsEarned} XP`, "success")

      wordScrambleService.addXp(pointsEarned)
      updateXpUI()

      scrambledWordEl.classList.add("correct-guess")

      setTimeout(() => {
        scrambledWordEl.classList.remove("correct-guess")
        if (currentRound < TOTAL_ROUNDS) {
          getNewWord()
        } else {
          endGame()
        }
      }, 1000)
    } else {
      incorrectCount++
      incorrectEl.textContent = incorrectCount
      showMessage("Incorrect! Try again.", "error")
      scrambledWordEl.classList.add("incorrect-guess")

      setTimeout(() => {
        scrambledWordEl.classList.remove("incorrect-guess")
      }, 500)
    }

    guessInput.value = ""
    guessInput.focus()
  }

  // End game
  function endGame() {
    gameOver = true
    clearInterval(timer)
    guessInput.disabled = true
    submitBtn.disabled = true

    const finalBonus = Math.floor(score / 5)
    if (finalBonus > 0) {
      wordScrambleService.addXp(finalBonus)
      updateXpUI()
    }

    showMessage(`Game Over! Final Score: ${score}`, "success")
  }

  // Event listeners
  submitBtn.addEventListener("click", checkGuess)
  guessInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") checkGuess()
  })

  difficultyButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      setDifficulty(btn.dataset.difficulty)
    })
  })

  restartBtn.addEventListener("click", startGame)

  // Leaderboard initialization
  const leaderboard = new Leaderboard();
  const leaderboardModal = document.getElementById('leaderboardModal');
  const closeBtn = document.querySelector('.close');
  const showLeaderboardBtn = document.getElementById('showLeaderboard');
  const tabButtons = document.querySelectorAll('.tab-button');
  const leaderboardTable = document.getElementById('leaderboard');
  let currentDifficultyTab = 'easy';

  // Show leaderboard modal
  function showLeaderboard(difficulty = 'easy') {
    currentDifficultyTab = difficulty;
    updateLeaderboardUI(difficulty);
    leaderboardModal.classList.add('show');
    document.body.style.overflow = 'hidden';
  }

  // Hide leaderboard modal
  function hideLeaderboard() {
    leaderboardModal.classList.remove('show');
    document.body.style.overflow = '';
  }

  // Update leaderboard UI with scores
  function updateLeaderboardUI(difficulty) {
    const scores = leaderboard.getTopScores(difficulty);
    
    // Update active tab
    tabButtons.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.difficulty === difficulty);
    });

    if (scores.length === 0) {
      leaderboardTable.innerHTML = `
        <div class="no-scores">
          No scores yet for ${difficulty} difficulty. Be the first to play!
        </div>
      `;
      return;
    }

    let html = `
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Player</th>
            <th>Score</th>
          </tr>
        </thead>
        <tbody>
    `;

    scores.forEach((entry, index) => {
      html += `
        <tr>
          <td class="rank">${index + 1}</td>
          <td class="player-name">${entry.name}</td>
          <td class="player-score">${entry.score}</td>
        </tr>
      `;
    });

    html += `
        </tbody>
      </table>
    `;

    leaderboardTable.innerHTML = html;
  }

  // Save score to leaderboard
  function saveScoreToLeaderboard(score) {
    const name = prompt('Congratulations! Enter your name for the leaderboard:');
    if (name && name.trim() !== '') {
      leaderboard.addScore(name.trim(), score, currentDifficulty);
      showLeaderboard(currentDifficulty);
    }
  }

  // Event listeners for leaderboard
  showLeaderboardBtn.addEventListener('click', () => showLeaderboard(currentDifficultyTab));
  closeBtn.addEventListener('click', hideLeaderboard);
  
  // Close modal when clicking outside
  window.addEventListener('click', (e) => {
    if (e.target === leaderboardModal) {
      hideLeaderboard();
    }
  });

  // Tab switching
  tabButtons.forEach(button => {
    button.addEventListener('click', () => {
      const difficulty = button.dataset.difficulty;
      updateLeaderboardUI(difficulty);
      currentDifficultyTab = difficulty;
    });
  });

  // Update endGame to show leaderboard
  const originalEndGame = endGame;
  endGame = function() {
    originalEndGame.apply(this, arguments);
    if (score > 0) {
      setTimeout(() => {
        saveScoreToLeaderboard(score);
      }, 1000);
    }
  };

  // Initialize UI
  updateXpUI();
  // Initialize leaderboard with easy difficulty
  updateLeaderboardUI('easy');
})
