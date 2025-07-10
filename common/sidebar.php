<!-- Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>

<!-- Sidebar Menu -->
<div id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out z-50">
    <div class="p-4 border-b">
        <h2 class="text-2xl font-bold text-blue-600">Menu</h2>
    </div>
    <nav class="mt-4">
        <a href="index.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100">
            <i class="fas fa-home w-6"></i>
            <span class="ml-3">Home</span>
        </a>
        <a href="order.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100">
            <i class="fas fa-box w-6"></i>
            <span class="ml-3">My Orders</span>
        </a>
        <a href="profile.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100">
            <i class="fas fa-user-circle w-6"></i>
            <span class="ml-3">Profile</span>
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="login.php?logout=1" class="flex items-center px-4 py-3 text-red-500 hover:bg-red-50">
                <i class="fas fa-sign-out-alt w-6"></i>
                <span class="ml-3">Logout</span>
            </a>
        <?php else: ?>
            <a href="login.php" class="flex items-center px-4 py-3 text-green-600 hover:bg-green-50">
                <i class="fas fa-sign-in-alt w-6"></i>
                <span class="ml-3">Login / Sign Up</span>
            </a>
        <?php endif; ?>
    </nav>
</div>