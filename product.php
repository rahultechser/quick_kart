<?php
require_once 'common/header.php';
require_once 'common/sidebar.php';

// Get category
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'new';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$category_name = "All Products";
$where_clauses = [];
$params = [];

if ($category_id > 0) {
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    if ($category) {
        $category_name = $category['name'];
        $where_clauses[] = "category_id = ?";
        $params[] = $category_id;
    }
}

if (!empty($search)) {
    $where_clauses[] = "name LIKE ?";
    $params[] = "%$search%";
    $category_name = "Search results for: \"$search\"";
}

$sql = "SELECT id, name, price, image FROM products";
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// Sorting logic
$order_by = " ORDER BY created_at DESC"; // Default: Newest
if ($sort === 'price_asc') {
    $order_by = " ORDER BY price ASC";
} elseif ($sort === 'price_desc') {
    $order_by = " ORDER BY price DESC";
}
$sql .= $order_by;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

?>
<div class="p-4">
    <h1 class="text-xl font-bold mb-4"><?php echo htmlspecialchars($category_name); ?></h1>

    <!-- Filters -->
    <div class="flex justify-between items-center mb-4 bg-gray-100 p-2 rounded-lg">
        <span class="text-sm font-medium">Sort by:</span>
        <div>
            <select id="sort-filter" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
                <option value="new" <?php echo ($sort === 'new') ? 'selected' : ''; ?>>Newest</option>
                <option value="price_asc" <?php echo ($sort === 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_desc" <?php echo ($sort === 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>
        </div>
    </div>
    
    <!-- Product Grid -->
    <div class="grid grid-cols-2 gap-4">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                    <div class="h-32 bg-gray-200 rounded-t-lg flex items-center justify-center">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="h-full w-full object-cover rounded-t-lg">
                    </div>
                    <div class="p-3">
                        <h3 class="text-sm font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-md font-bold text-teal-600 mt-1">$<?php echo number_format($product['price'], 2); ?></p>
                         <button class="w-full mt-2 text-xs bg-gray-800 text-white py-2 rounded-lg hover:bg-gray-900 transition-colors">
                            View Details
                        </button>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="col-span-2 text-center text-gray-500 mt-8">No products found in this category.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('sort-filter').addEventListener('change', function() {
    const selectedSort = this.value;
    const url = new URL(window.location);
    url.searchParams.set('sort', selectedSort);
    window.location.href = url.toString();
});
</script>

<?php require_once 'common/bottom.php'; ?>