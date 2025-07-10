<?php
require_once 'common/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if cart is empty, if so, redirect
$cart_check = $conn->query("SELECT id FROM cart WHERE user_id = $user_id LIMIT 1");
if ($cart_check->num_rows === 0) {
    header('Location: cart.php');
    exit();
}

// Handle Order Placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    
    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Fetch cart items and calculate total
        $cart_stmt = $conn->prepare("SELECT c.quantity, p.id as product_id, p.price, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $cart_stmt->bind_param("i", $user_id);
        $cart_stmt->execute();
        $cart_items = $cart_stmt->get_result();
        
        $total_amount = 0;
        $order_items_data = [];
        
        while ($item = $cart_items->fetch_assoc()) {
            if ($item['quantity'] > $item['stock']) {
                throw new Exception("Product '{$item['name']}' is out of stock.");
            }
            $total_amount += $item['quantity'] * $item['price'];
            $order_items_data[] = $item;
        }
        $cart_stmt->close();

        // 2. Create order
        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, phone, status) VALUES (?, ?, ?, ?, 'Placed')");
        $order_stmt->bind_param("idss", $user_id, $total_amount, $address, $phone);
        $order_stmt->execute();
        $order_id = $order_stmt->insert_id;
        $order_stmt->close();

        // 3. Insert order items and update product stock
        $order_item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $update_stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

        foreach ($order_items_data as $item) {
            $order_item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $order_item_stmt->execute();
            
            $update_stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $update_stock_stmt->execute();
        }
        $order_item_stmt->close();
        $update_stock_stmt->close();

        // 4. Clear user's cart
        $clear_cart_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_cart_stmt->bind_param("i", $user_id);
        $clear_cart_stmt->execute();
        $clear_cart_stmt->close();

        // 5. Commit transaction
        $conn->commit();
        
        // Redirect to a success page or the order details page
        header('Location: order.php?success=1&order_id=' . $order_id);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        // You can set a session error message to display
        $_SESSION['checkout_error'] = "Order failed: " . $e->getMessage();
        header('Location: checkout.php');
        exit();
    }
}

require_once 'common/header.php';

// Fetch user data for pre-filling the form
$user_stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();
?>

<h1 class="text-2xl font-bold mb-6">Checkout</h1>

<?php
if (isset($_SESSION['checkout_error'])) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">' . $_SESSION['checkout_error'] . '</div>';
    unset($_SESSION['checkout_error']);
}
?>

<form action="checkout.php" method="POST">
    <!-- Shipping Information -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <h2 class="text-lg font-semibold border-b pb-2 mb-4">Shipping Information</h2>
        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Shipping Address</label>
                <textarea id="address" name="address" rows="3" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
    </div>
    
    <!-- Payment Method -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <h2 class="text-lg font-semibold border-b pb-2 mb-4">Payment Method</h2>
        <div class="flex items-center p-3 border rounded-lg bg-gray-50">
            <input type="radio" id="cod" name="payment_method" value="cod" checked class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
            <label for="cod" class="ml-3 block text-sm font-medium text-gray-800">
                Cash on Delivery (COD)
            </label>
        </div>
         <p class="text-xs text-gray-500 mt-2">Pay with cash upon delivery.</p>
    </div>

    <!-- Place Order Button -->
    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-300">
        Place Order
    </button>
</form>

<?php require_once 'common/bottom.php'; ?>