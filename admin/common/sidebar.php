<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="w-64 bg-gray-800 text-white flex flex-col">
    <div class="px-6 py-4 border-b border-gray-700">
        <h2 class="text-2xl font-semibold">Quick Kart</h2>
        <span class="text-sm text-gray-400">Admin Panel</span>
    </div>
    <nav class="flex-1 px-4 py-4 space-y-2">
        <a href="index.php" class="sidebar-link flex items-center px-4 py-2 rounded-md hover:bg-gray-700 <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt w-6"></i>
            <span class="ml-3">Dashboard</span>
        </a>
        <a href="category.php" class="sidebar-link flex items-center px-4 py-2 rounded-md hover:bg-gray-700 <?php echo ($current_page == 'category.php') ? 'active' : ''; ?>">
            <i class="fas fa-tags w-6"></i>
            <span class="ml-3">Categories</span>
        </a>
        <a href="product.php" class="sidebar-link flex items-center px-4 py-2 rounded-md hover:bg-gray-700 <?php echo ($current_page == 'product.php') ? 'active' : ''; ?>">
            <i class="fas fa-box-open w-6"></i>
            <span class="ml-3">Products</span>
        </a>
        <a href="order.php" class="sidebar-link flex items-center px-4 py-2 rounded-md hover:bg-gray-700 <?php echo in_array($current_page, ['order.php', 'order_detail.php']) ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart w-6"></i>
            <span class="ml-3">Orders</span>
        </a>
        <a href="user.php" class="sidebar-link flex items-center px-4 py-2 rounded-md hover:bg-gray-700 <?php echo ($current_page == 'user.php') ? 'active' : ''; ?>">
            <i class="fas fa-users w-6"></i>
            <span class="ml-3">Users</span>
        </a>
        <a href="setting.php" class="sidebar-link flex items-center px-4 py-2 rounded-md hover:bg-gray-700 <?php echo ($current_page == 'setting.php') ? 'active' : ''; ?>">
            <i class="fas fa-cogs w-6"></i>
            <span class="ml-3">Settings</span>
        </a>
    </nav>
</div>