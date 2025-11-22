// Main JavaScript File for GameHub

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Game card hover effect
    const gameCards = document.querySelectorAll('.game-card, .game-card-lobby');
    gameCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.1)';
        });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert.auto-dismiss');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Game-related functions
class GameHub {
    // Initialize a game
    static initGame(gameId, options = {}) {
        console.log(`Initializing game ${gameId} with options:`, options);
        // Game initialization logic will be added for each game
    }

    // Save game score
    static saveScore(gameId, score, callback) {
        fetch('api/save_score.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                game_id: gameId,
                score: score
            })
        })
        .then(response => response.json())
        .then(data => {
            if (typeof callback === 'function') {
                callback(data);
            }
        })
        .catch(error => {
            console.error('Error saving score:', error);
        });
    }

    // Get leaderboard for a game
    static getLeaderboard(gameId, limit = 10) {
        return fetch(`api/get_leaderboard.php?game_id=${gameId}&limit=${limit}`)
            .then(response => response.json())
            .catch(error => {
                console.error('Error fetching leaderboard:', error);
                return [];
            });
    }

    // Update user stats
    static updateStats() {
        fetch('api/get_user_stats.php')
            .then(response => response.json())
            .then(stats => {
                // Update UI with new stats
                const statsElements = {
                    'total-points': stats.points || 0,
                    'games-played': stats.games_played || 0,
                    'badges-earned': stats.badges_earned || 0,
                    'user-rank': stats.rank || 'N/A'
                };

                Object.entries(statsElements).forEach(([id, value]) => {
                    const element = document.getElementById(id);
                    if (element) {
                        // If it's a number, animate the count
                        if (typeof value === 'number') {
                            this.animateValue(element, 0, value, 1000);
                        } else {
                            element.textContent = value;
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error updating stats:', error);
            });
    }

    // Animate number counting
    static animateValue(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            element.textContent = value.toLocaleString();
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    // Show game modal
    static showGameModal(gameId, title) {
        const modal = new bootstrap.Modal(document.getElementById('gameModal'));
        const modalTitle = document.getElementById('gameModalLabel');
        const modalBody = document.getElementById('gameModalBody');
        
        modalTitle.textContent = title || 'Loading Game...';
        modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading game, please wait...</p></div>';
        
        // Load game content via AJAX
        fetch(`games/${gameId}/index.html`)
            .then(response => response.text())
            .then(html => {
                modalBody.innerHTML = html;
                // Initialize the game
                if (window.initGame) {
                    window.initGame();
                }
            })
            .catch(error => {
                console.error('Error loading game:', error);
                modalBody.innerHTML = '<div class="alert alert-danger">Error loading game. Please try again later.</div>';
            });
        
        modal.show();
        
        // Clean up when modal is closed
        const modalElement = document.getElementById('gameModal');
        modalElement.addEventListener('hidden.bs.modal', function() {
            modalBody.innerHTML = '';
            if (window.gameCleanup) {
                window.gameCleanup();
            }
        }, { once: true });
    }
}

// Expose to window
window.GameHub = GameHub;

// Initialize any game-specific scripts if they exist
if (typeof initGamePage !== 'undefined') {
    document.addEventListener('DOMContentLoaded', initGamePage);
}
