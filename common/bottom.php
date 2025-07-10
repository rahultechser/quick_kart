            </main>
        </div>

        <!-- Bottom Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white shadow-t border-t z-40 flex justify-around">
            <a href="index.php" class="flex flex-col items-center justify-center w-1/4 p-3 text-gray-600 hover:text-blue-600">
                <i class="fas fa-home text-xl"></i>
                <span class="text-xs mt-1">Home</span>
            </a>
            <a href="cart.php" class="flex flex-col items-center justify-center w-1/4 p-3 text-gray-600 hover:text-blue-600 relative">
                <i class="fas fa-shopping-cart text-xl"></i>
                 <?php
                    if (isset($_SESSION['user_id'])) {
                        $cart_count_query = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = {$_SESSION['user_id']}");
                        $cart_count = $cart_count_query->fetch_assoc()['total'] ?? 0;
                        if ($cart_count > 0) {
                            echo "<span class='absolute top-1 right-5 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center'>$cart_count</span>";
                        }
                    }
                 ?>
                <span class="text-xs mt-1">Cart</span>
            </a>
            <a href="order.php" class="flex flex-col items-center justify-center w-1/4 p-3 text-gray-600 hover:text-blue-600">
                <i class="fas fa-box text-xl"></i>
                <span class="text-xs mt-1">Orders</span>
            </a>
            <a href="profile.php" class="flex flex-col items-center justify-center w-1/4 p-3 text-gray-600 hover:text-blue-600">
                <i class="fas fa-user text-xl"></i>
                <span class="text-xs mt-1">Profile</span>
            </a>
        </nav>
    </div>

<script>
    // Disable text selection, right click, and zoom
    document.addEventListener('contextmenu', event => event.preventDefault());
    document.addEventListener('selectstart', event => event.preventDefault());
    document.addEventListener('keydown', function (event) {
        if (event.ctrlKey) {
            if (event.key === '+' || event.key === '-' || event.key === '0') {
                event.preventDefault();
            }
        }
    });
    window.addEventListener('wheel', function(event) {
        if (event.ctrlKey) {
            event.preventDefault();
        }
    }, { passive: false });

    // Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    const toggleSidebar = () => {
        sidebar.classList.toggle('-translate-x-full');
        sidebarOverlay.classList.toggle('hidden');
    };

    sidebarToggle.addEventListener('click', toggleSidebar);
    sidebarOverlay.addEventListener('click', toggleSidebar);
</script>
</body>
</html>