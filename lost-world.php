<?php
$pageTitle = 'The Lost World - RPG';
require_once 'header.php';
?>

<style>
    .game-hero {
        background: linear-gradient(135deg, rgba(76, 201, 240, 0.2), rgba(190, 24, 93, 0.3));
        border-radius: 16px;
    }

    .rpg-orb {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        margin: 0 auto;
        position: relative;
        background: radial-gradient(circle at 30% 30%, #f9fafb, #22d3ee, #0f172a);
        box-shadow: 0 0 40px rgba(56, 189, 248, 0.7);
        overflow: hidden;
        animation: rpgOrbPulse 6s ease-in-out infinite;
    }

    .rpg-orb-inner {
        position: absolute;
        inset: 14%;
        border-radius: 50%;
        background: radial-gradient(circle at 20% 10%, rgba(248, 250, 252, 0.9), transparent 60%),
                    radial-gradient(circle at 80% 90%, rgba(56, 189, 248, 0.5), transparent 60%);
        mix-blend-mode: screen;
        filter: blur(0.5px);
    }

    @keyframes rpgOrbPulse {
        0%, 100% { transform: scale(1); box-shadow: 0 0 40px rgba(56, 189, 248, 0.7); }
        50% { transform: scale(1.04); box-shadow: 0 0 60px rgba(244, 114, 182, 0.9); }
    }

    .rpg-canvas-wrapper {
        position: relative;
        width: 100%;
        padding-top: 56.25%;
        border-radius: 16px;
        overflow: hidden;
        /* Place your own sky image at assets/images/my-sky.jpg to change the background */
        background-image: url('assets/images/my-sky.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.7);
    }

    #lostWorldCanvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        display: block;
    }

    .rpg-overlay {
        position: absolute;
        inset: 0;
        padding: .75rem .9rem;
        pointer-events: none;
        background: linear-gradient(to bottom, rgba(15, 23, 42, 0.78), transparent 35%, rgba(15, 23, 42, 0.9));
        color: #e5e7eb;
        font-size: 0.85rem;
    }

    .rpg-bars .rpg-bar {
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .rpg-bar-label {
        width: 2.2rem;
        text-transform: uppercase;
        font-size: 0.7rem;
        color: #9ca3af;
    }

    .rpg-bar-track {
        flex: 1;
        height: 3px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.9);
        overflow: hidden;
    }

    .rpg-bar-fill {
        height: 100%;
        width: 0;
        transition: width 0.3s ease-out;
    }

    .rpg-bar-fill.hp { background: linear-gradient(90deg, #ef4444, #f97316); }
    .rpg-bar-fill.mana { background: linear-gradient(90deg, #3b82f6, #22d3ee); }
    .rpg-bar-fill.xp { background: linear-gradient(90deg, #a855f7, #f97316); }

    .rpg-bars {
        max-width: 55%;
    }

    /* Center-screen XP popup */
    .xp-popup-center {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        padding: 0.75rem 1.25rem;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.95);
        color: #bbf7d0;
        font-weight: 700;
        font-size: 1.1rem;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.6);
        z-index: 1100;
        opacity: 0;
        pointer-events: none;
        animation: xpPop 1.3s ease-out forwards;
    }

    @keyframes xpPop {
        0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0; }
        20% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        80% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        100% { transform: translate(-50%, -60%) scale(0.9); opacity: 0; }
    }

    .rpg-inventory,
    .rpg-quests {
        min-height: 90px;
        max-height: 180px;
        padding: .75rem .9rem;
        border-radius: 12px;
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(148, 163, 184, 0.25);
        overflow-y: auto;
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="game-hero glass-card p-4 mb-4 text-white">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h1 class="display-5 fw-bold mb-3">The Lost World</h1>
                    <p class="lead mb-3">
                        Një lojë aventurë RPG ku udhëton në një botë fantastike, lufton monstera,
                        mbledh objekte dhe plotëson misione për të gjetur një thesar të humbur.
                    </p>
                    <ul class="mb-3 small">
                        <li>Exploration në një botë të hapur</li>
                        <li>Beteja kundër monstreve me shpërblime XP</li>
                        <li>Quests që çelin zona të reja</li>
                        <li>Inventory system për objekte dhe gear</li>
                    </ul>
                    <div class="d-flex gap-2 mt-2">
                        <button id="btnStartRpg" class="btn btn-primary btn-lg">
                            <i class="bi bi-controller me-2"></i>Start Adventure
                        </button>
                        <button id="btnFullscreen" class="btn btn-outline-light">
                            <i class="bi bi-arrows-fullscreen me-1"></i>Fullscreen
                        </button>
                    </div>
                </div>
                <div class="col-md-5 text-center">
                    <div class="rpg-orb mb-3">
                        <div class="rpg-orb-inner"></div>
                    </div>
                    <p class="small text-muted">Tipi: RPG, Aventurë • Single Player (MVP)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center mb-5">
    <div class="col-lg-10">
        <div id="lostWorldGameCard" class="glass-card p-3 p-md-4">
            <div class="row g-3 g-md-4">
                <div class="col-md-8">
                    <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
                        <div>HP: <span id="playerHpValue">100</span>/100</div>
                        <div>STR: <span id="rpgStrength">10</span> • Wave <span id="rpgWave">1</span></div>
                    </div>
                    <div class="rpg-canvas-wrapper">
                        <canvas id="lostWorldCanvas"></canvas>
                        <div id="lostWorldOverlay" class="rpg-overlay d-flex flex-column justify-content-between">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <div class="small text-uppercase text-muted">Hero</div>
                                    <div class="fw-bold" id="rpgPlayerName">Adventurer</div>
                                </div>
                                <div class="text-end">
                                    <div class="small text-uppercase text-muted">Location</div>
                                    <div class="fw-bold" id="rpgLocation">The Forgotten Forest</div>
                                </div>
                            </div>

                            <div class="rpg-bars mb-2">
                                <div class="rpg-bar mb-1">
                                    <div class="rpg-bar-label">HP</div>
                                    <div class="rpg-bar-track">
                                        <div class="rpg-bar-fill hp" id="rpgHpBar"></div>
                                    </div>
                                </div>
                                <div class="rpg-bar mb-1">
                                    <div class="rpg-bar-label">Mana</div>
                                    <div class="rpg-bar-track">
                                        <div class="rpg-bar-fill mana" id="rpgManaBar"></div>
                                    </div>
                                </div>
                                <div class="rpg-bar">
                                    <div class="rpg-bar-label">XP</div>
                                    <div class="rpg-bar-track">
                                        <div class="rpg-bar-fill xp" id="rpgXpBar"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between small text-muted">
                                <span>Quest: <span id="rpgCurrentQuest">Explore the forest</span></span>
                                <span>Lvl <span id="rpgLevel">1</span> • Gold: <span id="rpgGold">0</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <h5 class="text-white mb-2">Controls</h5>
                        <ul class="small text-white mb-0">
                            <li>W / A / S / D ose Shigjetat – Lëvizja</li>
                            <li>Space – Sulm i shpejtë</li>
                            <li>E – Interact / Open Chest</li>
                            <li>Q – Hap / mbyll Inventory</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <h5 class="text-white mb-2">Inventory</h5>
                        <div id="rpgInventory" class="rpg-inventory small text-white-50">
                            Nuk ke ende objekte... eksploro botën për të gjetur loot.
                        </div>
                    </div>
                    <div>
                        <h5 class="text-white mb-2">Quest Log</h5>
                        <div id="rpgQuestLog" class="rpg-quests small text-white-50">
                            - [Aktiv] Hapi i parë: Lëviz nëpër pyll për të gjetur një kamp të braktisur.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Death Screen Modal -->
<div id="deathModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border border-danger">
            <div class="modal-header border-danger">
                <h3 class="modal-title text-danger"><i class="bi bi-emoji-dizzy-fill me-2"></i>You Died!</h3>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="death-icon mb-3">
                    <i class="bi bi-heartbreak-fill text-danger" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-light mb-4">Your adventure has come to an end...</h4>
                <div class="stats mb-4">
                    <div class="mb-2">
                        <span class="text-muted">Level:</span>
                        <span class="ms-2 fw-bold" id="deathLevel">1</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted">Enemies Defeated:</span>
                        <span class="ms-2 fw-bold" id="deathEnemies">0</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted">Time Survived:</span>
                        <span class="ms-2 fw-bold" id="deathTime">0:00</span>
                    </div>
                </div>
                <div class="d-flex justify-content-center gap-3">
                    <button id="retryBtn" class="btn btn-danger btn-lg px-4">
                        <i class="bi bi-arrow-clockwise me-2"></i>Try Again
                    </button>
                    <button id="quitBtn" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-house-door me-2"></i>Main Menu
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global death screen functions
const deathScreen = {
    show: function(stats = {}) {
        // Update stats if provided
        if (stats.level) document.getElementById('deathLevel').textContent = stats.level;
        if (stats.enemies) document.getElementById('deathEnemies').textContent = stats.enemies;
        if (stats.time) document.getElementById('deathTime').textContent = stats.time;
        
        // Show the modal
        const deathModal = new bootstrap.Modal(document.getElementById('deathModal'));
        deathModal.show();
        
        // Play death sound if available
        if (typeof playSound === 'function') {
            playSound('death');
        }
    },
    
    init: function() {
        // Set up event listeners
        document.getElementById('retryBtn').addEventListener('click', function() {
            // Add any cleanup or reset logic here
            window.location.reload();
        });
        
        document.getElementById('quitBtn').addEventListener('click', function() {
            window.location.href = 'index.php';
        });
    }
};

// Initialize death screen when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    deathScreen.init();
    
    // Example of how to trigger the death screen:
    // deathScreen.show({
    //     level: 5,
    //     enemies: 23,
    //     time: '2:45'
    // });
});
</script>

<!-- Death Screen Overlay -->
<div id="deathScreen" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    z-index: 1000;
    justify-content: center;
    align-items: center;
    color: white;
    text-align: center;
    font-family: 'Segoe UI', Arial, sans-serif;">
    <div style="
        background: #1a1a2e;
        padding: 2.5rem;
        border-radius: 15px;
        border: 2px solid #e94560;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 0 30px rgba(233, 69, 96, 0.3);
        animation: fadeIn 0.5s ease-out;">
        
        <div style="font-size: 5rem; margin-bottom: 1rem;">💀</div>
        <h2 style="color: #e94560; margin: 0 0 1.5rem 0; font-size: 2.5rem; text-transform: uppercase; letter-spacing: 2px;">Game Over</h2>
        
        <div style="
            background: rgba(0, 0, 0, 0.3);
            padding: 1.2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: left;
            border-left: 4px solid #e94560;">
            <div style="margin-bottom: 0.8rem; font-size: 1.1rem;">
                <span style="color: #8d99ae;">Level:</span>
                <span id="deathLevel" style="float: right; font-weight: bold; color: #fff;">1</span>
            </div>
            <div style="margin-bottom: 0.8rem; font-size: 1.1rem;">
                <span style="color: #8d99ae;">Enemies Defeated:</span>
                <span id="deathEnemies" style="float: right; font-weight: bold; color: #fff;">0</span>
            </div>
            <div style="font-size: 1.1rem;">
                <span style="color: #8d99ae;">Time Survived:</span>
                <span id="deathTime" style="float: right; font-weight: bold; color: #fff;">0:00</span>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <button id="retryBtn" style="
                background: #e94560;
                color: white;
                border: none;
                padding: 0.9rem 2rem;
                font-size: 1.1rem;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s ease;
                text-transform: uppercase;
                letter-spacing: 1px;
                box-shadow: 0 4px 0 #b13146;
                transform: translateY(0);
            " onmouseover="this.style.background='#ff5c77'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 0 #b13146'" 
              onmouseout="this.style.background='#e94560'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 0 #b13146'"
              onmousedown="this.style.transform='translateY(2px)'; this.style.boxShadow='0 2px 0 #b13146'"
              onmouseup="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 0 #b13146'">
                🔄 Try Again
            </button>
            <button id="quitBtn" style="
                background: transparent;
                color: #fff;
                border: 2px solid #4a4e69;
                padding: 0.9rem 2rem;
                font-size: 1.1rem;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s ease;
                text-transform: uppercase;
                letter-spacing: 1px;
            " onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.borderColor='#6c7289'" 
              onmouseout="this.style.background='transparent'; this.style.borderColor='#4a4e69'">
                🏠 Main Menu
            </button>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

#retryBtn:active {
    transform: translateY(2px) !important;
    box-shadow: 0 2px 0 #b13146 !important;
}
</style>

<script>
// Death Screen Functions
const deathScreen = {
    element: document.getElementById('deathScreen'),
    
    show: function(stats = {}) {
        // Update stats if provided
        if (stats.level) document.getElementById('deathLevel').textContent = stats.level;
        if (stats.enemies) document.getElementById('deathEnemies').textContent = stats.enemies;
        if (stats.time) document.getElementById('deathTime').textContent = stats.time;
        
        // Show the death screen
        this.element.style.display = 'flex';
        
        // Play death sound if available
        if (typeof playSound === 'function') {
            playSound('death');
        }
        
        // Pause the game if needed
        if (typeof gameLoop !== 'undefined') {
            cancelAnimationFrame(gameLoop);
        }
    },
    
    hide: function() {
        this.element.style.display = 'none';
    },
    
    init: function() {
        // Set up event listeners
        document.getElementById('retryBtn').addEventListener('click', function() {
            window.location.reload();
        });
        
        document.getElementById('quitBtn').addEventListener('click', function() {
            window.location.href = 'index.php';
        });
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    deathScreen.init();
    
    // Example of how to show the death screen:
    // deathScreen.show({
    //     level: 5,
    //     enemies: 23,
    //     time: '2:45'
    // });
});
</script>

<script src="assets/js/lost-world.js"></script>
<?php require_once 'footer.php'; ?>
