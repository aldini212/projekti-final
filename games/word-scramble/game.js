document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const scrambledWordEl = document.getElementById('scrambled-word');
    const hintEl = document.getElementById('hint');
    const guessInput = document.getElementById('guess');
    const submitBtn = document.getElementById('submit');
    const restartBtn = document.getElementById('restart');
    const messageEl = document.getElementById('message');
    const scoreEl = document.getElementById('score');
    const timerEl = document.getElementById('timer');
    const correctEl = document.getElementById('correct');
    const incorrectEl = document.getElementById('incorrect');
    const levelEl = document.getElementById('level');
    const currentXpEl = document.getElementById('currentXp');
    const nextLevelXpEl = document.getElementById('nextLevelXp');
    const xpProgressEl = document.getElementById('xpProgress');
    const xpTextEl = document.getElementById('xpText');
    
    // XP service
    const wordScrambleService = new GameService('word-scramble');
    
    function updateXpUI() {
        const p = wordScrambleService.getProgress();
        if (levelEl) levelEl.textContent = p.level;
        if (currentXpEl) currentXpEl.textContent = p.xp;
        if (nextLevelXpEl) nextLevelXpEl.textContent = p.xpToNextLevel;
        if (xpProgressEl) xpProgressEl.style.width = `${p.progressPercentage}%`;
        if (xpTextEl) xpTextEl.textContent = `${p.progressPercentage}%`;
    }
    
    updateXpUI();
    
    // Game state
    let currentWord = '';
    let scrambledWord = '';
    let score = 0;
    let timeLeft = 60;
    let timer;
    let correctCount = 0;
    let incorrectCount = 0;
    let gameOver = false;
    
    // Word bank with hints
    const wordBank = [
        { word: 'javascript', hint: 'A popular programming language for web development' },
        { word: 'hangman', hint: 'A word guessing game' },
        { word: 'keyboard', hint: 'Input device for computers' },
        { word: 'elephant', hint: 'A large mammal with a trunk' },
        { word: 'mountain', hint: 'A large natural elevation of the earth\'s surface' },
        { word: 'guitar', hint: 'A musical instrument with strings' },
        { word: 'pizza', hint: 'A popular Italian dish' },
        { word: 'rainbow', hint: 'A meteorological phenomenon with colors' },
        { word: 'dolphin', hint: 'An intelligent marine mammal' },
        { word: 'library', hint: 'A place where books are kept' },
        { word: 'adventure', hint: 'An exciting or unusual experience' },
        { word: 'basketball', hint: 'A team sport with a ball and hoop' },
        { word: 'chocolate', hint: 'A sweet, brown food made from roasted cacao seeds' },
        { word: 'diamond', hint: 'A precious stone made of carbon' },
        { word: 'elephant', hint: 'The largest land animal' },
        { word: 'fireworks', hint: 'Explosive devices used for entertainment' },
        { word: 'galaxy', hint: 'A system of millions or billions of stars' },
        { word: 'hamburger', hint: 'A sandwich with a cooked patty of ground meat' },
        { word: 'internet', hint: 'A global computer network' },
        { word: 'jellyfish', hint: 'A free-swimming marine animal with a jelly-like body' }
    ];
    
    // Initialize the game
    function initGame() {
        // Reset game state
        clearInterval(timer);
        timeLeft = 60;
        updateTimer();
        
        // Select a random word
        const randomIndex = Math.floor(Math.random() * wordBank.length);
        currentWord = wordBank[randomIndex].word.toLowerCase();
        
        // Set the hint
        hintEl.textContent = wordBank[randomIndex].hint;
        
        // Scramble the word
        scrambledWord = scrambleWord(currentWord);
        scrambledWordEl.textContent = scrambledWord;
        
        // Clear input and message
        guessInput.value = '';
        messageEl.textContent = '';
        messageEl.className = 'message';
        
        // Start the timer
        timer = setInterval(updateTimer, 1000);
        
        // Focus the input
        guessInput.focus();
    }
    
    // Scramble a word
    function scrambleWord(word) {
        const letters = word.split('');
        for (let i = letters.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [letters[i], letters[j]] = [letters[j], letters[i]];
        }
        return letters.join('');
    }
    
    // Update the timer
    function updateTimer() {
        timeLeft--;
        timerEl.textContent = timeLeft;
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            endGame();
        }
    }
    
    // Check the player's guess
    function checkGuess() {
        if (gameOver) return;

        const guess = guessInput.value.trim().toLowerCase();
        if (!guess) return;
        
        if (guess === currentWord) {
            // Correct guess
            const basePoints = 10;
            const timeBonus = Math.floor(timeLeft * 0.5);
            const pointsEarned = basePoints + timeBonus;

            score += pointsEarned;
            score = Math.floor(score);
            scoreEl.textContent = score;
            
            correctCount++;
            correctEl.textContent = correctCount;
            
            // XP: base 10 + half of time bonus
            const xpEarned = 10 + Math.floor(timeBonus / 2);
            const { levelsGained } = wordScrambleService.addXp(xpEarned);
            showXpGain(xpEarned, levelsGained);
            updateXpUI();

            messageEl.textContent = 'Correct! Well done!';
            messageEl.className = 'message correct';
            
            // Get a new word after a short delay
            setTimeout(initGame, 1200);
        } else {
            // Incorrect guess
            incorrectCount++;
            incorrectEl.textContent = incorrectCount;
            
            messageEl.textContent = 'Incorrect. Try again!';
            messageEl.className = 'message incorrect';
            
            // Shake animation for incorrect guess
            scrambledWordEl.style.animation = 'shake 0.5s';
            setTimeout(() => {
                scrambledWordEl.style.animation = '';
            }, 500);
        }
        
        // Clear the input
        guessInput.value = '';
    }
    
    // End the game
    function endGame() {
        if (gameOver) return;
        gameOver = true;

        // Final XP bonus from total score
        if (score > 0) {
            const finalBonus = Math.floor(score / 5);
            const { levelsGained } = wordScrambleService.addXp(finalBonus);
            if (finalBonus > 0) {
                showXpGain(finalBonus, levelsGained);
            }
            updateXpUI();
        }

        messageEl.textContent = `Game Over! Your final score is ${score}`;
        messageEl.className = 'message';
        
        // Disable input and buttons
        guessInput.disabled = true;
        submitBtn.disabled = true;
        
        // Save the score
        saveScore('word-scramble', score);
    }
    
    // Save score to the server / local history
    function saveScore(gameName, score) {
        const progress = wordScrambleService.getProgress();

        const data = {
            game: gameName,
            score,
            correct: correctCount,
            incorrect: incorrectCount,
            xp: progress.xp,
            level: progress.level,
            xpToNextLevel: progress.xpToNextLevel,
            playedAt: new Date().toISOString()
        };

        // Save latest game summary for profile page
        localStorage.setItem('gamehub_word-scramble_last', JSON.stringify(data));

        console.log('Word Scramble summary:', data);
        // Backend call placeholder kept commented out
    }
    
    // Event listeners
    submitBtn.addEventListener('click', checkGuess);
    
    guessInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            checkGuess();
        }
    });
    
    restartBtn.addEventListener('click', () => {
        // Reset game state
        score = 0;
        scoreEl.textContent = score;
        correctCount = 0;
        correctEl.textContent = '0';
        incorrectCount = 0;
        incorrectEl.textContent = '0';
        
        // Re-enable input and buttons
        guessInput.disabled = false;
        submitBtn.disabled = false;
        
        // Start a new game
        initGame();
    });
    
    // Start the game
    initGame();
});
