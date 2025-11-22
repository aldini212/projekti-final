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
    
    // Random delay before showing target (1-3 seconds)
    const delay = 1000 + Math.random() * 2000;
    
    setTimeout(() => {
        if (!gameState.isRunning) return;
        
        // Position target randomly
        const maxX = gameArea.offsetWidth - 100;
        const maxY = gameArea.offsetHeight - 100;
        const x = Math.random() * maxX;
        const y = Math.random() * maxY;
        
        target.style.left = `${x}px`;
        target.style.top = `${y}px`;
        target.style.display = 'block';
        
        // Start timing
        gameState.startTime = performance.now();
        
        // Auto-miss after 3 seconds
        gameState.timeout = setTimeout(() => {
            if (gameState.isRunning) {
                handleMiss();
            }
        }, 3000);
        
    }, delay);
}

// Handle target click
function handleClick() {
    if (!gameState.isRunning) return;
    
    // Calculate reaction time
    const reactionTime = performance.now() - gameState.startTime;
    gameState.reactionTimes.push(reactionTime);
    
    // Update UI
    lastTimeElement.textContent = `${Math.round(reactionTime)} ms`;
    
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
    
    // Update UI
    resultText.textContent = 'Shumë ngadalë! Provo përsëri.';
    
    // Check if game is over
    if (gameState.currentRound >= gameState.totalRounds) {
        endGame();
    } else {
        // Start next round
        startRound();
    }
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