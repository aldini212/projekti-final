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
        'description' => 'Test your knowledge with our fun trivia game!',
        'image' => 'assets/images/trivia-game.jpg',
        'player_count' => 150,
        'category' => 'Trivia'
    ],
    [
        'id' => 2,
        'name' => 'Memory Game',
        'slug' => 'memory',
        'description' => 'Match pairs of cards in this classic memory game!',
        'image' => 'assets/images/memory-game.jpg',
        'player_count' => 120,
        'category' => 'Puzzle'
    ],
    [
        'id' => 3,
        'name' => 'Land Mine',
        'slug' => 'land-mine',
        'description' => 'Navigate through a minefield in this strategic game!',
        'image' => 'assets/images/minesweeper.jpg',
        'player_count' => 95,
        'category' => 'Strategy'
    ],
    [
        'id' => 4,
        'name' => 'Guess Number',
        'slug' => 'guess-number',
        'description' => 'Can you guess the secret number?',
        'image' => 'assets/images/number-game.jpg',
        'player_count' => 85,
        'category' => 'Puzzle'
    ],
    [
        'id' => 5,
        'name' => 'Reaction Time',
        'slug' => 'reaction-time',
        'description' => 'Test your reflexes in this quick reaction game!',
        'image' => 'assets/images/reaction-time.jpg',
        'player_count' => 110,
        'category' => 'Action'
    ],
    [
        'id' => 6,
        'name' => 'Word Scramble',
        'slug' => 'word-scramble',
        'description' => 'Unscramble the words as fast as you can!',
        'image' => 'assets/images/word-scramble.jpg',
        'player_count' => 75,
        'category' => 'Word'
    ]
];
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
        :root {
            --primary: #6c5ce7;
            --primary-dark: #5a4bc9;
            --secondary: #a29bfe;
            --dark: #1a1a2e;
            --darker: #16213e;
            --light: #f8f9fa;
            --gradient: linear-gradient(135deg, var(--primary), var(--secondary));
        }
        
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--darker);
            color: #fff;
            overflow-x: hidden;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--darker);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 5px;
        }
        
        /* Navbar */
        .navbar {
            background: rgba(22, 33, 62, 0.8) !important;
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .navbar.scrolled {
            padding: 0.5rem 0;
            background: rgba(22, 33, 62, 0.95) !important;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav-link {
            color: #fff !important;
            font-weight: 500;
            margin: 0 0.5rem;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            background: var(--gradient);
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .btn-gradient {
            background: var(--gradient);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            position: relative;
            overflow: hidden;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            opacity: 0;
            z-index: -1;
            transition: opacity 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
        }
        
        .btn-gradient:hover::before {
            opacity: 1;
        }
        
        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 100px 0;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(108, 92, 231, 0.15) 0%, transparent 30%),
                        radial-gradient(circle at 80% 70%, rgba(162, 155, 254, 0.15) 0%, transparent 30%);
            z-index: -1;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero h1 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            background: linear-gradient(to right, #fff, #a29bfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .hero p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
            max-width: 600px;
        }
        
        .hero-btns .btn {
            margin-right: 1rem;
            margin-bottom: 1rem;
        }
        
        .hero-image {
            position: relative;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        .hero-image img {
            max-width: 100%;
            filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.3));
        }
        
        /* Games Section */
        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 3rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 4px;
            background: var(--gradient);
            border-radius: 2px;
        }
        
        .game-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }
        
        .game-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            border-color: rgba(108, 92, 231, 0.3);
        }
        
        .game-card .card-img-top {
            height: 200px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .game-card:hover .card-img-top {
            transform: scale(1.05);
        }
        
        .game-card .card-body {
            padding: 1.5rem;
        }
        
        .game-card .card-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .game-card .card-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        
        .game-card .badge {
            background: var(--gradient);
            padding: 0.4rem 0.8rem;
            font-weight: 500;
            border-radius: 50px;
        }
        
        /* Features Section */
        .features {
            padding: 100px 0;
            background: rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .feature-box {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 15px;
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .feature-box:hover {
            transform: translateY(-10px);
            background: rgba(108, 92, 231, 0.1);
            border-color: rgba(108, 92, 231, 0.3);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(108, 92, 231, 0.1);
            border-radius: 20px;
            font-size: 2rem;
            color: var(--primary);
            transition: all 0.3s ease;
        }
        
        .feature-box:hover .feature-icon {
            background: var(--gradient);
            color: white;
            transform: rotate(5deg) scale(1.1);
        }
        
        .feature-box h4 {
            margin: 1rem 0;
            font-weight: 600;
        }
        
        .feature-box p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
        }
        
        /* Leaderboard Section */
        .leaderboard {
            padding: 100px 0;
            background: url('assets/images/pattern.png') center/cover;
            position: relative;
        }
        
        .leaderboard::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(22, 33, 62, 0.95);
            z-index: 0;
        }
        
        .leaderboard .container {
            position: relative;
            z-index: 1;
        }
        
        .leaderboard-table {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .leaderboard-table .table {
            margin-bottom: 0;
            color: #fff;
        }
        
        .leaderboard-table thead th {
            background: rgba(108, 92, 231, 0.2);
            border-bottom: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }
        
        .leaderboard-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .leaderboard-table tbody tr:last-child {
            border-bottom: none;
        }
        
        .leaderboard-table tbody tr:hover {
            background: rgba(108, 92, 231, 0.1);
        }
        
        .leaderboard-table tbody td {
            padding: 1.2rem 1rem;
            vertical-align: middle;
        }
        
        .leaderboard-table .user-info {
            display: flex;
            align-items: center;
        }
        
        .leaderboard-table .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 1rem;
            object-fit: cover;
        }
        
        .leaderboard-table .user-name {
            font-weight: 500;
            margin-bottom: 0.2rem;
        }
        
        .leaderboard-table .user-house {
            font-size: 0.8rem;
            color: var(--secondary);
        }
        
        .leaderboard-table .rank {
            font-weight: 700;
            color: var(--primary);
        }
        
        /* Footer */
        .footer {
            background: #0f0f23;
            padding: 80px 0 30px;
            position: relative;
        }
        
        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--gradient);
        }
        
        .footer-logo {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: inline-block;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .footer-about p {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 1.5rem;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
        }
        
        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background: var(--gradient);
            transform: translateY(-3px);
        }
        
        .footer-heading {
            color: #fff;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.8rem;
        }
        
        .footer-heading::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background: var(--gradient);
        }
        
        .footer-links li {
            margin-bottom: 0.8rem;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.6);
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .footer-links a:hover {
            color: var(--primary);
            padding-left: 5px;
        }
        
        .footer-newsletter p {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 1.5rem;
        }
        
        .newsletter-form {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .newsletter-form input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50px;
            color: #fff;
        }
        
        .newsletter-form button {
            position: absolute;
            top: 5px;
            right: 5px;
            bottom: 5px;
            background: var(--gradient);
            border: none;
            color: white;
            padding: 0 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .newsletter-form button:hover {
            background: var(--primary-dark);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 2rem;
            margin-top: 3rem;
            text-align: center;
        }
        
        .footer-bottom p {
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 0;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 991.98px) {
            .hero h1 {
                font-size: 3rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .feature-box {
                margin-bottom: 2rem;
            }
        }
        
        @media (max-width: 767.98px) {
            .hero {
                text-align: center;
                padding: 120px 0 80px;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                margin-left: auto;
                margin-right: auto;
            }
            
            .hero-btns {
                justify-content: center;
            }
            
            .hero-image {
                margin-top: 3rem;
            }
            
            .section-title {
                font-size: 1.8rem;
            }
        }
        
        /* Animations */
        [data-aos] {
            transition: all 0.6s cubic-bezier(0.2, 0.6, 0.2, 1);
        }
        
        /* Custom Scrollbar for Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: var(--primary) var(--darker);
        }
        :root {
            --primary: #6c5ce7;
            --primary-dark: #5a4bc9;
            --secondary: #a29bfe;
            --dark: #1a1a2e;
            --darker: #16213e;
            --light: #f8f9fa;
            --gradient: linear-gradient(135deg, var(--primary), var(--secondary));
        }
        
        body {
            background: var(--darker);
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: rgba(22, 33, 62, 0.95) !important;
            backdrop-filter: blur(10px);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary) !important;
        }
        
        .nav-link {
            color: #fff !important;
            font-weight: 500;
            margin: 0 0.5rem;
        }
        
        .btn-primary {
            background: var(--primary);
            border: none;
            padding: 0.5rem 1.5rem;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .game-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            border-color: var(--primary);
        }
        
        .game-card .card-img-top {
            height: 180px;
            object-fit: cover;
        }
        
        .game-card .card-body {
            padding: 1.25rem;
        }
        
        .game-card .card-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .game-card .card-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .badge-primary {
            background: var(--primary);
        }
        
        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 2rem;
            font-weight: 700;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--gradient);
            border-radius: 3px;
        }
        
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('assets/images/hero-bg.jpg') no-repeat center center/cover;
            padding: 120px 0;
            text-align: center;
            color: #fff;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        
        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .stats-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.1);
        }
        
        .stats-card i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .stats-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-card p {
            margin: 0;
            color: rgba(255, 255, 255, 0.7);
        }
        
        /* Pulse Animation */
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(108, 92, 231, 0.7);
            }
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 15px rgba(108, 92, 231, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(108, 92, 231, 0);
            }
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
            transition: all 0.3s ease;
        }
        
        .pulse-animation:hover {
            animation: none;
            transform: scale(1.05);
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
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#games">Games</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="leaderboard.php">Leaderboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="profile.php" class="btn btn-outline-light me-2">
                            <i class="bi bi-person-circle me-1"></i> My Profile
                        </a>
                        <a href="logout.php" class="btn btn-primary">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light me-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                        <a href="register.php" class="btn btn-primary">
                            <i class="bi bi-person-plus me-1"></i> Sign Up
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero py-5 position-relative overflow-hidden" id="home" style="padding-top: 100px !important; background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);">
        <div class="container position-relative">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4 text-white">Welcome to <span class="text-primary">GameHub</span></h1>
                    <p class="lead mb-5 text-white-50">Immerse yourself in our collection of exciting mini-games. Compete, have fun, and reach the top of the leaderboards!</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#games" class="btn btn-primary btn-lg px-4 py-3 fw-bold">
                            <i class="bi bi-joystick me-2"></i> Play Now
                        </a>
                        <a href="#features" class="btn btn-outline-light btn-lg px-4 py-3 fw-bold">
                            <i class="bi bi-trophy me-2"></i> View Leaderboard
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Custom Animated Image -->
            <div class="mt-5 text-center" id="custom-animation">
                <img src="assets/images/game-controller.gif" alt="Game Controller" style="max-width: 150px; height: auto; border-radius: 50%; border: 3px solid rgba(255,255,255,0.1); box-shadow: 0 0 30px rgba(108, 92, 231, 0.5);" class="pulse-animation">
            </div>
            
            <!-- Decorative elements -->
            <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden" style="pointer-events: none; z-index: 0;">
                <div class="position-absolute" style="width: 300px; height: 300px; background: linear-gradient(180deg, rgba(108, 92, 231, 0.1) 0%, rgba(0,0,0,0) 100%); border-radius: 50%; top: -150px; left: -100px;"></div>
                <div class="position-absolute" style="width: 500px; height: 500px; background: linear-gradient(180deg, rgba(162, 155, 254, 0.1) 0%, rgba(0,0,0,0) 100%); border-radius: 50%; bottom: -250px; right: -200px;"></div>
            </div>
        </div>
    </section>

    <!-- Games Section -->
    <section class="py-5" id="games" style="position: relative; z-index: 2; background: #0a0a1a; background: linear-gradient(180deg, #0f0c29 0%, #1a1a2e 100%);">
        <div class="container">
            <h2 class="section-title">Our Games</h2>
            <div class="row g-4">
                <?php if (!empty($games)): ?>
                    <?php foreach ($games as $game): ?>
                        <div class="col-md-4">
                            <div class="game-card">
                                <img src="<?php echo !empty($game['image']) ? htmlspecialchars($game['image']) : 'assets/images/games/default.jpg'; ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($game['name']); ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($game['name']); ?></h5>
                                        <span class="badge bg-primary"><?php echo $game['category'] ?? 'Casual'; ?></span>
                                    </div>
                                    <p class="card-text"><?php echo htmlspecialchars($game['description']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">
                                            <i class="bi bi-people me-1"></i> <?php echo $game['player_count']; ?> players
                                        </span>
                                        <a href="games/<?php echo $game['slug'] ?? 'game'; ?>" class="btn btn-sm btn-primary">Play Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-info">
                            No games available at the moment. Please check back later.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-dark" id="features">
        <div class="container">
            <h2 class="section-title text-center">Why Choose GameHub?</h2>
            <div class="row g-4 mt-4">
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-trophy fs-3"></i>
                        </div>
                        <h4>Compete & Win</h4>
                        <p class="text-white">Climb the leaderboards and compete with players from around the world to win amazing prizes.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-people fs-3"></i>
                        </div>
                        <h4>Play with Friends</h4>
                        <p class="text-white">Challenge your friends or team up with them in multiplayer games.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-controller fs-3"></i>
                        </div>
                        <h4>Variety of Games</h4>
                        <p class="text-white">Enjoy a wide selection of games across different genres and difficulty levels.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-dark">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="bi bi-joystick"></i>
                        <h3><?php echo count($games); ?>+</h3>
                        <p>Games Available</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="bi bi-people"></i>
                        <h3>1,000+</h3>
                        <p>Active Players</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="bi bi-trophy"></i>
                        <h3>5,000+</h3>
                        <p>Games Played</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Houses Section -->
<section class="py-5" style="background: linear-gradient(135deg, #0f0c29, #1a1a2e);">
    <div class="container">
        <div class="row g-4">
            <?php
            $userHouse = isset($_SESSION['user_house']) ? $_SESSION['user_house'] : '';
            $houses = [
                'Hipsters' => [
                    'color' => '#9b59b6',
                    'icon' => 'bi-joystick'
                ],
                'Speeders' => [
                    'color' => '#e74c3c',
                    'icon' => 'bi-lightning-charge'
                ],
                'Engineers' => [
                    'color' => '#2ecc71',
                    'icon' => 'bi-gear'
                ],
                'Shadows' => [
                    'color' => '#3498db',
                    'icon' => 'bi-moon'
                ]
            ];

            foreach ($houses as $name => $data):
                $isActive = ($userHouse === $name) ? 'active-house' : '';
            ?>
                <div class="col-md-3 col-6">
                    <div class="house-card <?php echo $isActive; ?>" 
                         style="--house-color: <?php echo $data['color']; ?>"
                         data-house="<?php echo $name; ?>">
                        <i class="bi <?php echo $data['icon']; ?>"></i>
                        <h4><?php echo $name; ?></h4>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.house-card {
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid var(--house-color);
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    color: white;
    transition: all 0.3s ease;
    cursor: pointer;
    height: 100%;
}

.house-card:hover, .house-card.active-house {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.1);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.house-card.active-house {
    animation: pulse 2s infinite;
    box-shadow: 0 0 20px var(--house-color);
}

.house-card i {
    font-size: 2.5rem;
    color: var(--house-color);
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.house-card:hover i, .house-card.active-house i {
    transform: scale(1.2);
}

.house-card h4 {
    margin: 0;
    font-weight: 600;
    color: white;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(var(--house-rgb), 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(var(--house-rgb), 0); }
    100% { box-shadow: 0 0 0 0 rgba(var(--house-rgb), 0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click handler for house selection
    document.querySelectorAll('.house-card').forEach(card => {
        card.addEventListener('click', function() {
            const house = this.dataset.house;
            fetch('update_house.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'house=' + encodeURIComponent(house)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove active class from all cards
                    document.querySelectorAll('.house-card').forEach(c => {
                        c.classList.remove('active-house');
                    });
                    // Add active class to clicked card
                    this.classList.add('active-house');
                }
            });
        });
    });
});
</script>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>GameHub</h5>
                    <p class="text-white">Your ultimate destination for fun and competitive gaming.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-discord"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#home" class="text-white text-decoration-none">Home</a></li>
                        <li><a href="#games" class="text-white text-decoration-none">Games</a></li>
                        <li><a href="leaderboard.php" class="text-white text-decoration-none">Leaderboard</a></li>
                        <li><a href="about.php" class="text-white text-decoration-none">About</a></li>
                        <li><a href="contact.php" class="text-white text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Legal</h5>
                    <ul class="list-unstyled">
                        <li><a href="privacy.php" class="text-white text-decoration-none">Privacy Policy</a></li>
                        <li><a href="terms.php" class="text-white text-decoration-none">Terms of Service</a></li>
                        <li><a href="cookies.php" class="text-white text-decoration-none">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center text-white">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> GameHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="bi bi-arrow-up"></i>
    </a>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.padding = '0.5rem 0';
                navbar.style.background = 'rgba(22, 33, 62, 0.98) !important';
            } else {
                navbar.style.padding = '1rem 0';
                navbar.style.background = 'rgba(22, 33, 62, 0.95) !important';
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
