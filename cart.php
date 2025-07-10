<?php
require_once 'common/config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// --- AJAX Handler for Cart Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Invalid action.'];

    // Update quantity action
    if ($_POST['action'] === 'update_quantity') {
        $cart_id = (int)$_POST['cart_id'];
        $quantity = (int)$_POST['quantity'];

        if ($cart_id > 0 && $quantity > 0) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
            if ($stmt->execute()) {
                $response = ['status' => 'success'];
            } else {
                $response['message'] = 'Failed to update cart.';
            }
            $stmt->close();
        } else {
             $response['message'] = 'Invalid quantity or item.';
        }
    }

    // Delete item action
    if ($_POST['action'] === 'delete_item') {
        $cart_id = (int)$_POST['cart_id'];
        if ($cart_id > 0) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $cart_id, $user_id);
            if ($stmt->execute()) {
                $response = ['status' => 'success'];
            } else {
                $response['message'] = 'Failed to remove item.';
            }
            $stmt->close();
        } else {
            $response['message'] = 'Invalid item.';
        }
    }
    
    // Fetch updated totals after any action
    if ($response['status'] === 'success') {
        $total_query = $conn->prepare("SELECT SUM(c.quantity * p.price) as total FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $total_query->bind_param("i", $user_id);
        $total_query->execute();
        $total_result = $total_query->get_result()->fetch_assoc();
        $response['total'] = number_format($total_result['total'] ?? 0, 2);
    }

    echo json_encode($response);
    exit();
}

require_once 'common/header.php';

// Fetch Cart Items
$stmt = $conn->prepare("SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.image, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

$total_price = 0;
?>

<h1 class="text-2xl font-bold mb-6">My Cart</h1>

<div id="cart-container">
    <?php if ($cart_items->num_rows > 0): ?>
        <div class="space-y-4">
        <?php while($item = $cart_items->fetch_assoc()): 
            $subtotal = $item['price'] * $item['quantity'];
            $total_price += $subtotal;
        ?>
            <div id="cart-item-<?php echo $item['cart_id']; ?>" class="bg-white rounded-lg shadow-md p-4 flex items-center gap-4">
                <img src="admin/uploads/<?php echo $item['image']; ?>" class="w-20 h-20 rounded-md object-cover">
                <div class="flex-1">
                    <h3 class="font-semibold"><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p class="text-gray-600 text-sm">$<?php echo $item['price']; ?></p>
                    <div class="flex items-center mt-2">
                        <input type="number" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" data-cart-id="<?php echo $item['cart_id']; ?>" data-price="<?php echo $item['price']; ?>" class="quantity-input w-16 text-center border rounded-md py-1">
                        <button class="delete-item-btn text-red-500 hover:text-red-700 ml-4" data-cart-id="<?php echo $item['cart_id']; ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <p class="font-bold text-lg" id="subtotal-<?php echo $item['cart_id']; ?>">$<?php echo number_format($subtotal, 2); ?></p>
            </div>
        <?php endwhile; ?>
        </div>

        <div class="mt-8 bg-white p-4 rounded-lg shadow-t sticky bottom-20">
            <div class="flex justify-between items-center text-xl font-bold">
                <span>Total:</span>
                <span id="grand-total">$<?php echo number_format($total_price, 2); ?></span>
            </div>
            <a href="checkout.php" class="block w-full text-center bg-blue-600 text-white font-bold py-3 rounded-lg mt-4 hover:bg-blue-700">
                Proceed to Checkout
            </a>
        </div>
    <?php else: ?>
        <div class="text-center py-16">
            <i class="fas fa-shopping-cart text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">Your cart is empty.</p>
            <a href="index.php" class="mt-4 inline-block bg-blue-500 text-white px-6 py-2 rounded-full">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cartContainer = document.getElementById('cart-container');
    
    const updateCart = async (action, cartId, quantity = null) => {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('cart_id', cartId);
        if (quantity !== null) {
            formData.append('quantity', quantity);
        }

        try {
            const response = await fetch('cart.php', { method: 'POST', body: formData });
            return await response.json();
        } catch (error) {
            return { status: 'error', message: 'Network error.' };
        }
    };
    
    cartContainer.addEventListener('change', async (e) => {
        if (e.target.classList.contains('quantity-input')) {
            const cartId = e.target.dataset.cartId;
            const price = e.target.dataset.price;
            const quantity = e.target.value;

            const result = await updateCart('update_quantity', cartId, quantity);
            
            if (result.status === 'success') {
                document.getElementById(`subtotal-${cartId}`).textContent = '$' + (price * quantity).toFixed(2);
                document.getElementById('grand-total').textContent = '$' + result.total;
            } else {
                alert(result.message);
                // Optionally revert value
            }
        }
    });

    cartContainer.addEventListener('click', async (e) => {
        const deleteBtn = e.target.closest('.delete-item-btn');
        if (deleteBtn) {
            const cartId = deleteBtn.dataset.cartId;
            if (confirm('Are you sure you want to remove this item?')) {
                const result = await updateCart('delete_item', cartId);
                if (result.status === 'success') {
                    document.getElementById(`cart-item-${cartId}`).remove();
                    document.getElementById('grand-total').textContent = '$' + result.total;
                    // Check if cart is now empty
                    if (document.querySelectorAll('.quantity-input').length === 0) {
                        location.reload(); // Reload to show empty cart message
                    }
                } else {
                    alert(result.message);
                }
            }
        }
    });
});
</script>

<?php 
$stmt->close();
require_once 'common/bottom.php'; 
?>