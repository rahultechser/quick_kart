<?php
require_once __DIR__ . '/../common/config.php';

// AJAX handler for status update
if (isset($_POST['action']) && $_POST['action'] == 'update_status') {
    header('Content-Type: application/json');
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Status updated.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update status.']);
    }
    exit;
}

require_once 'common/header.php';

$order_id = (int)$_GET['id'];
// Get Order Details
$order_stmt = $conn->prepare("SELECT o.*, u.name as user_name, u.email, u.phone as user_phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

// Get Order Items
$items_stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result();
?>
<div class="bg-white p-6 rounded-lg shadow-lg">
    <a href="order.php" class="text-blue-600 mb-4 inline-block"><i class="fas fa-arrow-left"></i> Back to Orders</a>
    <h2 class="text-2xl font-bold mb-4">Order Details #<?php echo $order['id']; ?></h2>
    
    <div id="status-message" class="hidden p-3 rounded-md mb-4 text-sm"></div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Customer & Shipping -->
        <div class="md:col-span-1 space-y-4">
            <div class="p-4 border rounded-lg">
                <h3 class="font-bold mb-2">Customer Details</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['user_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['user_phone']); ?></p>
            </div>
             <div class="p-4 border rounded-lg">
                <h3 class="font-bold mb-2">Shipping Address</h3>
                <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
            </div>
            <div class="p-4 border rounded-lg">
                <h3 class="font-bold mb-2">Update Status</h3>
                <select id="status-select" class="w-full p-2 border rounded">
                    <?php foreach (['Placed', 'Dispatched', 'Delivered', 'Cancelled'] as $status): ?>
                    <option value="<?php echo $status; ?>" <?php echo ($order['status'] == $status) ? 'selected' : ''; ?>><?php echo $status; ?></option>
                    <?php endforeach; ?>
                </select>
                <button id="update-status-btn" class="w-full mt-2 bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Update</button>
            </div>
        </div>

        <!-- Order Items -->
        <div class="md:col-span-2 p-4 border rounded-lg">
            <h3 class="font-bold mb-2">Ordered Items</h3>
            <div class="space-y-3">
                <?php while($item = $items->fetch_assoc()): ?>
                <div class="flex items-center gap-4 border-b pb-2">
                    <img src="uploads/<?php echo $item['image']; ?>" class="w-16 h-16 rounded object-cover">
                    <div class="flex-1">
                        <p class="font-semibold"><?php echo htmlspecialchars($item['product_name']); ?></p>
                        <p class="text-sm text-gray-500">Qty: <?php echo $item['quantity']; ?> x $<?php echo $item['price']; ?></p>
                    </div>
                    <p class="font-bold">$<?php echo number_format($item['quantity'] * $item['price'], 2); ?></p>
                </div>
                <?php endwhile; ?>
            </div>
            <div class="text-right mt-4 text-xl font-bold">
                Total: $<?php echo $order['total_amount']; ?>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('update-status-btn').addEventListener('click', () => {
    const status = document.getElementById('status-select').value;
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('order_id', <?php echo $order_id; ?>);
    formData.append('status', status);

    const msgBox = document.getElementById('status-message');

    fetch('order_detail.php?id=<?php echo $order_id; ?>', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            msgBox.textContent = data.message;
            msgBox.classList.remove('hidden');
            if (data.status === 'success') {
                msgBox.className = 'p-3 rounded-md mb-4 text-sm bg-green-100 text-green-700';
                setTimeout(() => location.reload(), 1000);
            } else {
                msgBox.className = 'p-3 rounded-md mb-4 text-sm bg-red-100 text-red-700';
            }
        });
});
</script>
<?php require_once 'common/bottom.php'; ?>