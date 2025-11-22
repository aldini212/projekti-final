<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'Leaderboard';
$activeNav = 'leaderboard';

// Get the selected game filter
$gameFilter = $_GET['game'] ?? 'all';
$difficultyFilter = $_GET['difficulty'] ?? 'all';
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

// Apply filters
if ($gameFilter !== 'all') {
    $sql .= " AND g.slug = ?";
    $params[] = $gameFilter;
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
    
} catch (PDOException $e) {
    $error = "Error fetching leaderboard: " . $e->getMessage();
    error_log($error);
    $scores = [];
    $games = [];
}

// Include header
include 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Leaderboard</h1>
    
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

<?php include 'includes/footer.php'; ?>
}