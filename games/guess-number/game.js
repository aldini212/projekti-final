document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const guessInput = document.getElementById('guess');
    const submitBtn = document.getElementById('submit');
    const restartBtn = document.getElementById('restart');
    const hintBtn = document.getElementById('hintBtn');
    const messageEl = document.getElementById('message');
    const scoreEl = document.getElementById('score');
    const attemptsEl = document.getElementById('attempts');
    const hintCountEl = document.getElementById('hintCount');
    const minEl = document.getElementById('min');
    const maxEl = document.getElementById('max');
    const previousGuessesEl = document.getElementById('previous-guesses');
    const hintEl = document.getElementById('hint');
    const xpGainEl = document.getElementById('xpGain');
    const xpGainAmount = document.getElementById('xpGainAmount');
    const xpProgress = document.getElementById('xpProgress');
    const xpText = document.getElementById('xpText');
    const currentXpEl = document.getElementById('currentXp');
    const nextLevelXpEl = document.getElementById('nextLevelXp');
    const levelEl = document.getElementById('level');
    
    // Game state
    let targetNumber;
    let score = 100;
    let attempts = 0;
    let hintsRemaining = 3;
    let min = 1;
    let max = 100;
    let gameOver = false;
    let previousGuesses = [];
    
    // XP System
    let xp = 0;
    let level = 1;
    let xpToNextLevel = 100;
    
    // Initialize the game
    function initGame() {
        // Reset game state
        targetNumber = Math.floor(Math.random() * 100) + 1;
        score = 100;
        attempts = 0;
        hintsRemaining = 3;
        min = 1;
        max = 100;
        gameOver = false;
        previousGuesses = [];
        
        // Update the UI
        updateUI();
        
        // Clear input and message
        guessInput.value = '';
        messageEl.innerHTML = '<i class="bi bi-info-circle me-2"></i> Enter a number and click Submit';
        messageEl.className = 'message';
        hintEl.textContent = '';
        previousGuessesEl.innerHTML = '';
        
        // Enable input and buttons
        guessInput.disabled = false;
        submitBtn.disabled = false;
        hintBtn.disabled = false;
        
        // Update hint button text
        hintBtn.innerHTML = `<i class="bi bi-lightbulb me-1"></i> Get Hint (${hintsRemaining} left)`;
        
        // Focus the input
        guessInput.focus();
        
        // Load XP and level from localStorage
        loadGameProgress();
    }
    
    // Load game progress from localStorage
    function loadGameProgress() {
        const savedData = localStorage.getItem('guessNumberGame');
        if (savedData) {
            const { xp: savedXp, level: savedLevel } = JSON.parse(savedData);
            xp = savedXp || 0;
            level = savedLevel || 1;
            updateXpBar();
        }
    }
    
    // Save game progress to localStorage
    function saveGameProgress() {
        const gameData = { xp, level };
        localStorage.setItem('guessNumberGame', JSON.stringify(gameData));
    }
    
    // Update the UI
    function updateUI() {
        scoreEl.textContent = Math.max(0, score);
        attemptsEl.textContent = attempts;
        hintCountEl.textContent = hintsRemaining;
        minEl.textContent = min;
        maxEl.textContent = max;
    }
    
    // Update XP bar
    function updateXpBar() {
        // Calculate XP percentage for current level
        const xpPercentage = Math.min(100, (xp / xpToNextLevel) * 100);
        xpProgress.style.width = `${xpPercentage}%`;
        xpText.textContent = `${Math.round(xpPercentage)}%`;
        currentXpEl.textContent = xp;
        nextLevelXpEl.textContent = xpToNextLevel;
        levelEl.textContent = level;
    }
    
    // Add XP with animation
    function addXp(amount) {
        const oldXp = xp;
        xp += amount;
        
        // Check for level up
        while (xp >= xpToNextLevel) {
            level++;
            xp -= xpToNextLevel;
            xpToNextLevel = Math.floor(xpToNextLevel * 1.5); // Increase XP needed for next level
            showLevelUp();
        }
        
        // Show XP gain animation
        showXpGain(amount);
        
        // Update XP bar with animation
        animateXpBar(oldXp, xp);
        
        // Save progress
        saveGameProgress();
    }
    
    // Animate XP bar
    function animateXpBar(oldXp, newXp) {
        let currentXp = oldXp;
        const xpStep = Math.ceil((newXp - oldXp) / 20); // 20 frames for animation
        
        const animation = setInterval(() => {
            currentXp += xpStep;
            if (currentXp >= newXp) {
                currentXp = newXp;
                clearInterval(animation);
            }
            
            // Calculate current XP percentage
            let currentXpToNextLevel = xpToNextLevel;
            if (currentXp > xpToNextLevel) {
                // If we've leveled up, calculate for the next level
                currentXpToNextLevel = xpToNextLevel * 1.5;
            }
            
            const xpPercentage = Math.min(100, (currentXp % currentXpToNextLevel) / currentXpToNextLevel * 100);
            xpProgress.style.width = `${xpPercentage}%`;
            xpText.textContent = `${Math.round(xpPercentage)}%`;
            currentXpEl.textContent = Math.min(currentXp, xpToNextLevel);
            
        }, 30);
    }
    
    // Show XP gain animation
    function showXpGain(amount) {
        xpGainAmount.textContent = `+${amount} XP`;
        xpGainEl.style.display = 'flex';
        
        // Reset animation
        xpGainEl.style.animation = 'none';
        void xpGainEl.offsetWidth; // Trigger reflow
        xpGainEl.style.animation = 'floatUp 1.5s ease-out forwards';
        
        // Hide after animation
        setTimeout(() => {
            xpGainEl.style.display = 'none';
        }, 1500);
    }
    
    // Show level up animation
    function showLevelUp() {
        const levelUpEl = document.createElement('div');
        levelUpEl.className = 'level-up';
        levelUpEl.innerHTML = `
            <div class="level-up-content">
                <i class="bi bi-trophy-fill"></i>
                <h3>Level Up!</h3>
                <p>You've reached level ${level}!</p>
            </div>
        `;
        document.body.appendChild(levelUpEl);
        
        // Remove after animation
        setTimeout(() => {
            levelUpEl.remove();
        }, 3000);
    }
    
    // Check the player's guess
    function checkGuess() {
        const guess = parseInt(guessInput.value);
        
        // Validate input
        if (isNaN(guess) || guess < min || guess > max) {
            showMessage(`Please enter a number between ${min} and ${max}`, 'error');
            return;
        }
        
        // Check if already guessed
        if (previousGuesses.includes(guess)) {
            showMessage(`You've already guessed ${guess}. Try a different number.`, 'error');
            guessInput.value = '';
            return;
        }
        
        // Add to previous guesses
        previousGuesses.push(guess);
        attempts++;
        
        // Check the guess
        if (guess === targetNumber) {
            // Correct guess
            gameOver = true;
            const xpEarned = calculateXpEarned();
            showMessage(`Congratulations! You found the number in ${attempts} attempts! You earned ${xpEarned} XP!`, 'correct');
            
            // Disable input and buttons
            guessInput.disabled = true;
            submitBtn.disabled = true;
            
            // Add XP
            addXp(xpEarned);
            
            // Show the number in the previous guesses
            addGuessToHistory(guess, 'correct');
            
            // Save the score
            saveScore('guess-number', score, attempts);
        } else {
            // Incorrect guess
            score = Math.max(0, score - 10);
            
            // Update range and message
            if (guess < targetNumber) {
                min = guess + 1;
                showMessage('Too low! Try a higher number.', 'low');
                addGuessToHistory(guess, 'low');
            } else {
                max = guess - 1;
                showMessage('Too high! Try a lower number.', 'high');
                addGuessToHistory(guess, 'high');
            }
            
            // Check if game over (score reached 0)
            if (score <= 0) {
                gameOver = true;
                showMessage(`Game Over! The number was ${targetNumber}.`, 'error');
                
                // Disable input and buttons
                guessInput.disabled = true;
                submitBtn.disabled = true;
                
                // Still give some XP for trying
                const xpEarned = Math.max(5, 20 - attempts);
                addXp(xpEarned);
                
                // Save the score (0)
                saveScore('guess-number', 0, attempts);
            }
        }
        
        // Update the UI
        updateUI();
        
        // Clear the input and focus it
        guessInput.value = '';
        guessInput.focus();
    }
    
    // Calculate XP earned based on performance
    function calculateXpEarned() {
        // Base XP for winning
        let xp = 50;
        
        // Bonus for fewer attempts
        if (attempts <= 5) xp += 30;
        else if (attempts <= 10) xp += 20;
        else if (attempts <= 15) xp += 10;
        
        // Bonus for high score
        xp += Math.floor(score / 10);
        
        return Math.max(10, xp); // Minimum 10 XP
    }
    
    // Show message with animation
    function showMessage(text, type = 'info') {
        messageEl.className = `message ${type}`;
        
        // Add appropriate icon based on message type
        let icon = 'bi-info-circle';
        if (type === 'error') icon = 'bi-exclamation-triangle';
        else if (type === 'correct') icon = 'bi-check-circle';
        else if (type === 'low') icon = 'bi-arrow-up-circle';
        else if (type === 'high') icon = 'bi-arrow-down-circle';
        
        messageEl.innerHTML = `<i class="bi ${icon} me-2"></i> ${text}`;
        
        // Add animation
        messageEl.style.animation = 'none';
        void messageEl.offsetWidth; // Trigger reflow
        messageEl.style.animation = 'pulse 0.5s';
    }
    
    // Add a guess to the history
    function addGuessToHistory(guess, type) {
        const guessEl = document.createElement('div');
        guessEl.className = `guess-number ${type}`;
        guessEl.textContent = guess;
        guessEl.title = `Guess #${attempts}: ${guess} (${type})`;
        
        // Add to the beginning of the container
        previousGuessesEl.insertBefore(guessEl, previousGuessesEl.firstChild);
        
        // Add animation
        guessEl.style.animation = 'float 0.5s ease-out';
    }
    
    // Get a hint
    function getHint() {
        if (hintsRemaining <= 0) {
            showMessage('No hints remaining!', 'error');
            return;
        }
        
        hintsRemaining--;
        updateUI();
        
        // Update hint button text
        hintBtn.innerHTML = `<i class="bi bi-lightbulb me-1"></i> Get Hint (${hintsRemaining} left)`;
        
        // Disable button if no hints left
        if (hintsRemaining <= 0) {
            hintBtn.disabled = true;
        }
        
        // Generate hint based on game state
        let hint = '';
        const hintType = Math.floor(Math.random() * 3);
        
        switch(hintType) {
            case 0: // Even or odd
                hint = `The number is ${targetNumber % 2 === 0 ? 'even' : 'odd'}.`;
                break;
            case 1: // Range hint
                const range = max - min;
                if (range > 20) {
                    const third = Math.floor(range / 3);
                    if (targetNumber < min + third) {
                        hint = 'The number is in the lower third of the range.';
                    } else if (targetNumber > max - third) {
                        hint = 'The number is in the upper third of the range.';
                    } else {
                        hint = 'The number is in the middle third of the range.';
                    }
                } else {
                    hint = `The number is between ${Math.max(min, targetNumber - 5)} and ${Math.min(max, targetNumber + 5)}.`;
                }
                break;
            case 2: // Digit hint
                const digits = targetNumber.toString().split('');
                if (digits.length > 1) {
                    const randomDigit = digits[Math.floor(Math.random() * digits.length)];
                    hint = `One of the digits is ${randomDigit}.`;
                } else {
                    hint = `The number is a single digit.`;
                }
                break;
        }
        
        // Show the hint
        hintEl.innerHTML = `<i class="bi bi-lightbulb-fill text-warning me-2"></i> ${hint}`;
        
        // Animate hint
        hintEl.style.animation = 'none';
        void hintEl.offsetWidth; // Trigger reflow
        hintEl.style.animation = 'pulse 0.5s';
    }
    
    // Save score to the server
    function saveScore(gameName, score, attempts) {
        // This would be replaced with an actual API call to your backend
        console.log(`Saving score - Game: ${gameName}, Score: ${score}, Attempts: ${attempts}, XP: ${xp}, Level: ${level}`);
        
        // Example fetch request (commented out as it requires backend implementation)
        /*
        fetch('/api/save_score.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                game: gameName,
                score: score,
                attempts: attempts,
                xp: xp,
                level: level
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Score saved:', data);
        })
        .catch(error => {
            console.error('Error saving score:', error);
        });
        */
    }
    
    // Event listeners
    submitBtn.addEventListener('click', checkGuess);
    
    guessInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            checkGuess();
        }
    });
    
    hintBtn.addEventListener('click', getHint);
    restartBtn.addEventListener('click', initGame);
    
    // Start the game
    initGame();
    
    // Add styles for level up animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes floatUp {
            0% { opacity: 0; transform: translate(-50%, 20px); }
            20% { opacity: 1; transform: translate(-50%, 0); }
            80% { opacity: 1; transform: translate(-50%, 0); }
            100% { opacity: 0; transform: translate(-50%, -20px); }
        }
        
        .level-up {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            color: white;
            padding: 2rem 3rem;
            border-radius: 15px;
            text-align: center;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: levelUp 1s ease-out;
        }
        
        .level-up i {
            font-size: 3rem;
            color: #ffd700;
            margin-bottom: 1rem;
            display: block;
        }
        
        .level-up h3 {
            margin: 0 0 0.5rem;
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(45deg, #fff, #ffd700);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .level-up p {
            margin: 0;
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        @keyframes levelUp {
            0% { transform: translate(-50%, -50%) scale(0.5); opacity: 0; }
            70% { transform: translate(-50%, -50%) scale(1.1); opacity: 1; }
            100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
});
