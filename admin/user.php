<?php
require_once 'common/header.php';
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<div class="bg-white p-6 rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold mb-6">Registered Users</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">ID</th>
                    <th class="py-3 px-4 text-left">Name</th>
                    <th class="py-3 px-4 text-left">Email</th>
                    <th class="py-3 px-4 text-left">Phone</th>
                    <th class="py-3 px-4 text-left">Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = $users->fetch_assoc()): ?>
                <tr class="border-b">
                    <td class="py-3 px-4"><?php echo $user['id']; ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($user['name']); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($user['phone']); ?></td>
                    <td class="py-3 px-4"><?php echo date('d M, Y', strtotime($user['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once 'common/bottom.php'; ?>