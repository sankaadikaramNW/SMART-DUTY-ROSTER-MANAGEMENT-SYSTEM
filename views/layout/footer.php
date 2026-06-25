    </div> <!-- Close content-wrapper container -->

    <footer class="footer py-3 mt-auto border-top border-secondary bg-dark text-center text-muted" style="background: rgba(11, 15, 25, 0.9) !important; border-color: var(--glass-border) !important;">
        <div class="container">
            <span class="small">&copy; <?= date('Y') ?> Sri Lanka Air Force. Smart Duty Roster Management System. All Rights Reserved.</span>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
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
        });
    </script>
</body>
</html>
