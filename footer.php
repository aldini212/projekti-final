<footer class="mt-auto py-5 bg-dark text-white-50">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="text-white mb-4">
                    <i class="bi bi-joystick me-2"></i>GameHub
                </h5>
                <p class="mb-4">Your ultimate gaming destination where players compete, connect, and conquer. Join our community today and start your gaming journey!</p>
                <div class="social-links">
                    <a href="#" class="text-white me-3"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-discord"></i></a>
                    <a href="#" class="text-white"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
            
            <div class="col-6 col-md-3 col-lg-2">
                <h6 class="text-white mb-4">Quick Links</h6>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a href="index.php" class="nav-link p-0 text-white-50">Home</a></li>
                    <li class="nav-item mb-2"><a href="games.php" class="nav-link p-0 text-white-50">Games</a></li>
                    <li class="nav-item mb-2"><a href="leaderboard.php" class="nav-link p-0 text-white-50">Leaderboard</a></li>
                    <li class="nav-item mb-2"><a href="profile.php" class="nav-link p-0 text-white-50">My Profile</a></li>
                    <li class="nav-item"><a href="#" class="nav-link p-0 text-white-50">FAQ</a></li>
                </ul>
            </div>
            
            <div class="col-6 col-md-3 col-lg-2">
                <h6 class="text-white mb-4">Legal</h6>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a href="privacy.php" class="nav-link p-0 text-white-50">Privacy Policy</a></li>
                    <li class="nav-item mb-2"><a href="terms.php" class="nav-link p-0 text-white-50">Terms of Service</a></li>
                    <li class="nav-item"><a href="cookies.php" class="nav-link p-0 text-white-50">Cookie Policy</a></li>
                </ul>
            </div>
            
            <div class="col-md-4 col-lg-3 offset-lg-1">
                <h6 class="text-white mb-4">Newsletter</h6>
                <p>Subscribe to our newsletter for the latest updates and game releases.</p>
                <form class="mb-3">
                    <div class="input-group">
                        <input type="email" class="form-control bg-dark text-white border-secondary" placeholder="Your email" aria-label="Your email" aria-describedby="button-newsletter">
                        <button class="btn btn-primary" type="button" id="button-newsletter">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <hr class="my-4 border-secondary">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="mb-3 mb-md-0">
                &copy; <?= date('Y') ?> GameHub. All rights reserved.
            </div>
            <div class="d-flex gap-3">
                <a href="#" class="text-white-50 text-decoration-none">
                    <i class="bi bi-globe me-1"></i> English
                </a>
                <a href="#" class="text-white-50 text-decoration-none">
                    <i class="bi bi-shield-check me-1"></i> Security
                </a>
                <a href="#" class="text-white-50 text-decoration-none">
                    <i class="bi bi-question-circle me-1"></i> Help
                </a>
            </div>
        </div>
    </div>
</footer>

<!-- Back to top button -->
<button type="button" class="btn btn-primary btn-lg rounded-circle shadow back-to-top" id="back-to-top">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
    // Back to top button
    const backToTopButton = document.getElementById('back-to-top');
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopButton.style.display = 'block';
        } else {
            backToTopButton.style.display = 'none';
        }
    });
    
    backToTopButton.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Add active class to current nav link
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = location.pathname.split('/').pop() || 'index.php';
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    });
</script>
</body>
</html>