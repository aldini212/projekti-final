// Game elements
const startButton = document.getElementById('startButton');
const gameArea = document.getElementById('gameArea');
const target = document.getElementById('target');
const resultText = document.getElementById('resultText');
const avgTimeElement = document.getElementById('avgTime');
const lastTimeElement = document.getElementById('lastTime');
const roundLabel = document.getElementById('roundLabel');

// Game state
let gameState = {
    isRunning: false,
    startTime: 0,
    reactionTimes: [],
    currentRound: 0,
    totalRounds: 5
};

// Start the game
function startGame() {
    if (gameState.isRunning) return;
    
    // Reset game state
    gameState.isRunning = true;
    gameState.reactionTimes = [];
    gameState.currentRound = 0;
    
    // Reset UI elements
    lastTimeElement.textContent = '-';
    lastTimeElement.style.color = '';
    avgTimeElement.textContent = '-';
    resultText.textContent = 'Përgatitu për të klikuar!';
    resultText.style.color = '';
    
    // Update UI
    startButton.disabled = true;
    startButton.textContent = 'Duke u përgatitur...';
    resultText.textContent = '';
    gameArea.innerHTML = '<div id="target"></div>';
    
    // Start first round
    setTimeout(startRound, 1000);
}

// Start a new round
function startRound() {
    gameState.currentRound++;
    roundLabel.textContent = `${gameState.currentRound}/${gameState.totalRounds}`;
    
    // Reset target
    const target = document.getElementById('target');
    target.style.display = 'none';
    target.style.width = '100px';
    target.style.height = '100px';
    target.style.backgroundColor = '#4CAF50';
    target.style.borderRadius = '10px';
    target.style.position = 'absolute';
    target.style.cursor = 'pointer';
    
    // Random delay before showing target (1-2.5 seconds)
    const delay = 1000 + Math.random() * 1500;
    
    setTimeout(() => {
        if (!gameState.isRunning) return;
        
        // Position target randomly with padding from edges
        const padding = 50;
        const maxX = gameArea.offsetWidth - 150;
        const maxY = gameArea.offsetHeight - 150;
        const x = padding + Math.random() * (maxX - padding * 2);
        const y = padding + Math.random() * (maxY - padding * 2);
        
        target.style.left = `${x}px`;
        target.style.top = `${y}px`;
        target.style.display = 'block';
        target.style.transition = 'background-color 0.2s';
        
        // Start timing
        gameState.startTime = performance.now();
        
        // Auto-miss after 2 seconds (more forgiving)
        gameState.timeout = setTimeout(() => {
            if (gameState.isRunning) {
                target.style.backgroundColor = '#ff4444'; // Visual feedback for missing
                setTimeout(() => {
                    if (gameState.isRunning) {
                        handleMiss();
                    }
                }, 200);
            }
        }, 2000);
        
    }, delay);
}

// Handle target click
function handleClick() {
    if (!gameState.isRunning) return;
    
    // Clear any pending miss timeout
    clearTimeout(gameState.timeout);
    
    // Calculate reaction time
    const reactionTime = performance.now() - gameState.startTime;
    gameState.reactionTimes.push(reactionTime);
    
    // Visual feedback for successful click
    const target = document.getElementById('target');
    target.style.backgroundColor = '#4CAF50';
    target.style.transform = 'scale(0.95)';
    setTimeout(() => {
        if (target) {
            target.style.transform = 'scale(1)';
        }
    }, 100);
    
    // Update UI with color feedback based on reaction time
    lastTimeElement.textContent = `${Math.round(reactionTime)} ms`;
    if (reactionTime < 300) {
        lastTimeElement.style.color = '#4CAF50'; // Fast (green)
    } else if (reactionTime < 600) {
        lastTimeElement.style.color = '#FFC107'; // Medium (yellow)
    } else {
        lastTimeElement.style.color = '#F44336'; // Slow (red)
    }
    
    // Calculate average
    const avg = gameState.reactionTimes.reduce((a, b) => a + b, 0) / gameState.reactionTimes.length;
    avgTimeElement.textContent = `${Math.round(avg)} ms`;
    
    // Check if game is over
    if (gameState.currentRound >= gameState.totalRounds) {
        endGame();
    } else {
        // Start next round
        startRound();
    }
}

// Handle miss
function handleMiss() {
    if (!gameState.isRunning) return;
    
    // Update UI with more visible feedback
    resultText.textContent = 'Shumë ngadalë! Provo përsëri.';
    resultText.style.color = '#ff6b6b';
    setTimeout(() => {
        if (resultText) resultText.style.color = '';
    }, 1000);
    
    // Add a small delay before next round
    setTimeout(() => {
        // Check if game is over
        if (gameState.currentRound >= gameState.totalRounds) {
            endGame();
        } else {
            // Start next round
            startRound();
        }
    }, 500);
}

// End game
function endGame() {
    gameState.isRunning = false;
    clearTimeout(gameState.timeout);
    
    // Calculate average
    const avg = gameState.reactionTimes.length > 0 
        ? Math.round(gameState.reactionTimes.reduce((a, b) => a + b, 0) / gameState.reactionTimes.length)
        : 0;
    
    // Show final results
    resultText.textContent = `Testi mbaroi! Koha mesatare: ${avg} ms`;
    startButton.disabled = false;
    startButton.textContent = 'Fillo Përsëri';
}

// Event listeners
startButton.addEventListener('click', startGame);
gameArea.addEventListener('click', (e) => {
    if (e.target.id === 'target' && gameState.isRunning) {
        clearTimeout(gameState.timeout);
        handleClick();
    }
});

// Initialize
roundLabel.textContent = `0/${gameState.totalRounds}`;
lastTimeElement.textContent = '-';
avgTimeElement.textContent = '-';