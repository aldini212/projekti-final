<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$pageTitle = 'My Profile';
$activeNav = 'profile';

// Include header
include 'includes/header.php';

// Get user data
try {
    // Single optimized query to get all user data
    $userId = $_SESSION['user_id'];
    
    // Start transaction for better performance with multiple queries
    $pdo->beginTransaction();
    
    // Update games_played counter based on scores
    $updateStmt = $pdo->prepare("
        UPDATE users 
        SET games_played = (
            SELECT COUNT(DISTINCT game_id) 
            FROM scores 
            WHERE user_id = ?
        )
        WHERE id = ?
    ");
    $updateStmt->execute([$userId, $userId]);
    
    // Get user info
    $user = fetch("
        SELECT u.*, up.full_name, up.bio, up.location, up.website, up.twitter, up.facebook, up.instagram
        FROM users u
        LEFT JOIN user_profiles up ON u.id = up.user_id
        WHERE u.id = ?
        LIMIT 1
    ", [$userId]);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Get user's recent scores (optimized query with specific columns)
    $recentScores = fetchAll("
        SELECT id, game_id, score, created_at 
        FROM scores 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ", [$userId]);

    // Get user's top scores (optimized with specific columns)
    $topScores = fetchAll("
        SELECT id, game_id, score, created_at
        FROM scores 
        WHERE user_id = ? 
        ORDER BY score DESC 
        LIMIT 5
    ", [$userId]);

    // Get user's badges with error handling
    $badges = [];
    try {
        // First check if tables exist
        $tables = $pdo->query("SHOW TABLES LIKE 'badges'")->rowCount() > 0 && 
                 $pdo->query("SHOW TABLES LIKE 'user_badges'")->rowCount() > 0;
        
        if ($tables) {
            $badges = fetchAll("
                SELECT b.id, b.name, b.description, 
                       COALESCE(b.color, '#6c757d') as color, 
                       ub.earned_at,
                       CONCAT('badge-', LOWER(REPLACE(REPLACE(b.name, ' ', '-'), '.', ''))) as badge_class
                FROM badges b
                JOIN user_badges ub ON b.id = ub.badge_id
                WHERE ub.user_id = ?
                ORDER BY ub.earned_at DESC
                LIMIT 12
            ", [$userId]);
        }
    } catch (Exception $e) {
        // Log the error but don't break the page
        error_log("Error loading badges: " . $e->getMessage());
    }
    
    // Commit transaction
    $pdo->commit();

    // Calculate level based on points
    $level = $user['level'] ?? 1;
    $points = $user['points'] ?? 0;
    $nextLevelPoints = $level * 1000;
    $currentLevelPoints = ($level - 1) * 1000;
    $progress = $nextLevelPoints > 0 
        ? (($points - $currentLevelPoints) / ($nextLevelPoints - $currentLevelPoints)) * 100 
        : 0;

} catch (Exception $e) {
    $error = "Error loading profile: " . $e->getMessage();
    error_log($error);
}

?>

<div class="container py-3">
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="loadingToast" class="toast hide" role="status" aria-live="polite" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Loading</strong>
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Left Sidebar -->
            <div class="col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-4">
                        <!-- User Profile Header -->
                        <div class="text-center mb-4">
                            <?php if (!empty($user['house'])): 
                                $house = strtolower($user['house']);
                                $imgPath = "assets/images/houses/" . strtolower($user['house']) . ".png";
                                if (!file_exists($imgPath)) {
                                    $imgPath = "assets/images/houses/default.png";
                                }
                                $houseDescriptions = [
                                    'Hipster' => 'Creativity & Style',
                                    'Speedster' => 'Speed & Agility',
                                    'Shadow' => 'Stealth & Strategy',
                                    'Beginner' => 'Potential & Growth'
                                ];
                                $description = $houseDescriptions[$user['house']] ?? 'Member';
                            ?>
                                <div class="position-relative d-inline-block mb-3">
                                    <img src="<?php echo $imgPath; ?>" 
                                         alt="<?php echo htmlspecialchars($user['house']); ?>" 
                                         class="img-thumbnail rounded-circle border-3"
                                         style="width: 120px; height: 120px; object-fit: cover;">
                                    <span class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 border border-3 border-white">
                                        <i class="bi bi-trophy"></i> <?= $level ?>
                                    </span>
                                </div>
                                <h4 class="mb-1"><?= htmlspecialchars($user['username']) ?></h4>
                                <span class="badge bg-primary mb-3"><?= htmlspecialchars($user['house']) ?> House</span>
                                <p class="text-muted small mb-3"><?= $description ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- User Stats -->
                        <div class="border-top border-bottom py-3 mb-4">
                            <div class="row text-center">
                                <div class="col-6 col-sm-4 col-lg-3">
                                    <div class="text-center">
                                        <div class="h4 mb-1"><?= number_format($user['level']) ?></div>
                                        <div class="small text-muted">Level</div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-4 col-lg-3">
                                    <div class="text-center">
                                        <div class="h4 mb-1"><?= number_format($user['games_played'] ?? 0) ?></div>
                                        <div class="small text-muted">Games Played</div>
                                    </div>
                                </div>
                                <div class="col-4 border-start border-end">
                                    <div class="h5 mb-1"><?= $totalGamesPlayed = count($recentScores) + count($topScores) > 0 ? count($recentScores) + count($topScores) : 0 ?></div>
                                    <div class="text-muted small">Games</div>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-1"><?= $user['level'] ?? 1 ?></div>
                                    <div class="text-muted small">Level</div>
                                </div>
                            </div>
                        </div>
                        
                        <h3 class="h4 mb-1"><?= htmlspecialchars($user['username']) ?></h3>
                        <?php if (!empty($user['full_name'])): ?>
                            <p class="text-muted mb-3"><?= htmlspecialchars($user['full_name']) ?></p>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-center gap-3 mb-4">
                            <?php if (!empty($user['website'])): ?>
                                <a href="<?= htmlspecialchars($user['website']) ?>" class="text-decoration-none text-primary" target="_blank" title="Website">
                                    <i class="bi bi-globe fs-5"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($user['twitter'])): ?>
                                <a href="https://twitter.com/<?= htmlspecialchars(ltrim($user['twitter'], '@')) ?>" class="text-decoration-none text-info" target="_blank" title="Twitter">
                                    <i class="bi bi-twitter-x fs-5"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($user['facebook'])): ?>
                                <a href="https://facebook.com/<?= htmlspecialchars($user['facebook']) ?>" class="text-decoration-none text-primary" target="_blank" title="Facebook">
                                    <i class="bi bi-facebook fs-5"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($user['instagram'])): ?>
                                <a href="https://instagram.com/<?= htmlspecialchars(ltrim($user['instagram'], '@')) ?>" class="text-decoration-none text-danger" target="_blank" title="Instagram">
                                    <i class="bi bi-instagram fs-5"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <a href="edit-profile.php" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-pencil-square me-1"></i> Edit Profile
                        </a>
                        
                        <?php if (!empty($user['bio'])): ?>
                            <div class="mt-4 text-start">
                                <h6 class="text-uppercase text-muted mb-3">About Me</h6>
                                <p class="small"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Profile Stats Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded-3">
                                    <div class="text-primary mb-2">
                                        <i class="bi bi-trophy fs-1"></i>
                                    </div>
                                    <h4 class="h5 mb-1">Level <?= $level ?></h4>
                                    <p class="text-muted small mb-0">Current Level</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded-3">
                                    <div class="text-success mb-2">
                                        <i class="bi bi-star-fill fs-1"></i>
                                    </div>
                                    <h4 class="h5 mb-1"><?= number_format($points) ?></h4>
                                    <p class="text-muted small mb-0">Total XP</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded-3">
                                    <div class="text-warning mb-2">
                                        <i class="bi bi-joystick fs-1"></i>
                                    </div>
                                    <h4 class="h5 mb-1"><?= count($recentScores) ?></h4>
                                    <p class="text-muted small mb-0">Games Played</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Games Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Activity</h5>
                        <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        // Get user's recent games with scores
                        $recentGames = fetchAll("
                            SELECT g.id, g.name, g.slug, g.thumbnail, 
                                   MAX(s.score) as high_score, 
                                   COUNT(s.id) as games_played,
                                   MAX(s.created_at) as last_played
                            FROM games g
                            LEFT JOIN scores s ON g.id = s.game_id
                            WHERE s.user_id = ?
                            GROUP BY g.id
                            ORDER BY last_played DESC
                            LIMIT 6
                        ", [$userId]);
                        
                        if (!empty($recentGames)): ?>
                            <div class="row g-3 p-3">
                                <?php foreach ($recentGames as $game): ?>
                                    <div class="col-md-4 col-6">
                                        <a href="game.php?slug=<?= htmlspecialchars($game['slug']) ?>" class="text-decoration-none text-dark">
                                            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                                <div class="position-relative">
                                                    <img src="<?= htmlspecialchars($game['thumbnail'] ?? 'assets/images/game-placeholder.jpg') ?>" 
                                                         class="card-img-top" 
                                                         alt="<?= htmlspecialchars($game['name']) ?>"
                                                         style="height: 120px; object-fit: cover;">
                                                    <div class="position-absolute top-0 end-0 m-2">
                                                        <span class="badge bg-primary">
                                                            <i class="bi bi-trophy-fill me-1"></i> 
                                                            <?= number_format($game['high_score']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="card-body p-3">
                                                    <h6 class="card-title mb-1 text-truncate"><?= htmlspecialchars($game['name']) ?></h6>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">
                                                            <i class="bi bi-controller me-1"></i> 
                                                            <?= $game['games_played'] ?> play<?= $game['games_played'] != 1 ? 's' : '' ?>
                                                        </small>
                                                        <small class="text-muted">
                                                            <?= time_elapsed_string($game['last_played']) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center pb-3">
                                <a href="games.php?filter=played" class="btn btn-outline-primary btn-sm">
                                    View All Games
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-joystick display-4 text-muted mb-3"></i>
                                <p class="text-muted">No games played yet</p>
                                <a href="games.php" class="btn btn-primary">
                                    <i class="bi bi-joystick me-1"></i> Play Games
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <ul class="nav nav-pills nav-fill mb-4" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="recent-scores-tab" data-bs-toggle="tab" data-bs-target="#recent-scores" type="button" role="tab">
                            <i class="bi bi-clock-history me-2"></i> Recent Activity
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="achievements-tab" data-bs-toggle="tab" data-bs-target="#achievements" type="button" role="tab">
                            <i class="bi bi-award me-2"></i> Achievements
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="profileTabContent">
                    <div class="position-fixed top-50 start-50 translate-middle" id="loadingOverlay" style="z-index: 1050; display: none;">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    
                    <!-- Recent Scores -->
                    <div class="tab-pane fade show active" id="recent-scores" role="tabpanel">
                        <?php if (empty($recentScores)): ?>
                            <div class="text-center py-5 bg-light rounded-3">
                                <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 text-muted">No Recent Activity</h5>
                                <p class="text-muted">Start playing games to see your activity here.</p>
                                <a href="games.php" class="btn btn-primary px-4">
                                    <i class="bi bi-joystick me-2"></i>Play Now
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="card shadow-sm">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-4">Game</th>
                                                    <th>Score</th>
                                                    <th>Date</th>
                                                    <th class="text-end pe-4">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recentScores as $score): ?>
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0 me-3">
                                                                    <i class="bi bi-joystack fs-4 text-primary"></i>
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-0"><?= htmlspecialchars($score['game_name'] ?? 'Unknown Game') ?></h6>
                                                                    <small class="text-muted"><?= date('M j, Y', strtotime($score['created_at'])) ?></small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                                                <?= number_format($score['score'] ?? 0) ?> pts
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted"><?= date('M j, Y', strtotime($score['created_at'])) ?></small>
                                                        </td>
                                                        <td class="text-end pe-4">
                                                            <button class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-arrow-repeat me-1"></i>Play Again
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Achievements -->
                    <div class="tab-pane fade" id="achievements" role="tabpanel">
                        <?php if (empty($badges)): ?>
                            <div class="text-center py-5 bg-light rounded-3">
                                <i class="bi bi-award text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 text-muted">No Achievements Yet</h5>
                                <p class="text-muted">Keep playing to unlock achievements and earn badges!</p>
                                <a href="games.php" class="btn btn-primary px-4">
                                    <i class="bi bi-joystick me-2"></i>Play Games
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php foreach ($badges as $badge): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-body text-center p-4">
                                                <div class="position-relative d-inline-block mb-3">
                                                    <div class="position-relative">
                                                        <img src="<?= htmlspecialchars($badge['image'] ?? 'assets/images/badge-default.png') ?>" 
                                                             class="img-fluid" 
                                                             style="width: 120px; height: 120px; object-fit: contain;"
                                                             alt="<?= htmlspecialchars($badge['name'] ?? 'Badge') ?>"
                                                             onerror="this.src='assets/images/badge-default.png'"
                                                        >
                                                        <?php if (isset($badge['is_new']) && $badge['is_new']): ?>
                                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                                New!
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <h5 class="card-title mb-2"><?= htmlspecialchars($badge['name'] ?? 'Unknown Badge') ?></h5>
                                                <?php if (!empty($badge['earned_at'])): ?>
                                                    <p class="text-muted small mb-3">
                                                        <i class="bi bi-calendar-check me-1"></i> Earned on <?= date('M j, Y', strtotime($badge['earned_at'])) ?>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if (!empty($badge['description'])): ?>
                                                    <p class="card-text small text-muted">
                                                        <?= htmlspecialchars($badge['description']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if (isset($badge['progress']) && $badge['progress'] < 100): ?>
                                                    <div class="mt-3">
                                                        <div class="d-flex justify-content-between small text-muted mb-1">
                                                            <span>Progress</span>
                                                            <span><?= $badge['progress'] ?>%</span>
                                                        </div>
                                                        <div class="progress" style="height: 6px;">
                                                            <div class="progress-bar" role="progressbar" style="width: <?= $badge['progress'] ?>%;" 
                                                                 aria-valuenow="<?= $badge['progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="mt-3">
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i> Unlocked
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
    <?php endif; ?>
</div>

<?php 
// Add JavaScript for performance optimization
$jsOptimization = "
<script>
    // Show loading state when switching tabs
    document.querySelectorAll('[data-bs-toggle=\"tab\"]').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            // Hide loading after content is loaded
            setTimeout(function() {
                var overlay = document.getElementById('loadingOverlay');
                if (overlay) overlay.style.display = 'none';
            }, 300);
        });
    });

    // Lazy load images
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('img[loading=\"lazy\"]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    });

    // Optimize scroll performance
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                // Any scroll-related updates can go here
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });
</script>";

// Include footer with optimized JS
include 'footer.php'; 
// Output the JS after footer
echo $jsOptimization;
?>