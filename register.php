<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$errors = [];
$success = false;

// Check if user is already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start output buffering to prevent any accidental output
    ob_start();
    
    // Sanitize and validate input
    $username = trim($_POST['username'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);
    
    // Debug log
    error_log("Registration attempt - Username: $username, Email: $email");
    
    // Validate username
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors['username'] = 'Username must be between 3 and 20 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username can only contain letters, numbers, and underscores';
    } else {
        // Check if username is taken
        $existing = fetch("SELECT id FROM users WHERE username = ?", [$username]);
        if ($existing) {
            $errors['username'] = 'Username is already taken';
        }
    }
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    } else {
        // Check if email is already registered
        $existing = fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            $errors['email'] = 'Email is already registered';
        }
    }
    
    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter and one number';
    }
    
    // Validate password confirmation
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // Validate house selection
    $house = trim($_POST['house'] ?? '');
    $valid_houses = ['Hipsters', 'Speeders', 'Engineers', 'Shadows'];
    if (empty($house) || !in_array($house, $valid_houses)) {
        $errors['house'] = 'Please select a valid house';
    }
    
    // Validate terms acceptance
    if (!$terms) {
        $errors['terms'] = 'You must accept the terms and conditions';
    }
    
    // If no errors, create user
    if (empty($errors)) {
        try {
            // Generate secure password hash
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            if ($hashed_password === false) {
                throw new Exception('Failed to hash password');
            }
            
            $verification_token = bin2hex(random_bytes(32));
            $avatar = 'default.png'; // Default avatar
            
            // Debug log
            error_log("Creating user: $username ($email)");
            
            // Start transaction
            query("START TRANSACTION");
            
            // Insert new user with house
            query(
                "INSERT INTO users (username, email, password, verification_token, house) VALUES (?, ?, ?, ?, ?)",
                [$username, $email, $hashed_password, $verification_token, $house]
            );
            
            $userId = lastInsertId();
            
            // Create user profile
            query(
                "INSERT INTO user_profiles (user_id, created_at) VALUES (?, NOW())",
                [$userId]
            );
            
            // Add welcome badge (using ID 1 which is the welcome badge)
            try {
                query(
                    "INSERT INTO user_badges (user_id, badge_id, earned_at) VALUES (?, 1, NOW())",
                    [$userId]
                );
            } catch (Exception $e) {
                // Log the error but don't fail registration if badge assignment fails
                error_log("Failed to assign welcome badge: " . $e->getMessage());
            }
            
            // Add initial points
            query("UPDATE users SET points = points + 100 WHERE id = ?", [$userId]);
            
            // Log the registration
            logActivity($userId, 'registration');
            
            // Commit transaction
            query("COMMIT");
            
            // Log successful registration
            error_log("User registered successfully: $username (ID: $userId)");
            
            // Clear any output that might have been generated
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Set success message
            $_SESSION['success_message'] = 'Registration successful! You can now log in.';
            
            // Debug log before redirect
            error_log("Redirecting to login page");
            
            // Redirect to login page
            header('Location: login.php');
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            // Log the full error with stack trace
            error_log('Registration error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            // Add a user-friendly error message
            $errors[] = 'Registration failed. Please try again. If the problem persists, contact support.';
            
            // For debugging, you can uncomment the next line to see the actual error
            // $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<?php 
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - GameHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/images/signup-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.95);
        }
        .card-body {
            padding: 2.5rem;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #6c5ce7;
            box-shadow: 0 0 0 0.25rem rgba(108, 92, 231, 0.25);
        }
        .btn-primary {
            background: linear-gradient(45deg, #6c5ce7, #a29bfe);
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
        }
        .divider {
            position: relative;
            text-align: center;
            margin: 2rem 0;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #e0e0e0;
            z-index: 1;
        }
        .divider span {
            position: relative;
            display: inline-block;
            padding: 0 1rem;
            background-color: white;
            z-index: 2;
            color: #6c757d;
            font-size: 0.875rem;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        .input-group .form-control:not(:first-child) {
            border-left: none;
            padding-left: 0;
        }
        .toggle-password {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .form-text {
            font-size: 0.75rem;
            margin-top: 0.25rem;
            color: #6c757d;
        }
        .form-check-input:checked {
            background-color: #6c5ce7;
            border-color: #6c5ce7;
        }
        .form-check-label a {
            color: #6c5ce7;
            text-decoration: none;
            font-weight: 500;
        }
        .form-check-label a:hover {
            text-decoration: underline;
        }
        .social-btn {
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        .social-btn:hover {
            transform: translateY(-2px);
        }
        .social-btn i {
            margin-right: 0.5rem;
        }
        
        /* Dark theme styles */
        body {
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Make all text white */
        h1, h2, h3, h4, h5, h6, p, label, .form-label, .form-text, .text-muted {
            color: #ffffff !important;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            background-color: #1a1a2e;
            border: none;
        }
        
        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: #6c5ce7;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(108, 92, 231, 0.25);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .btn-primary {
            background-color: #6c5ce7;
            border: none;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #5a4bc9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 92, 231, 0.3);
        }
        
        .text-muted {
            color: rgba(255, 255, 255, 0.6) !important;
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .divider span {
            padding: 0 1rem;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
        }
        
        /* Make form controls dark theme compatible */
        .input-group-text {
            background-color: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
        }
        
        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        .form-check-input:checked {
            background-color: #6c5ce7;
            border-color: #6c5ce7;
        }
        
        .form-check-label {
            color: rgba(255, 255, 255, 0.8);
        }
        
        /* Links */
        a {
            color: #8c7ae6;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        a:hover {
            color: #6c5ce7;
            text-decoration: underline;
        }
        
        /* House Selection Styles */
        .house-option {
            height: 100%;
            padding: 1.25rem 0.5rem;
            border-radius: 12px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid rgba(255, 255, 255, 0.1) !important;
            background-color: #1a1a2e !important;
            color: #e6e6e6 !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .btn-check:checked + .house-option {
            border-color: #6c5ce7 !important;
            background: linear-gradient(145deg, #1a1a2e, #16213e) !important;
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3), 0 0 0 2px rgba(108, 92, 231, 0.4) !important;
        }
        
        /* Add a subtle glow effect on hover */
        .house-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 8px;
            padding: 2px;
            background: linear-gradient(145deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
            opacity: 0.8;
        }
        
        .btn-check:checked + .btn-outline-primary {
            border-color: #6c5ce7 !important;
            color: #6c5ce7 !important;
        }
        
        .btn-check:checked + .btn-outline-danger {
            border-color: #dc3545 !important;
            color: #dc3545 !important;
        }
        
        .btn-check:checked + .btn-outline-warning {
            border-color: #ffc107 !important;
            color: #ffc107 !important;
        }
        
        .btn-check:checked + .btn-outline-info {
            border-color: #0dcaf0 !important;
            color: #0dcaf0 !important;
        }
        
        .house-option:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }
        
        .house-option img {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
            margin-bottom: 0.75rem;
        }
        
        .btn-check:checked + .house-option img {
            transform: scale(1.15) rotate(5deg);
            filter: drop-shadow(0 4px 8px rgba(108, 92, 231, 0.4));
        }
        
        /* House name styling */
        .house-option span {
            font-weight: 600;
            letter-spacing: 0.5px;
            font-size: 0.95rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-check:checked + .house-option span {
            color: #fff !important;
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Overlay for better readability -->
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: -1;"></div>
    
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h2 class="mb-2 fw-bold" style="color: #6c5ce7;">Join GameHub</h2>
                            <p class="text-muted">Create your account to start playing</p>
                        </div>
                        
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $errors['general']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="text-center py-4">
                                <div class="mb-4">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                                </div>
                                <h4 class="mb-3">Registration Successful!</h4>
                                <p>Your account has been created successfully. Redirecting you to the dashboard...</p>
                                <div class="spinner-border text-primary mt-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = 'index.php';
                                }, 2000);
                            </script>
                        <?php else: ?>
                            <form method="POST" action="register.php" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                                               id="username" name="username" 
                                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                               placeholder="Choose a username"
                                               required>
                                    </div>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['username'] ?? 'Please choose a username' ?>
                                    </div>
                                </div>
                                 
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                               id="email" name="email" 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                               placeholder="your@email.com"
                                               required>
                                    </div>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['email'] ?? 'Please enter a valid email' ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                               id="password" name="password" 
                                               placeholder="••••••••"
                                               required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">At least 8 characters with 1 uppercase & number</div>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['password'] ?? 'Please enter a valid password' ?>
                                    </div>
                                </div>
                                 
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-shield-lock-fill"></i></span>
                                        <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                               id="confirm_password" name="confirm_password" 
                                               placeholder="••••••••"
                                               required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['confirm_password'] ?? 'Passwords do not match' ?>
                                    </div>
                                </div>

                                <!-- House Selection -->
                                <div class="mb-4">
                                    <label class="form-label d-block">Choose Your House <span class="text-danger">*</span></label>
                                    <div class="row g-3">
                                        <div class="col-6 col-md-3">
                                            <input type="radio" class="btn-check" name="house" id="hipsters" value="Hipsters" required 
                                                <?php echo ($_POST['house'] ?? '') === 'Hipsters' ? 'checked' : ''; ?>>
                                            <label class="w-100 btn d-flex flex-column align-items-center house-option" for="hipsters" style="border: none;">
                                                <img src="assets/images/houses/hipsters.png" alt="Hipsters" class="img-fluid" style="width: 60px; height: 60px; object-fit: contain;" onerror="this.src='assets/images/houses/default.png';">
                                                <span class="mt-1">Hipsters</span>
                                            </label>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <input type="radio" class="btn-check" name="house" id="speeders" value="Speeders" 
                                                <?php echo ($_POST['house'] ?? '') === 'Speeders' ? 'checked' : ''; ?>>
                                            <label class="w-100 btn d-flex flex-column align-items-center house-option" for="speeders" style="border: none;">
                                                <img src="assets/images/houses/speeders.png" alt="Speeders" class="img-fluid" style="width: 60px; height: 60px; object-fit: contain;" onerror="this.src='assets/images/houses/default.png';">
                                                <span class="mt-1">Speeders</span>
                                            </label>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <input type="radio" class="btn-check" name="house" id="engineers" value="Engineers" 
                                                <?php echo ($_POST['house'] ?? '') === 'Engineers' ? 'checked' : ''; ?>>
                                            <label class="w-100 btn d-flex flex-column align-items-center house-option" for="engineers" style="border: none;">
                                                <img src="assets/images/houses/engineers.png" alt="Engineers" class="img-fluid" style="width: 60px; height: 60px; object-fit: contain;" onerror="this.src='assets/images/houses/default.png';">
                                                <span class="mt-1">Engineers</span>
                                            </label>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <input type="radio" class="btn-check" name="house" id="shadows" value="Shadows" 
                                                <?php echo ($_POST['house'] ?? '') === 'Shadows' ? 'checked' : ''; ?>>
                                            <label class="w-100 btn d-flex flex-column align-items-center house-option" for="shadows" style="border: none;">
                                                <img src="assets/images/houses/shadows.png" alt="Shadows" class="img-fluid" style="width: 60px; height: 60px; object-fit: contain;" onerror="this.src='assets/images/houses/default.png';">
                                                <span class="mt-1">Shadows</span>
                                            </label>
                                        </div>
                                    </div>
                                    <?php if (isset($errors['house'])): ?>
                                        <div class="invalid-feedback d-block">
                                            <?php echo $errors['house']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-check mb-4">
                                    <input class="form-check-input <?php echo isset($errors['terms']) ? 'is-invalid' : ''; ?>" 
                                           type="checkbox" id="terms" name="terms" required 
                                           <?php echo !empty($_POST['terms']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label small" for="terms">
                                        I agree to the <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Service</a> and 
                                        <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                                    </label>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['terms'] ?? 'You must accept the terms and conditions' ?>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 mb-3 fw-bold">
                                    <i class="bi bi-person-plus-fill me-2"></i>Create Account
                                </button>

                                <p class="text-center text-muted mb-0">
                                    Already have an account? <a href="login.php" class="text-decoration-none fw-semibold" style="color: #6c5ce7;">Sign In</a>
                                </p>
                            </form>
                             
                            <div class="divider my-4">
                                <span>OR</span>
                            </div>
                             
                            <div class="text-center">
                                <p class="mb-3 text-muted small">Sign up with</p>
                                <div class="d-flex gap-3 justify-content-center">
                                    <a href="#" class="btn btn-outline-dark social-btn">
                                        <i class="bi bi-google"></i> Google
                                    </a>
                                    <a href="#" class="btn btn-outline-primary social-btn">
                                        <i class="bi bi-facebook"></i> Facebook
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye-fill');
                icon.classList.add('bi-eye-slash-fill');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash-fill');
                icon.classList.add('bi-eye-fill');
            }
        });
    });
    
    // Form validation
    (function () {
        'use strict'
        
        const forms = document.querySelectorAll('.needs-validation')
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                
                form.classList.add('was-validated')
            }, false)
        })
    })()
    
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const passwordStrength = document.createElement('div');
    passwordStrength.className = 'password-strength mt-1';
    passwordInput.parentNode.insertBefore(passwordStrength, passwordInput.nextSibling.nextSibling);
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        // Length check
        if (password.length >= 8) strength++;
        
        // Contains number
        if (/\d/.test(password)) strength++;
        
        // Contains uppercase
        if (/[A-Z]/.test(password)) strength++;
        
        // Contains special character
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        // Update strength indicator
        let strengthText = '';
        let strengthClass = '';
        
        switch(strength) {
            case 0:
            case 1:
                strengthText = 'Weak';
                strengthClass = 'text-danger';
                break;
            case 2:
                strengthText = 'Moderate';
                strengthClass = 'text-warning';
                break;
            case 3:
                strengthText = 'Strong';
                strengthClass = 'text-info';
                break;
            case 4:
                strengthText = 'Very Strong';
                strengthClass = 'text-success';
                break;
        }
        
        passwordStrength.innerHTML = `
            <div class="progress mb-1" style="height: 5px;">
                <div class="progress-bar ${strengthClass.replace('text-', 'bg-')}" 
                     role="progressbar" 
                     style="width: ${strength * 25}%" 
                     aria-valuenow="${strength * 25}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                </div>
            </div>
            <small class="${strengthClass}">${strengthText}</small>
        `;
    });
    </script>
</body>
</html>
