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
            // Start transaction
            query("START TRANSACTION");
            
            // Insert user
            query(
                "INSERT INTO users (username, email, password, avatar, verification_token, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [$username, $email, $hashed_password, $avatar, $verification_token]
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
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card">
                    <div class="card-body p-4">
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
                                <span class="px-2">OR</span>
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
