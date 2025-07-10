<?php
// Step 1: Include the configuration file with the correct path.
require_once __DIR__ . '/../common/config.php';

// Initialize variables for messages.
$message = '';
$msg_type = '';

// Step 2: Handle the form submission for changing the password.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_SESSION['admin_id'];
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($new_password) || empty($confirm_password)) {
        $message = 'Please fill in both password fields.';
        $msg_type = 'error';
    } elseif ($new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $admin_id);
        if ($stmt->execute()) {
            $message = 'Password updated successfully.';
            $msg_type = 'success';
        } else {
            $message = 'Failed to update password. Please try again.';
            $msg_type = 'error';
        }
        $stmt->close();
    } else {
        $message = 'Passwords do not match.';
        $msg_type = 'error';
    }
}

// Step 3: Include the admin header. This must come after all PHP logic.
require_once 'common/header.php';
?>

<!-- Main content for the settings page -->
<div class="bg-white p-6 rounded-lg shadow-lg max-w-md mx-auto">
    <h2 class="text-2xl font-bold mb-6">Admin Settings</h2>
    
    <?php if ($message): ?>
        <div class="p-3 mb-4 rounded-md text-sm <?php echo $msg_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="setting.php">
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-1">Username</label>
            <!-- Display the admin username from the session; it's read-only. -->
            <input type="text" value="<?php echo htmlspecialchars($_SESSION['admin_username']); ?>" class="w-full px-3 py-2 border rounded bg-gray-100 cursor-not-allowed" readonly>
        </div>
        <div class="mb-4">
            <label for="new_password" class="block text-gray-700 font-medium mb-1">New Password</label>
            <input type="password" id="new_password" name="new_password" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div class="mb-6">
            <label for="confirm_password" class="block text-gray-700 font-medium mb-1">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-300">Update Password</button>
    </form>
</div>

<?php 
// Step 4: Include the admin footer.
require_once 'common/bottom.php'; 
?>