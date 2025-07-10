<?php
// All PHP logic at the top of the file remains the same.
require_once 'common/config.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }
// ... (rest of the PHP logic)
require_once 'common/header.php';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// ... (rest of the PHP logic to fetch product and related products)
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id); $stmt->execute(); $result = $stmt->get_result(); $product = $result->fetch_assoc(); $stmt->close();

if (!$product) { /* ... error handling ... */ }
?>

<!-- HTML Part -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <!-- ... (Image container) ... -->
    <div class="w-full h-64 bg-gray-100 flex items-center justify-center">
        <img src="admin/uploads/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-contain max-h-full">
    </div>
    
    <div class="p-4">
        <h1 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
        <p class="text-2xl font-extrabold text-blue-600 mb-4">₹<?php echo number_format($product['price'], 2); ?></p>
        <div class="flex items-center text-sm text-gray-500 mb-4">
            <span class="mr-2">Category:</span>
            <a href="product.php?category_id=<?php echo $product['category_id']; ?>" class="bg-gray-200 text-gray-800 px-2 py-1 rounded-full hover:bg-gray-300"><?php echo htmlspecialchars($product['category_name']); ?></a>
        </div>
        
        <p class="text-gray-600 mb-4">
            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
        </p>
        
        <div class="text-sm font-semibold mb-6">
            Status: 
            <?php if($product['stock'] > 0): ?>
                <span class="text-green-600">In Stock (<?php echo $product['stock']; ?> available)</span>
            <?php else: ?>
                <span class="text-red-600">Out of Stock</span>
            <?php endif; ?>
        </div>

        <!-- 
            THE FIX IS HERE:
            The if condition around the form is removed.
            Instead, we will disable the button if stock is 0.
        -->
        <div class="flex items-center gap-4">
            <!-- Quantity Selector -->
            <div class="flex items-center border rounded-md">
                <button id="qty-minus" class="px-3 py-2 text-lg font-bold" <?php if($product['stock'] <= 0) echo 'disabled'; ?>>-</button>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock'] > 0 ? $product['stock'] : 1; ?>" class="w-12 text-center border-l border-r focus:outline-none" <?php if($product['stock'] <= 0) echo 'readonly'; ?>>
                <button id="qty-plus" class="px-3 py-2 text-lg font-bold" <?php if($product['stock'] <= 0) echo 'disabled'; ?>>+</button>
            </div>
            <!-- Add to Cart Button -->
            <button id="add-to-cart-btn" 
                    class="flex-1 text-white font-bold py-3 px-4 rounded-lg transition duration-300 flex items-center justify-center 
                           <?php echo ($product['stock'] > 0) ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'; ?>" 
                    <?php if($product['stock'] <= 0) echo 'disabled'; ?>>
                
                <i class="fas fa-cart-plus mr-2"></i>
                <span class="btn-text">
                    <?php echo ($product['stock'] > 0) ? 'Add to Cart' : 'Out of Stock'; ?>
                </span>
                <i class="fas fa-spinner fa-spin ml-2 hidden"></i>
            </button>
        </div>
    </div>
</div>

<!-- ... (Rest of the page and JavaScript) ... -->

<?php require_once 'common/bottom.php'; ?>