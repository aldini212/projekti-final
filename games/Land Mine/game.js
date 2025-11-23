// c:\xampp\htdocs\GamingHub\projekti-final-1\games\Land Mine\js\game.js
document.addEventListener('DOMContentLoaded', () => {
    const gameBoard = document.getElementById('game-board');
    const timerDisplay = document.getElementById('timer');
    const minesLeftDisplay = document.getElementById('mines-left');
    const restartButton = document.getElementById('restart');
    const difficultyButtons = document.querySelectorAll('.difficulty button');
    
    const ROWS = 8;
    const COLS = 8;
    let board = [];
    let minesCount = 10;
    let flagsCount = 0;
    let revealedCount = 0;
    let gameOver = false;
    let timer = 0;
    let timerInterval;
    let firstClick = true;

    // Initialize the game
    function initGame() {
        // Reset game state
        gameOver = false;
        firstClick = true;
        flagsCount = 0;
        revealedCount = 0;
        timer = 0;
        clearInterval(timerInterval);
        updateTimer();
        updateMinesLeft();
        
        // Clear the board
        gameBoard.innerHTML = '';
        board = [];
        
        // Create empty board
        for (let row = 0; row < ROWS; row++) {
            const rowArray = [];
            for (let col = 0; col < COLS; col++) {
                const cell = document.createElement('div');
                cell.className = 'cell';
                cell.dataset.row = row;
                cell.dataset.col = col;
                
                cell.addEventListener('click', handleCellClick);
                cell.addEventListener('contextmenu', handleRightClick);
                
                gameBoard.appendChild(cell);
                rowArray.push({
                    element: cell,
                    isMine: false,
                    isRevealed: false,
                    isFlagged: false,
                    neighborMines: 0
                });
            }
            board.push(rowArray);
        }
    }
    
    // Place mines randomly
    function placeMines(firstClickRow, firstClickCol) {
        let minesPlaced = 0;
        
        while (minesPlaced < minesCount) {
            const row = Math.floor(Math.random() * ROWS);
            const col = Math.floor(Math.random() * COLS);
            
            // Don't place mine on first click or where a mine already exists
            if ((row === firstClickRow && col === firstClickCol) || 
                board[row][col].isMine) {
                continue;
            }
            
            board[row][col].isMine = true;
            minesPlaced++;
            
            // Update neighbor counts
            updateNeighborCounts(row, col);
        }
    }
    
    // Update neighbor counts when placing mines
    function updateNeighborCounts(row, col) {
        for (let r = Math.max(0, row - 1); r <= Math.min(ROWS - 1, row + 1); r++) {
            for (let c = Math.max(0, col - 1); c <= Math.min(COLS - 1, col + 1); c++) {
                if (r === row && c === col) continue;
                board[r][c].neighborMines++;
            }
        }
    }
    
    // Handle cell click
    function handleCellClick(e) {
        if (gameOver) return;
        
        const cell = e.target;
        const row = parseInt(cell.dataset.row);
        const col = parseInt(cell.dataset.col);
        const cellData = board[row][col];
        
        if (cellData.isRevealed || cellData.isFlagged) return;
        
        if (firstClick) {
            firstClick = false;
            placeMines(row, col);
            startTimer();
        }
        
        if (cellData.isMine) {
            // Game over
            revealAllMines();
            gameOver = true;
            clearInterval(timerInterval);
            cell.classList.add('mine');
            setTimeout(() => alert('Game Over! You hit a mine!'), 100);
            return;
        }
        
        revealCell(row, col);
        
        // Check for win
        if (revealedCount === (ROWS * COLS - minesCount)) {
            gameOver = true;
            clearInterval(timerInterval);
            flagAllMines();
            setTimeout(() => alert('Congratulations! You won in ' + timer + ' seconds!'), 100);
        }
    }
    
    // Handle right-click to place flag
    function handleRightClick(e) {
        e.preventDefault();
        if (gameOver || firstClick) return;
        
        const cell = e.target;
        const row = parseInt(cell.dataset.row);
        const col = parseInt(cell.dataset.col);
        const cellData = board[row][col];
        
        if (cellData.isRevealed) return;
        
        if (cellData.isFlagged) {
            // Remove flag
            cellData.isFlagged = false;
            cell.classList.remove('flagged');
            flagsCount--;
        } else {
            // Place flag
            cellData.isFlagged = true;
            cell.classList.add('flagged');
            flagsCount++;
        }
        
        updateMinesLeft();
    }
    
    // Reveal a cell
    function revealCell(row, col) {
        if (row < 0 || row >= ROWS || col < 0 || col >= COLS) return;
        
        const cellData = board[row][col];
        if (cellData.isRevealed || cellData.isFlagged) return;
        
        cellData.isRevealed = true;
        cellData.element.classList.add('revealed');
        revealedCount++;
        
        if (cellData.neighborMines > 0) {
            cellData.element.textContent = cellData.neighborMines;
            cellData.element.classList.add(`mine-count-${cellData.neighborMines}`);
        } else {
            // Reveal all adjacent cells if no neighboring mines
            for (let r = row - 1; r <= row + 1; r++) {
                for (let c = col - 1; c <= col + 1; c++) {
                    if (r === row && c === col) continue;
                    revealCell(r, c);
                }
            }
        }
    }
    
    // Reveal all mines (game over)
    function revealAllMines() {
        for (let row = 0; row < ROWS; row++) {
            for (let col = 0; col < COLS; col++) {
                const cellData = board[row][col];
                if (cellData.isMine) {
                    cellData.element.classList.add('mine');
                }
            }
        }
    }
    
    // Flag all mines (win)
    function flagAllMines() {
        for (let row = 0; row < ROWS; row++) {
            for (let col = 0; col < COLS; col++) {
                const cellData = board[row][col];
                if (cellData.isMine && !cellData.isFlagged) {
                    cellData.isFlagged = true;
                    cellData.element.classList.add('flagged');
                }
            }
        }
        flagsCount = minesCount;
        updateMinesLeft();
    }
    
    // Start the timer
    function startTimer() {
        clearInterval(timerInterval);
        timer = 0;
        updateTimer();
        timerInterval = setInterval(() => {
            timer++;
            updateTimer();
        }, 1000);
    }
    
    // Update timer display
    function updateTimer() {
        timerDisplay.textContent = timer;
    }
    
    // Update mines left display
    function updateMinesLeft() {
        minesLeftDisplay.textContent = minesCount - flagsCount;
    }
    
    // Event listeners
    restartButton.addEventListener('click', initGame);
    
    difficultyButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const mines = parseInt(e.target.dataset.mines);
            if (mines !== minesCount) {
                minesCount = mines;
                initGame();
            }
        });
    });
    
    // Prevent context menu on right-click
    document.addEventListener('contextmenu', (e) => {
        if (e.target.classList.contains('cell')) {
            e.preventDefault();
        }
    });
    
    // Initialize the game
    initGame();
});