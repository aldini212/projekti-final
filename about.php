<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Static array of games with their details and images
$games = [
    [
        'id' => 1,
        'name' => 'Trivia Game',
        'slug' => 'trivia',
        'description' => 'Test your knowledge with our fun trivia game! Answer questions across various categories and difficulty levels.',
        'features' => ['Multiple categories', 'Different difficulty levels', 'Score tracking', 'Responsive design'],
        'image' => 'assets/images/trivia-game.jpg',
        'player_count' => 150,
        'category' => 'Trivia'
    ],
    [
        'id' => 2,
        'name' => 'Memory Game',
        'slug' => 'memory',
        'description' => 'Match pairs of cards in this classic memory game with beautiful animations and smooth gameplay.',
        'features' => ['Multiple card sets', 'Score tracking', 'Timer', 'Responsive design'],
        'image' => 'assets/images/memory-game.jpg',
        'player_count' => 120,
        'category' => 'Puzzle'
    ],
    [
        'id' => 3,
        'name' => 'Word Scramble',
        'slug' => 'word-scramble',
        'description' => 'Unscramble letters to form words in this challenging word game with increasing difficulty.',
        'features' => ['Multiple difficulty levels', 'Hints system', 'Score tracking', 'Leaderboard'],
        'image' => 'assets/images/word-scramble.jpg',
        'player_count' => 95,
        'category' => 'Word'
    ],
    [
        'id' => 4,
        'name' => 'Land Mine',
        'slug' => 'land-mine',
        'description' => 'Navigate through a minefield in this strategic game that tests your logic and planning skills.',
        'features' => ['Multiple difficulty levels', 'Score tracking', 'Responsive design'],
        'image' => 'assets/images/minesweeper.jpg',
        'player_count' => 85,
        'category' => 'Strategy'
    ],
    [
        'id' => 5,
        'name' => 'Reaction Time',
        'slug' => 'reaction-time',
        'description' => 'Test your reflexes in this quick reaction game that measures your response time.',
        'features' => ['Reaction time tracking', 'Leaderboard', 'Responsive design'],
        'image' => 'assets/images/reaction-time.jpg',
        'player_count' => 110,
        'category' => 'Arcade'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Me - GameHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #6c5ce7;
            --primary-dark: #5a4bc9;
            --secondary: #a29bfe;
            --dark: #2d3436;
            --light: #f8f9fa;
            --success: #00b894;
            --danger: #ff7675;
            --warning: #fdcb6e;
            --info: #00cec9;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #0f0c29;
            background: linear-gradient(135deg, #0f0c29 0%, #1a1a2e 100%);
            color: var(--light);
            line-height: 1.6;
        }
        
        .navbar {
            background: rgba(0, 0, 0, 0.8) !important;
            backdrop-filter: blur(10px);
            padding: 15px 0;
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--light) !important;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--primary) !important;
        }
        
        .hero {
            padding: 180px 0 100px;
            position: relative;
            overflow: hidden;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
            max-width: 600px;
            margin: 0 auto 30px;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            font-size: 2.5rem;
            font-weight: 700;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            width: 50%;
            height: 4px;
            background: var(--primary);
            bottom: -10px;
            left: 25%;
            border-radius: 2px;
        }
        
        .game-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .game-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        
        .game-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .game-category {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .game-content {
            padding: 25px;
        }
        
        .game-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: white;
        }
        
        .game-description {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 15px;
        }
        
        .game-features {
            margin: 20px 0;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .feature-item i {
            color: var(--primary);
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .game-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-play {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
        }
        
        .btn-play:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
            color: white;
        }
        
        .about-section {
            padding: 100px 0;
            background: rgba(0, 0, 0, 0.2);
            margin: 50px 0;
        }
        
        .about-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        
        .about-content h2 {
            font-size: 2.5rem;
            margin-bottom: 30px;
            color: white;
        }
        
        .about-content p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 30px;
            line-height: 1.8;
        }
        
        .skills {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin: 40px 0;
        }
        
        .skill-tag {
            background: rgba(108, 92, 231, 0.2);
            color: var(--primary);
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 500;
            border: 1px solid rgba(108, 92, 231, 0.3);
        }
        
        footer {
            background: rgba(0, 0, 0, 0.3);
            padding: 30px 0;
            margin-top: 50px;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .social-links {
            margin: 20px 0;
        }
        
        .social-link {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 50%;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background: var(--primary);
            transform: translateY(-3px);
            color: white;
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .game-card {
                margin-bottom: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">GameHub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#games">Games</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h1>About Me & My Games</h1>
                    <p>Welcome to my portfolio of mini-games! I'm passionate about creating fun and engaging web-based games that challenge and entertain.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <h2>Hello, I'm a Game Developer</h2>
                <p>I specialize in creating interactive and engaging web-based games using modern web technologies. My goal is to build games that are not only fun to play but also showcase the capabilities of web development.</p>
                <p>Each game is built with a focus on user experience, performance, and responsive design to ensure they work seamlessly across all devices.</p>
                
                <div class="skills">
                    <span class="skill-tag">HTML5</span>
                    <span class="skill-tag">CSS3</span>
                    <span class="skill-tag">JavaScript</span>
                    <span class="skill-tag">PHP</span>
                    <span class="skill-tag">MySQL</span>
                    <span class="skill-tag">jQuery</span>
                    <span class="skill-tag">Bootstrap</span>
                    <span class="skill-tag">Responsive Design</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Games Section -->
    <section class="py-5" id="games">
        <div class="container">
            <h2 class="section-title">My Games</h2>
            <div class="row">
                <?php foreach ($games as $game): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="game-card">
                            <div class="game-image" style="background-image: url('<?php echo $game['image']; ?>');">
                                <span class="game-category"><?php echo $game['category']; ?></span>
                            </div>
                            <div class="game-content">
                                <h3 class="game-title"><?php echo $game['name']; ?></h3>
                                <p class="game-description"><?php echo $game['description']; ?></p>
                                
                                <div class="game-features">
                                    <?php if (!empty($game['features'])): ?>
                                        <?php foreach ($game['features'] as $feature): ?>
                                            <div class="feature-item">
                                                <i class="fas fa-check-circle"></i>
                                                <span><?php echo $feature; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="game-stats">
                                    <div class="stat">
                                        <span class="stat-value"><?php echo $game['player_count']; ?>+</span>
                                        <span class="stat-label">Players</span>
                                    </div>
                                    <a href="games/<?php echo $game['slug']; ?>" class="btn btn-play">
                                        Play Now <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="social-links">
                <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
            </div>
            <p class="mb-0">&copy; <?php echo date('Y'); ?> GameHub. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.padding = '10px 0';
                navbar.style.background = 'rgba(0, 0, 0, 0.9)';
            } else {
                navbar.style.padding = '15px 0';
                navbar.style.background = 'rgba(0, 0, 0, 0.8)';
            }
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
