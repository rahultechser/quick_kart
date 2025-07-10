<?php 
require_once 'common/header.php';

// Fetch stats
$total_users = $conn->query("SELECT COUNT(id) as count FROM users")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(id) as count FROM orders")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as sum FROM orders WHERE status = 'Delivered'")->fetch_assoc()['sum'] ?? 0;
$active_products = $conn->query("SELECT COUNT(id) as count FROM products WHERE stock > 0")->fetch_assoc()['count'];
$pending_shipments = $conn->query("SELECT COUNT(id) as count FROM orders WHERE status = 'Placed'")->fetch_assoc()['count'];
$cancelled_orders = $conn->query("SELECT COUNT(id) as count FROM orders WHERE status = 'Cancelled'")->fetch_assoc()['count'];
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Stat Card -->
    <div class="bg-white p-6 rounded-lg shadow-lg flex items-center space-x-4">
        <div class="bg-blue-100 p-3 rounded-full">
            <i class="fas fa-users text-2xl text-blue-600"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Users</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_users; ?></p>
        </div>
    </div>
    <!-- Stat Card -->
    <div class="bg-white p-6 rounded-lg shadow-lg flex items-center space-x-4">
        <div class="bg-green-100 p-3 rounded-full">
            <i class="fas fa-dollar-sign text-2xl text-green-600"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-800">$<?php echo number_format($total_revenue, 2); ?></p>
        </div>
    </div>
    <!-- Stat Card -->
    <div class="bg-white p-6 rounded-lg shadow-lg flex items-center space-x-4">
        <div class="bg-yellow-100 p-3 rounded-full">
            <i class="fas fa-shopping-bag text-2xl text-yellow-600"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Orders</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_orders; ?></p>
        </div>
    </div>
     <!-- Stat Card -->
    <div class="bg-white p-6 rounded-lg shadow-lg flex items-center space-x-4">
        <div class="bg-indigo-100 p-3 rounded-full">
            <i class="fas fa-box-open text-2xl text-indigo-600"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Active Products</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $active_products; ?></p>
        </div>
    </div>
     <!-- Stat Card -->
    <div class="bg-white p-6 rounded-lg shadow-lg flex items-center space-x-4">
        <div class="bg-purple-100 p-3 rounded-full">
            <i class="fas fa-truck-loading text-2xl text-purple-600"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pending Shipments</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $pending_shipments; ?></p>
        </div>
    </div>
     <!-- Stat Card -->
    <div class="bg-white p-6 rounded-lg shadow-lg flex items-center space-x-4">
        <div class="bg-red-100 p-3 rounded-full">
            <i class="fas fa-times-circle text-2xl text-red-600"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Cancellations</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $cancelled_orders; ?></p>
        </div>
    </div>
</div>

<div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
    <h2 class="text-xl font-bold mb-4">Quick Actions</h2>
    <div class="flex space-x-4">
        <a href="product.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-plus-circle mr-2"></i>Add Product
        </a>
        <a href="order.php" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
            <i class="fas fa-list-alt mr-2"></i>Manage Orders
        </a>
    </div>
</div>

<?php require_once 'common/bottom.php'; ?>