<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameHub - Play Mini Games & Compete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), 
                        url('assets/images/gamehub-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            color: #fff;
        }
        .navbar {
            background-color: rgba(0, 0, 0, 0.7) !important;
            backdrop-filter: blur(10px);
        }
        .card {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            color: #fff;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .game-card .card-img-top {
            height: 180px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .game-card:hover .card-img-top {
            transform: scale(1.03);
        }
        .btn-primary {
            background: linear-gradient(45deg, #6c5ce7, #a29bfe);
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
        }
        .featured-section {
            background: rgba(0, 0, 0, 0.6);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        h1, h2, h3, h4, h5, h6 {
            font-weight: 700;
            color: #fff;
        }
        .text-muted {
            color: #adb5bd !important;
        }
        .navbar-brand {
            font-weight: 800;
            font-size: 1.75rem;
            background: linear-gradient(45deg, #6c5ce7, #a29bfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">GameHub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="lost-world.php">Games</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="leaderboard.php">Leaderboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="challenges.php">Challenges</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> My Profile
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light me-2">Login</a>
                        <a href="register.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container py-5">
        <div class="row">
            <!-- Featured Games -->
            <section class="col-md-8">
                <h2 class="mb-4">Featured Games</h2>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card game-card h-100">
                            <img src="assets/images/memory-game.jpg" class="card-img-top" alt="Memory Game">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Memory Match</h5>
                                <p class="card-text">Test your memory with this fun card matching game!</p>
                                <div class="mt-auto">
                                    <a href="/GamingHub/projekti-final-1/games/memory/" class="btn btn-primary">Play Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card game-card h-100">
                            <img src="assets/images/word-scramble.jpg" class="card-img-top" alt="Word Scramble">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Word Scramble</h5>
                                <p class="card-text">Unscramble the letters to form the correct word!</p>
                                <div class="mt-auto">
                                    <a href="/GamingHub/projekti-final-1/games/word-scramble/" class="btn btn-primary">Play Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card game-card h-100">
                            <img src="assets/images/number-game.jpg" class="card-img-top" alt="Guess the Number">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Guess the Number</h5>
                                <p class="card-text">Can you guess the secret number in the fewest tries?</p>
                                <div class="mt-auto">
                                    <a href="/GamingHub/projekti-final-1/games/guess-number/" class="btn btn-primary">Play Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card game-card h-100">
                            <img src="assets/images/trivia-game.jpg" class="card-img-top" alt="Trivia Quiz">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Trivia Challenge</h5>
                                <p class="card-text">Test your knowledge with our fun trivia questions!</p>
                                <div class="mt-auto">
                                    <a href="/GamingHub/projekti-final-1/games/trivia/" class="btn btn-primary">Play Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card game-card h-100">
                            <img src="assets/images/reaction-time.jpg" class="card-img-top" alt="Reaction Time Challenge">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Reaction Time Challenge</h5>
                                <p class="card-text">Click the blocks as fast as you can through 3 phases of speed and chaos!</p>
                                <div class="mt-auto">
                                    <a href="/GamingHub/projekti-final-1/games/reaction-time/" class="btn btn-primary">Play Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sidebar -->
            <aside class="col-md-4">
                <!-- User Stats -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <img src="assets/images/avatars/default.png" alt="User Avatar" class="rounded-circle mb-3" width="100">
                            <h5>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Player'); ?></h5>
                            <div class="d-flex justify-content-around mt-3">
                                <div class="text-center">
                                    <div class="h4 mb-0">1,250</div>
                                    <small>Points</small>
                                </div>
                                <div class="text-center">
                                    <div class="h4 mb-0">15</div>
                                    <small>Games Played</small>
                                </div>
                                <div class="text-center">
                                    <div class="h4 mb-0">3</div>
                                    <small>Badges</small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Leaderboard -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Top Players</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary me-2"><?php echo $i; ?></span>
                                    <span>Player <?php echo $i; ?></span>
                                </div>
                                <span class="badge bg-success"><?php echo rand(1000, 5000); ?> pts</span>
                            </div>
                        <?php endfor; ?>
                        <a href="leaderboard.php" class="list-group-item list-group-item-action text-center">
                            View Full Leaderboard <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <!-- Footer -->
    <footer class="text-white py-4 mt-5" style="background-color: rgba(0, 0, 0, 0.7);">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About GameHub</h5>
                    <p>Your ultimate destination for fun and competitive mini-games. Play, compete, and climb the leaderboards!</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Games</a></li>
                        <li><a href="#" class="text-white">Leaderboard</a></li>
                        <li><a href="#" class="text-white">Challenges</a></li>
                        <li><a href="#" class="text-white">Profile</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Connect With Us</h5>
                    <div class="social-links">
                        <a href="#" class="text-white me-2"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-2"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-discord"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> GameHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
