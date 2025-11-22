document.addEventListener('DOMContentLoaded', () => {
    const gameBoard = document.getElementById('game-board');
    const movesDisplay = document.getElementById('moves');
    const timerDisplay = document.getElementById('timer');
    const restartButton = document.getElementById('restart');
    
    let cards = [];
    let hasFlippedCard = false;
    let lockBoard = false;
    let firstCard, secondCard;
    let moves = 0;
    let timer = 0;
    let timerInterval;
    
    // Card symbols - you can replace these with actual image paths
    const cardSymbols = [
        '🐶', '🐱', '🐭', '🐹',
        '🐰', '🦊', '🐻', '🐼',
        '🐨', '🐯', '🦁', '🐮',
        '🐷', '🐸', '🐵', '🐔'
    ];
    
    // Initialize the game
    function initGame() {
        // Reset game state
        moves = 0;
        timer = 0;
        clearInterval(timerInterval);
        updateMoves();
        updateTimer();
        
        // Shuffle and create cards
        const shuffledCards = [...cardSymbols, ...cardSymbols].sort(() => Math.random() - 0.5);
        
        // Clear the board
        gameBoard.innerHTML = '';
        
        // Create cards
        shuffledCards.forEach((symbol, index) => {
            const card = document.createElement('div');
            card.classList.add('card');
            card.dataset.symbol = symbol;
            card.dataset.index = index;
            
            const img = document.createElement('img');
            img.src = `assets/cards/${symbol}.png`;
            img.alt = symbol;
            img.onerror = function() {
                // If image doesn't exist, use emoji as fallback
                this.style.display = 'none';
                const fallback = document.createElement('div');
                fallback.textContent = symbol;
                fallback.style.fontSize = '2rem';
                this.parentNode.appendChild(fallback);
            };
            
            card.appendChild(img);
            card.addEventListener('click', flipCard);
            gameBoard.appendChild(card);
        });
        
        cards = document.querySelectorAll('.card');
        
        // Start the timer after a short delay
        setTimeout(() => {
            timerInterval = setInterval(updateTimer, 1000);
        }, 1000);
    }
    
    // Flip a card
    function flipCard() {
        if (lockBoard) return;
        if (this === firstCard) return;
        if (this.classList.contains('matched')) return;
        
        this.classList.add('flipped');
        
        if (!hasFlippedCard) {
            // First card flipped
            hasFlippedCard = true;
            firstCard = this;
            return;
        }
        
        // Second card flipped
        secondCard = this;
        checkForMatch();
    }
    
    // Check if the flipped cards match
    function checkForMatch() {
        const isMatch = firstCard.dataset.symbol === secondCard.dataset.symbol;
        
        if (isMatch) {
            disableCards();
            checkGameOver();
        } else {
            unflipCards();
        }
        
        // Update moves
        moves++;
        updateMoves();
    }
    
    // Disable matched cards
    function disableCards() {
        firstCard.removeEventListener('click', flipCard);
        secondCard.removeEventListener('click', flipCard);
        firstCard.classList.add('matched');
        secondCard.classList.add('matched');
        
        resetBoard();
    }
    
    // Unflip cards that don't match
    function unflipCards() {
        lockBoard = true;
        
        setTimeout(() => {
            firstCard.classList.remove('flipped');
            secondCard.classList.remove('flipped');
            
            resetBoard();
        }, 1000);
    }
    
    // Reset the board state
    function resetBoard() {
        [hasFlippedCard, lockBoard] = [false, false];
        [firstCard, secondCard] = [null, null];
    }
    
    // Update moves counter
    function updateMoves() {
        movesDisplay.textContent = moves;
    }
    
    // Update timer
    function updateTimer() {
        timer++;
        timerDisplay.textContent = timer;
    }
    
    // Check if the game is over
    function checkGameOver() {
        const matchedCards = document.querySelectorAll('.matched');
        if (matchedCards.length === cards.length) {
            clearInterval(timerInterval);
            setTimeout(() => {
                alert(`Congratulations! You won in ${moves} moves and ${timer} seconds!`);
                saveScore('memory', moves, timer);
            }, 500);
        }
    }
    
    // Save score to the server
    function saveScore(gameName, score, time) {
        // This would be replaced with an actual API call to your backend
        console.log(`Saving score - Game: ${gameName}, Moves: ${score}, Time: ${time}s`);
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
                time: time
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
    restartButton.addEventListener('click', initGame);
    
    // Start the game
    initGame();
});
