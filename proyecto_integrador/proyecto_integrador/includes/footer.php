    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('toggleSidebar')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });
        
        window.Notificacion = {
            exito: function(msg) { alert('✓ ' + msg); },
            error: function(msg) { alert('✗ ' + msg); },
            info: function(msg) { alert('ℹ ' + msg); }
        };
    </script>
</body>
</html>