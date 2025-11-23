<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user data
$user = fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Initialize game data
$gameData = [
    'level' => 1,
    'xp' => 0,
    'gold' => 0,
    'health' => 100,
    'max_health' => 100,
    'inventory' => [],
    'current_quest' => 'Explore the forest',
    'quests' => [
        'Explore the forest' => 'Find the abandoned camp in the forest.',
        'Defeat the goblin' => 'Eliminate the goblin threatening the village.',
        'Find the artifact' => 'Locate the ancient artifact in the ruins.'
    ]
];

// Save/load game data from session
if (!isset($_SESSION['game_data'])) {
    $_SESSION['game_data'] = $gameData;
} else {
    $gameData = $_SESSION['game_data'];
}

// Handle game actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'attack':
            // Handle attack logic
            $gameData['xp'] += 10;
            $gameData['gold'] += rand(1, 5);
            break;
            
        case 'collect':
            // Handle item collection
            $items = ['Health Potion', 'Gold Coin', 'Sword', 'Shield'];
            $item = $items[array_rand($items)];
            $gameData['inventory'][] = $item;
            break;
            
        case 'heal':
            // Handle healing
            $gameData['health'] = min($gameData['max_health'], $gameData['health'] + 20);
            break;
    }
    
    // Save updated game data
    $_SESSION['game_data'] = $gameData;
}

// Prepare data for the view
$gameTitle = "Lost World RPG";
$pageTitle = "Lost World - $gameTitle";
$userName = $user['username'];
$userHouse = $user['house'] ?? 'Adventurer';
$userAvatar = !empty($user['avatar']) ? "assets/uploads/avatars/{$user['avatar']}" : 'assets/images/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary: #6c5ce7;
            --primary-dark: #5a4bc9;
            --secondary: #a29bfe;
            --dark: #1a1a2e;
            --darker: #16213e;
            --light: #f8f9fa;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
        }
        
        body {
            background: var(--darker);
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('assets/images/gaming-bg.jpg');
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
        }
        
        .game-container {
            background: rgba(22, 33, 62, 0.9);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            padding: 2rem;
            margin: 2rem 0;
        }
        
        .game-header {
            border-bottom: 2px solid var(--primary);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .game-title {
            color: var(--primary);
            font-weight: 800;
            text-shadow: 0 0 10px rgba(108, 92, 231, 0.5);
        }
        
        .game-stats {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-bar {
            height: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }
        
        .stat-fill {
            height: 100%;
            background: var(--primary);
            transition: width 0.3s ease;
        }
        
        .hp-fill { background: var(--danger); }
        .xp-fill { background: var(--success); }
        
        .inventory-item {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
        }
        
        .inventory-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        
        .quest-log {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        .quest-item {
            padding: 0.5rem;
            border-left: 3px solid var(--primary);
            margin-bottom: 0.5rem;
            background: rgba(0, 0, 0, 0.2);
        }
        
        .quest-item.active {
            border-left-color: var(--success);
        }
        
        .game-controls .btn {
            margin: 0.25rem;
            min-width: 120px;
        }
        
        .game-log {
            background: rgba(0, 0, 0, 0.5);
            border-radius: 5px;
            padding: 10px;
            height: 150px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 14px;
        }
        
        .log-entry {
            margin-bottom: 5px;
            padding-bottom: 5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .text-success { color: var(--success) !important; }
        .text-warning { color: var(--warning) !important; }
        .text-danger { color: var(--danger) !important; }
        .text-info { color: var(--primary) !important; }
    </style>
</head>
<body>
    <div class="container">
        <div class="game-container">
            <div class="game-header text-center">
                <h1 class="game-title"><?= htmlspecialchars($gameTitle) ?></h1>
                <div class="text-muted">Welcome back, <?= htmlspecialchars($userName) ?> of <?= htmlspecialchars($userHouse) ?></div>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <!-- Game Area -->
                    <div class="game-area mb-4" style="background: rgba(0, 0, 0, 0.3); height: 400px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <h3 class="text-center text-muted">Game World</h3>
                    </div>
                    
                    <!-- Stats -->
                    <div class="game-stats">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Health: <?= $gameData['health'] ?>/<?= $gameData['max_health'] ?></span>
                                        <span>Level: <?= $gameData['level'] ?></span>
                                    </div>
                                    <div class="stat-bar">
                                        <div class="stat-fill hp-fill" style="width: <?= ($gameData['health'] / $gameData['max_health']) * 100 ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>XP: <?= $gameData['xp'] ?>/100</span>
                                        <span>Gold: <?= $gameData['gold'] ?></span>
                                    </div>
                                    <div class="stat-bar">
                                        <div class="stat-fill xp-fill" style="width: <?= ($gameData['xp'] % 100) ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Controls -->
                    <div class="game-controls text-center mb-4">
                        <button class="btn btn-primary" onclick="gameAction('attack')">
                            <i class="bi bi-sword me-1"></i> Attack
                        </button>
                        <button class="btn btn-success" onclick="gameAction('collect')">
                            <i class="bi bi-bag-plus me-1"></i> Collect
                        </button>
                        <button class="btn btn-warning" onclick="gameAction('heal')">
                            <i class="bi bi-heart me-1"></i> Heal
                        </button>
                    </div>
                    
                    <!-- Game Log -->
                    <div class="game-log" id="gameLog">
                        <div class="log-entry text-muted">Game log will appear here...</div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Inventory -->
                    <div class="card bg-dark mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-backpack me-2"></i> Inventory
                        </div>
                        <div class="card-body p-2" id="inventoryList">
                            <?php if (empty($gameData['inventory'])): ?>
                                <div class="text-center text-muted py-3">Your inventory is empty</div>
                            <?php else: ?>
                                <?php foreach ($gameData['inventory'] as $item): ?>
                                    <div class="inventory-item"><?= htmlspecialchars($item) ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quest Log -->
                    <div class="card bg-dark">
                        <div class="card-header bg-success text-white">
                            <i class="bi bi-journal-text me-2"></i> Quest Log
                        </div>
                        <div class="card-body p-2 quest-log">
                            <?php foreach ($gameData['quests'] as $quest => $description): ?>
                                <div class="quest-item <?= $quest === $gameData['current_quest'] ? 'active' : '' ?>">
                                    <strong><?= htmlspecialchars($quest) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($description) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Game state
        const gameState = {
            player: {
                health: <?= $gameData['health'] ?>,
                maxHealth: <?= $gameData['max_health'] ?>,
                level: <?= $gameData['level'] ?>,
                xp: <?= $gameData['xp'] ?>,
                gold: <?= $gameData['gold'] ?>
            },
            inventory: <?= json_encode($gameData['inventory']) ?>
        };

        // Game actions
        async function gameAction(action) {
            try {
                const response = await fetch('lost-world.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=' + action
                });
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                // Reload the page to show updated game state
                window.location.reload();
                
            } catch (error) {
                console.error('Error:', error);
                addToLog('Error performing action: ' + error.message, 'danger');
            }
        }
        
        // Add message to game log
        function addToLog(message, type = 'info') {
            const log = document.getElementById('gameLog');
            const entry = document.createElement('div');
            entry.className = `log-entry text-${type}`;
            entry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            log.prepend(entry);
            
            // Keep log to 10 entries max
            while (log.children.length > 10) {
                log.removeChild(log.lastChild);
            }
        }
        
        // Initialize game log with welcome message
        document.addEventListener('DOMContentLoaded', () => {
            addToLog('Welcome to Lost World!', 'success');
            addToLog('Use the controls to start your adventure.', 'info');
        });
    </script>
</body>
</html>
