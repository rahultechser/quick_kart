<?php
// PHP logic at the top remains the same
require_once 'common/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'common/header.php';

$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$products_result = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 8");
?>

<!-- Search Bar -->
<div class="mb-6">
    <div class="relative">
        <input type="text" placeholder="Search for products..." class="w-full p-3 pl-10 border rounded-full bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
    </div>
</div>

<!-- Categories Section -->
<div class="mb-6">
    <h2 class="text-xl font-bold mb-3">Categories</h2>
    <div class="flex overflow-x-auto space-x-4 pb-2 no-scrollbar">
        <?php if ($categories_result && $categories_result->num_rows > 0): ?>
            <?php while($category = $categories_result->fetch_assoc()): ?>
            <a href="product.php?category_id=<?php echo $category['id']; ?>" class="flex-shrink-0 text-center">
                <div class="w-16 h-16 bg-white rounded-full shadow-md flex items-center justify-center overflow-hidden mx-auto">
                    <img src="admin/uploads/<?php echo $category['image']; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="w-full h-full object-cover">
                </div>
                <span class="block text-sm mt-2 text-gray-600"><?php echo htmlspecialchars($category['name']); ?></span>
            </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-500">No categories found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Featured Products Section -->
<div>
    <h2 class="text-xl font-bold mb-3">Featured Products</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        <?php if ($products_result && $products_result->num_rows > 0): ?>
            <?php while($product = $products_result->fetch_assoc()): ?>
            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="bg-white rounded-lg shadow-md overflow-hidden block">
                <img src="admin/uploads/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-32 object-cover">
                <div class="p-3">
                    <h3 class="font-semibold text-sm truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                    
                    <!-- THE FIX IS HERE -->
                    <p class="text-lg font-bold text-blue-600">₹<?php echo number_format($product['price'], 2); ?></p>
                    
                    <button class="mt-2 w-full bg-blue-500 text-white text-xs py-1.5 rounded-md hover:bg-blue-600">View Details</button>
                </div>
            </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-500 col-span-full text-center">No featured products found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'common/bottom.php'; ?>