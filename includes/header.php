<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'GameHub') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --dark-color: #2d3436;
            --light-color: #f8f9fa;
        }
        
        /* Optimized body styles */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #1a365d; /* Fallback solid color */
            color: #fff;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: subpixel-antialiased;
        }
        
        .navbar {
            background: rgba(0, 0, 0, 0.8) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #fff !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .nav-link {
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9) !important;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            margin: 0 0.2rem;
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        
        .nav-link.active {
            background: var(--primary-color);
            color: white !important;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .progress {
            height: 10px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #6c5ce7, #a29bfe);
        }
        
        .table {
            color: #fff;
        }
        
        .table-hover tbody tr:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .nav-tabs .nav-link {
            color: rgba(255, 255, 255, 0.7) !important;
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            background: transparent;
            color: #fff !important;
            border-bottom: 3px solid var(--primary-color);
        }
        
        /* Simplified background for better performance */
        .bg-custom {
            position: relative;
            overflow-x: hidden;
            background: #1a365d; /* Solid color fallback */
        }
        
        .bg-custom::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            z-index: -1;
            /* Remove will-change as it can cause performance issues */
        }
    </style>
</head>
<body class="bg-custom">
    <!-- Simplified overlay -->
    <style>
        .bg-custom::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
            pointer-events: none;
        }
        
        /* Optimize images and prevent layout shifts */
        img {
            max-width: 100%;
            height: auto;
            display: block;
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
    </style>
    <nav class="navbar navbar-expand-lg navbar-dark" style="position: sticky; top: 0; z-index: 1030; background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(5px);">
        <div class="container py-4 position-relative">
        <!-- Simple overlay for better text readability -->
        <div class="position-fixed top-0 left-0 w-100 h-100" style="z-index: -1; background: rgba(0, 0, 0, 0.5);"></div>
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-joystick me-2"></i>GameHub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $activeNav === 'home' ? 'active' : '' ?>" href="index.php">
                            <i class="bi bi-house-door me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeNav === 'games' ? 'active' : '' ?>" href="games.php">
                            <i class="bi bi-joystick me-1"></i>Games
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeNav === 'leaderboard' ? 'active' : '' ?>" href="leaderboard.php">
                            <i class="bi bi-trophy me-1"></i>Leaderboard
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activeNav === 'profile' ? 'active' : '' ?>" href="profile.php">
                                <i class="bi bi-person-circle me-1"></i>My Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="bi bi-box-arrow-right me-1"></i>Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-primary ms-2" href="register.php">
                                <i class="bi bi-person-plus me-1"></i>Sign Up
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container py-4">
