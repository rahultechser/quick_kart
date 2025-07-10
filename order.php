<?php
// Step 1: Include config for session and database.
require_once 'common/config.php';

// Step 2: Perform all PHP logic, like login checks, BEFORE any HTML output.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Step 3: Now it's safe to include the header and start printing HTML.
require_once 'common/header.php';

// --- The rest of the page logic for fetching orders ---
$orders_query = $conn->prepare(
    "SELECT o.*, 
    (SELECT p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) as product_image
    FROM orders o 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC"
);
$orders_query->bind_param("i", $user_id);
$orders_query->execute();
$orders_result = $orders_query->get_result();

$active_orders = [];
$history_orders = [];
while ($order = $orders_result->fetch_assoc()) {
    if (in_array($order['status'], ['Placed', 'Dispatched'])) {
        $active_orders[] = $order;
    } else {
        $history_orders[] = $order;
    }
}
$orders_query->close();

function render_order_card($order, $conn) {
    // This function remains the same as before.
    $items_query = $conn->prepare(
        "SELECT p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ? LIMIT 2"
    );
    $items_query->bind_param("i", $order['id']);
    $items_query->execute();
    $items_result = $items_query->get_result();
    $item_names = [];
    while($item = $items_result->fetch_assoc()){
        $item_names[] = htmlspecialchars($item['name']);
    }
    $items_query->close();
    $product_preview_text = implode(', ', $item_names) . ($items_result->num_rows > 1 ? '...' : '');

    $statuses = ['Placed', 'Dispatched', 'Delivered'];
    $current_status_index = array_search($order['status'], $statuses);
    if ($order['status'] === 'Cancelled') {
        $current_status_index = -1;
    }
?>
    <div class="bg-white rounded-lg shadow-md mb-4 overflow-hidden">
        <div class="p-4">
            <div class="flex gap-4">
                <img src="admin/uploads/<?php echo $order['product_image'] ?: 'placeholder.png'; ?>" alt="Product" class="w-16 h-16 rounded-md object-cover">
                <div class="flex-1">
                    <p class="font-semibold text-gray-800 truncate"><?php echo $product_preview_text; ?></p>
                    <p class="text-sm text-gray-500">Order #<?php echo $order['id']; ?></p>
                    <p class="text-lg font-bold text-blue-600 mt-1">$<?php echo number_format($order['total_amount'], 2); ?></p>
                </div>
            </div>
            
            <?php if ($order['status'] !== 'Cancelled'): ?>
            <!-- Progress Tracker -->
            <div class="mt-4">
                <div class="flex items-center">
                    <?php foreach ($statuses as $index => $status): 
                        $is_completed = $index <= $current_status_index;
                        $is_current = $index === $current_status_index;
                        $line_active = $index < $current_status_index;
                    ?>
                    <!-- Stage Node -->
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center <?php echo $is_completed ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'; ?>">
                            <i class="fas <?php echo ($status === 'Placed' ? 'fa-box' : ($status === 'Dispatched' ? 'fa-truck' : 'fa-check-circle')); ?>"></i>
                        </div>
                        <p class="text-xs mt-1 <?php echo $is_current ? 'font-bold text-blue-600' : 'text-gray-500'; ?>"><?php echo $status; ?></p>
                    </div>
                    <?php if ($index < count($statuses) - 1): ?>
                    <div class="flex-1 h-1 <?php echo $line_active ? 'bg-blue-600' : 'bg-gray-300'; ?>"></div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="mt-4 text-center bg-red-50 text-red-600 font-semibold p-3 rounded-lg">
                <i class="fas fa-times-circle mr-2"></i> Order Cancelled
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php
}
?>

<h1 class="text-2xl font-bold mb-4">My Orders</h1>

<?php if (isset($_GET['success'])): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
    <strong class="font-bold">Success!</strong>
    <span class="block sm:inline">Your order has been placed successfully.</span>
</div>
<?php endif; ?>

<!-- Tabs -->
<div class="mb-4 border-b border-gray-200">
    <nav class="flex -mb-px" id="order-tabs">
        <button data-target="active-orders" class="tab-btn whitespace-nowrap py-4 px-6 text-sm font-medium border-b-2 active-nav">
            Active Orders
        </button>
        <button data-target="order-history" class="tab-btn whitespace-nowrap py-4 px-6 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
            Order History
        </button>
    </nav>
</div>

<!-- Tab Content -->
<div id="tab-content">
    <div id="active-orders" class="tab-pane">
        <?php if (count($active_orders) > 0): ?>
            <?php foreach ($active_orders as $order) { render_order_card($order, $conn); } ?>
        <?php else: ?>
            <p class="text-center text-gray-500 py-8">No active orders found.</p>
        <?php endif; ?>
    </div>
    <div id="order-history" class="tab-pane hidden">
         <?php if (count($history_orders) > 0): ?>
            <?php foreach ($history_orders as $order) { render_order_card($order, $conn); } ?>
        <?php else: ?>
            <p class="text-center text-gray-500 py-8">Your order history is empty.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tab-btn');
    const panes = document.querySelectorAll('.tab-pane');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active-nav', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300'));
            panes.forEach(p => p.classList.add('hidden'));

            tab.classList.add('active-nav');
            tab.classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            
            const targetPane = document.getElementById(tab.dataset.target);
            targetPane.classList.remove('hidden');

            tabs.forEach(t => {
                if (!t.classList.contains('active-nav')) {
                    t.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                }
            });
        });
    });
});
</script>

<?php require_once 'common/bottom.php'; ?>