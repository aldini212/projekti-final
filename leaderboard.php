<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'Leaderboard';
$activeNav = 'leaderboard';

// Get the selected tab
$tab = $_GET['tab'] ?? 'players';

// Get the selected filters
$gameFilter = $_GET['game'] ?? 'all';
$timeFilter = $_GET['time'] ?? 'all';

// Build the SQL query
$sql = "SELECT 
            u.username,
            u.avatar,
            g.name AS game_name,
            g.slug AS game_slug,
            s.score,
            s.level,
            s.time_spent,
            DATE_FORMAT(s.created_at, '%Y-%m-%d %H:%i') as date_played
        FROM scores s
        JOIN users u ON s.user_id = u.id
        JOIN games g ON s.game_id = g.id
        WHERE 1=1";

$params = [];

if ($gameFilter !== 'all') {
    $sql .= " AND g.slug = ?";
    $params[] = $gameFilter;
}

if ($timeFilter !== 'all') {
    if ($timeFilter === 'week') {
        $sql .= " AND s.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } elseif ($timeFilter === 'month') {
        $sql .= " AND s.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }
}

// Order by score
$sql .= " ORDER BY s.score DESC LIMIT 100";

try {
    // Use the existing $pdo connection from database.php
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all games for the filter
    $games = $pdo->query("SELECT id, name, slug FROM games ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get house leaderboard data with Ipsters prioritized
    $houseLeaderboard = $pdo->query("
        SELECT 
            u.house,
            COUNT(DISTINCT s.user_id) as total_players,
            COUNT(s.id) as total_games_played,
            SUM(s.score) as total_score,
            SUM(s.xp_earned) as total_xp,
            ROUND(AVG(s.score), 2) as avg_score_per_game
        FROM 
            users u
        JOIN 
            scores s ON u.id = s.user_id
        WHERE 
            u.house IS NOT NULL
            AND u.house != 'Beginner'
        GROUP BY 
            u.house
        ORDER BY 
            CASE WHEN u.house = 'Ipsters' THEN 0 ELSE 1 END,
            total_xp DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error fetching leaderboard: " . $e->getMessage();
    error_log($error);
    $scores = [];
    $games = [];
    $houseLeaderboard = [];
}

// Include header
include 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Leaderboard</h1>
    
    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="leaderboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'players' ? 'active' : '' ?>" id="players-tab" data-bs-toggle="tab" data-bs-target="#players" type="button">
                Top Players
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'houses' ? 'active' : '' ?>" id="houses-tab" data-bs-toggle="tab" data-bs-target="#houses" type="button">
                House Rankings
            </button>
        </li>
    </ul>
    
    <div class="tab-content" id="leaderboardTabsContent">
        <!-- Players Tab -->
        <div class="tab-pane fade <?= $tab === 'players' ? 'show active' : '' ?>" id="players" role="tabpanel">
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="col-md-6">
                            <label for="game" class="form-label">Game</label>
                            <select name="game" id="game" class="form-select">
                                <option value="all" <?= $gameFilter === 'all' ? 'selected' : '' ?>>All Games</option>
                                <?php foreach ($games as $game): ?>
                                    <option value="<?= htmlspecialchars($game['slug']) ?>" 
                                            <?= $gameFilter === $game['slug'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($game['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="time" class="form-label">Time Period</label>
                            <select name="time" id="time" class="form-select">
                                <option value="all" <?= $timeFilter === 'all' ? 'selected' : '' ?>>All Time</option>
                                <option value="week" <?= $timeFilter === 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                                <option value="month" <?= $timeFilter === 'month' ? 'selected' : '' ?>>Last 30 Days</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php elseif (empty($scores)): ?>
                <div class="alert alert-info">No scores found. Be the first to play and set a high score!</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Player</th>
                                <th>Game</th>
                                <th class="text-end">Score</th>
                                <th class="text-center">Level</th>
                                <th class="text-center">Time</th>
                                <th class="text-end">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($scores as $index => $score): ?>
                                <tr>
                                    <td class="fw-bold"><?= $index + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= htmlspecialchars($score['avatar'] ?? 'assets/images/default-avatar.png') ?>" 
                                                 alt="<?= htmlspecialchars($score['username']) ?>" 
                                                 class="rounded-circle me-2" 
                                                 width="32" 
                                                 height="32" 
                                                 onerror="this.src='assets/images/default-avatar.png'"
                                            >
                                            <?= htmlspecialchars($score['username']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($score['game_name']) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($score['score']) ?></td>
                                    <td class="text-center"><?= $score['level'] ?></td>
                                    <td class="text-center"><?= $score['time_spent'] ?>s</td>
                                    <td class="text-end text-muted small"><?= $score['date_played'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Houses Tab -->
        <div class="tab-pane fade <?= $tab === 'houses' ? 'show active' : '' ?>" id="houses" role="tabpanel">
            <?php if (!empty($houseLeaderboard)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>House</th>
                                <th class="text-end">Total XP</th>
                                <th class="text-end">Players</th>
                                <th class="text-end">Games Played</th>
                                <th class="text-end">Avg. Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($houseLeaderboard as $index => $house): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <!-- Fixed double .png extension to single .png -->
                                            <img src="assets/images/houses/<?= strtolower(str_replace(' ', '-', $house['house'])) ?>.png" 
                                                 alt="<?= htmlspecialchars($house['house']) ?>" 
                                                 class="rounded-circle me-2" 
                                                 style="width: 32px; height: 32px; object-fit: cover;"
                                                 onerror="this.style.display='none'">
                                            <span class="fw-bold"><?= htmlspecialchars($house['house']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-primary">
                                            <?= number_format($house['total_xp']) ?> XP
                                        </span>
                                    </td>
                                    <td class="text-end"><?= $house['total_players'] ?></td>
                                    <td class="text-end"><?= number_format($house['total_games_played']) ?></td>
                                    <td class="text-end"><?= number_format($house['avg_score_per_game'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No house data available yet. Play some games to see house rankings!</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Handle tab state in URL
const leaderboardTabs = document.querySelectorAll('#leaderboardTabs .nav-link');
leaderboardTabs.forEach(tab => {
    tab.addEventListener('click', (e) => {
        const tabId = e.target.getAttribute('data-bs-target').substring(1);
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.pushState({}, '', url);
    });
});
</script>

<?php include 'includes/footer.php'; ?>
