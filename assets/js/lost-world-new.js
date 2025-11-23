// The Lost World - RPG
// Clean version with basic platformer mechanics

// Game state
const game = {
    // Canvas and context
    canvas: null,
    ctx: null,
    
    // Game state
    running: false,
    gameOver: false,
    scoreSaved: false,
    gameTime: 0,
    lastTime: 0,
    
    // Hero properties
    hero: {
        x: 100,
        y: 300,
        width: 50,
        height: 80,
        speed: 5,
        jumpForce: 15,
        velocityY: 0,
        isJumping: false
    },
    
    // Physics
    gravity: 0.8,
    
    // Input handling
    keys: {
        ArrowLeft: false,
        ArrowRight: false,
        ArrowUp: false,
        ' ': false
    },
    
    // Save score to server
    saveScore(score) {
        fetch('/projekti-final-1/api/save_score.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                game: 'lost-world',
                score: score,
                time: Math.floor(this.gameTime / 1000) // Convert to seconds
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Score saved:', data);
            this.scoreSaved = true;
        })
        .catch(error => {
            console.error('Error saving score:', error);
        });
    },
    
    // Initialize the game
    init() {
        console.log('Initializing game...');
        this.scoreSaved = false;
        
        // Get canvas and context
        this.canvas = document.getElementById('lostWorldCanvas');
        if (!this.canvas) {
            console.error('Canvas element not found!');
            return false;
        }
        
        this.ctx = this.canvas.getContext('2d');
        if (!this.ctx) {
            console.error('Could not get 2D context!');
            return false;
        }
        
        // Set up event listeners
        window.addEventListener('keydown', this.handleKeyDown.bind(this));
        window.addEventListener('keyup', this.handleKeyUp.bind(this));
        
        // Set up start button
        const startButton = document.getElementById('btnStartRpg');
        if (startButton) {
            startButton.addEventListener('click', this.startGame.bind(this));
        } else {
            console.error('Start button not found!');
        }
        
        // Set up fullscreen button
        const fullscreenButton = document.getElementById('btnFullscreen');
        if (fullscreenButton) {
            fullscreenButton.addEventListener('click', this.toggleFullscreen.bind(this));
        }
        
        // Initial render
        this.resizeCanvas();
        this.render();
        
        console.log('Game initialized successfully');
        return true;
    },
    
    // Start the game
    startGame() {
        console.log('Starting game...');
        const overlay = document.getElementById('lostWorldOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
        
        // Reset game state
        this.gameOver = false;
        this.gameTime = 0;
        this.hero.x = 100;
        this.hero.y = 300;
        this.hero.velocityY = 0;
        this.hero.isJumping = false;
        
        // Start game loop
        if (!this.running) {
            this.running = true;
            this.lastTime = performance.now();
            requestAnimationFrame(this.gameLoop.bind(this));
            console.log('Game loop started');
        }
    },
    
    // Game loop
    gameLoop(timestamp) {
        if (!this.running) return;
        
        // Calculate delta time
        const deltaTime = (timestamp - this.lastTime) / 1000;
        this.lastTime = timestamp;
        
        // Update game state
        this.update(deltaTime);
        
        // Render the game
        this.render();
        
        // Continue the game loop
        requestAnimationFrame(this.gameLoop.bind(this));
    },
    
    // Update game state
    update(deltaTime) {
        if (this.gameOver) {
            if (!this.scoreSaved) {
                this.saveScore(1000); // Example score, replace with actual score logic
            }
            return;
        }
        const hero = this.hero;
        
        // Update hero position
        if (this.keys.ArrowLeft) hero.x = Math.max(0, hero.x - hero.speed);
        if (this.keys.ArrowRight) hero.x = Math.min(this.canvas.width - hero.width, hero.x + hero.speed);
        
        // Apply gravity
        hero.velocityY += this.gravity;
        hero.y += hero.velocityY;
        
        // Ground collision
        if (hero.y > this.canvas.height - hero.height) {
            hero.y = this.canvas.height - hero.height;
            hero.velocityY = 0;
            hero.isJumping = false;
        }
        
        // Jump
        if ((this.keys.ArrowUp || this.keys[' ']) && !hero.isJumping) {
            hero.velocityY = -hero.jumpForce;
            hero.isJumping = true;
        }
        
        // Update game time
        this.gameTime += deltaTime;
    },
    
    // Render the game
    render() {
        const ctx = this.ctx;
        const hero = this.hero;
        
        // Clear canvas
        ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Draw background
        ctx.fillStyle = '#1a1a2e';
        ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Draw ground
        ctx.fillStyle = '#2d3436';
        ctx.fillRect(0, this.canvas.height - 50, this.canvas.width, 50);
        
        // Draw hero
        ctx.fillStyle = '#e74c3c';
        ctx.fillRect(hero.x, hero.y, hero.width, hero.height);
        
        // Draw HUD
        ctx.fillStyle = 'white';
        ctx.font = '16px Arial';
        ctx.fillText(`X: ${Math.floor(hero.x)} Y: ${Math.floor(hero.y)}`, 10, 30);
        ctx.fillText(`Time: ${Math.floor(this.gameTime)}s`, 10, 50);
    },
    
    // Handle keyboard input
    handleKeyDown(e) {
        if (this.keys.hasOwnProperty(e.key)) {
            this.keys[e.key] = true;
            e.preventDefault();
        }
    },
    
    handleKeyUp(e) {
        if (this.keys.hasOwnProperty(e.key)) {
            this.keys[e.key] = false;
            e.preventDefault();
        }
    },
    
    // Toggle fullscreen
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            if (this.canvas.requestFullscreen) {
                this.canvas.requestFullscreen();
            } else if (this.canvas.webkitRequestFullscreen) { /* Safari */
                this.canvas.webkitRequestFullscreen();
            } else if (this.canvas.msRequestFullscreen) { /* IE11 */
                this.canvas.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) { /* Safari */
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) { /* IE11 */
                document.msExitFullscreen();
            }
        }
    },
    
    // Resize canvas to fit container
    resizeCanvas() {
        const container = this.canvas.parentElement;
        if (!container) return;
        
        this.canvas.width = container.clientWidth;
        this.canvas.height = container.clientHeight;
        
        // Redraw when resized
        if (this.running) {
            this.render();
        }
    }
};

// Initialize the game when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing game...');
    if (!game.init()) {
        console.error('Failed to initialize game!');
    }
});

// Handle window resize
window.addEventListener('resize', () => {
    game.resizeCanvas();
});
