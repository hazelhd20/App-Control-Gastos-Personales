    <!-- Scripts -->
    <script src="<?php echo BASE_URL; ?>public/js/main.js"></script>
    
    <script>
        // Flash message auto-hide
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-auto-hide');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>

