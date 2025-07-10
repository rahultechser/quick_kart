        </main>
    </div>
</div>

<script>
    // Disable unwanted user interactions
    document.addEventListener('contextmenu', event => event.preventDefault());
    document.addEventListener('selectstart', event => event.preventDefault());
    document.addEventListener('keydown', function (event) {
        if (event.ctrlKey && (event.key === '+' || event.key === '-' || event.key === '0')) {
            event.preventDefault();
        }
    });
    window.addEventListener('wheel', function(event) {
        if (event.ctrlKey) event.preventDefault();
    }, { passive: false });
</script>
</body>
</html>