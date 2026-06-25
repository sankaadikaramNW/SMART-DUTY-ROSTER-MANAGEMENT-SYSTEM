    <?php if ($isLoggedIn): ?>
            </div> <!-- Close main-content-container -->
            
            <footer class="footer-custom">
                <span class="small">&copy; <?= date('Y') ?> Sri Lanka Air Force. Smart Duty Roster Management System. All Rights Reserved.</span>
            </footer>
        </div> <!-- Close main-layout -->
    </div> <!-- Close app-container -->
    <?php else: ?>
        </div> <!-- Close content-wrapper container -->
        
        <footer class="footer py-3 mt-auto border-top border-secondary bg-dark text-center text-muted" style="background: rgba(11, 15, 25, 0.9) !important; border-color: var(--glass-border) !important;">
            <div class="container">
                <span class="small">&copy; <?= date('Y') ?> Sri Lanka Air Force. Smart Duty Roster Management System. All Rights Reserved.</span>
            </div>
        </footer>
    <?php endif; ?>

    <!-- Bootstrap 5 Bundle with Popper -->
    <script src="<?= BASE_URL ?>/views/assets/vendor/js/bootstrap.bundle.min.js"></script>
    
    <!-- Micro-interactions & Helpers Javascript -->
    <script>
        // Custom animation triggers or common functions
        document.addEventListener('DOMContentLoaded', () => {
            // Fade-in cards smoothly
            const cards = document.querySelectorAll('.glass-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(15px)';
                card.style.transition = 'opacity 0.4s ease-out, transform 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 80);
            });

            // Mobile sidebar toggle trigger
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar-custom');
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                });
            }
            
            // Close sidebar on tapping outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth < 992 && sidebar && sidebar.classList.contains('active')) {
                    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                        sidebar.classList.remove('active');
                    }
                }
            });

            // Theme toggle (Visual indicator)
            const themeToggle = document.getElementById('themeToggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const icon = themeToggle.querySelector('i');
                    if (icon.classList.contains('fa-moon')) {
                        icon.classList.remove('fa-moon');
                        icon.classList.add('fa-sun');
                        document.body.style.background = 'radial-gradient(circle at 50% 0%, #0f172a 0%, #020617 70%)';
                    } else {
                        icon.classList.remove('fa-sun');
                        icon.classList.add('fa-moon');
                        document.body.style.background = '';
                    }
                });
            }
        });
    </script>
</body>
</html>
