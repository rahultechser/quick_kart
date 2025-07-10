<?php
require_once 'common/header.php';
$orders = $conn->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
?>
<div class="bg-white p-6 rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold mb-6">Manage Orders</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">Order ID</th>
                    <th class="py-3 px-4 text-left">User</th>
                    <th class="py-3 px-4 text-left">Amount</th>
                    <th class="py-3 px-4 text-left">Status</th>
                    <th class="py-3 px-4 text-left">Date</th>
                    <th class="py-3 px-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($order = $orders->fetch_assoc()): 
                    $status_color = [
                        'Placed' => 'bg-blue-200 text-blue-800',
                        'Dispatched' => 'bg-yellow-200 text-yellow-800',
                        'Delivered' => 'bg-green-200 text-green-800',
                        'Cancelled' => 'bg-red-200 text-red-800'
                    ][$order['status']];
                ?>
                <tr class="border-b">
                    <td class="py-3 px-4">#<?php echo $order['id']; ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($order['user_name']); ?></td>
                    <td class="py-3 px-4">$<?php echo $order['total_amount']; ?></td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $status_color; ?>">
                            <?php echo $order['status']; ?>
                        </span>
                    </td>
                    <td class="py-3 px-4"><?php echo date('d M, Y', strtotime($order['created_at'])); ?></td>
                    <td class="py-3 px-4">
                        <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">View Details</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once 'common/bottom.php'; ?>