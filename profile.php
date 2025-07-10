<?php
require_once 'common/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// --- AJAX Handler for Profile Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'An error occurred.'];

    // Update Profile Info
    if ($_POST['action'] === 'update_profile') {
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];

        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $address, $user_id);
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name; // Update session name
            $response = ['status' => 'success', 'message' => 'Profile updated successfully!'];
        } else {
            $response['message'] = 'Failed to update profile.';
        }
        $stmt->close();
    }
    // Change Password
    elseif ($_POST['action'] === 'change_password') {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if ($new_pass !== $confirm_pass) {
            $response['message'] = 'New passwords do not match.';
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result && password_verify($current_pass, $result['password'])) {
                $new_hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_hashed_pass, $user_id);
                if ($update_stmt->execute()) {
                    $response = ['status' => 'success', 'message' => 'Password changed successfully!'];
                } else {
                    $response['message'] = 'Failed to update password.';
                }
                $update_stmt->close();
            } else {
                $response['message'] = 'Incorrect current password.';
            }
            $stmt->close();
        }
    }

    echo json_encode($response);
    exit();
}


require_once 'common/header.php';
// Fetch user data
$stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<h1 class="text-2xl font-bold mb-6">My Profile</h1>

<div id="message-container" class="hidden mb-4 p-3 rounded-md text-sm"></div>

<!-- Edit Profile Form -->
<div class="bg-white p-4 rounded-lg shadow-md mb-6">
    <h2 class="text-lg font-semibold border-b pb-2 mb-4">Edit Profile</h2>
    <form id="profile-form">
        <input type="hidden" name="action" value="update_profile">
        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="mt-1 block w-full input-style">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email (Cannot be changed)</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="mt-1 block w-full input-style bg-gray-100" readonly>
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="mt-1 block w-full input-style">
            </div>
             <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                <textarea id="address" name="address" rows="3" class="mt-1 block w-full input-style"><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>
        </div>
        <button type="submit" class="w-full mt-4 bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700">Save Changes</button>
    </form>
</div>

<!-- Change Password Form -->
<div class="bg-white p-4 rounded-lg shadow-md mb-6">
    <h2 class="text-lg font-semibold border-b pb-2 mb-4">Change Password</h2>
    <form id="password-form">
        <input type="hidden" name="action" value="change_password">
        <div class="space-y-4">
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                <input type="password" id="current_password" name="current_password" class="mt-1 block w-full input-style">
            </div>
            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" id="new_password" name="new_password" class="mt-1 block w-full input-style">
            </div>
             <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="mt-1 block w-full input-style">
            </div>
        </div>
        <button type="submit" class="w-full mt-4 bg-gray-700 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-800">Update Password</button>
    </form>
</div>

<!-- Logout -->
<a href="login.php?logout=1" class="block w-full text-center bg-red-500 text-white font-bold py-3 rounded-lg hover:bg-red-600 mb-4">
    <i class="fas fa-sign-out-alt mr-2"></i>Logout
</a>

<style>.input-style { border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.5rem 0.75rem; }</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const messageContainer = document.getElementById('message-container');

    const handleFormSubmit = async (formId) => {
        const form = document.getElementById(formId);
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            
            try {
                const response = await fetch('profile.php', { method: 'POST', body: formData });
                const result = await response.json();
                
                messageContainer.textContent = result.message;
                messageContainer.classList.remove('hidden');
                if (result.status === 'success') {
                    messageContainer.className = 'mb-4 p-3 rounded-md text-sm bg-green-100 text-green-700';
                    if (formId === 'password-form') form.reset();
                } else {
                    messageContainer.className = 'mb-4 p-3 rounded-md text-sm bg-red-100 text-red-700';
                }
                window.scrollTo(0, 0); // Scroll to top to see message
            } catch (error) {
                messageContainer.textContent = 'A network error occurred.';
                messageContainer.className = 'mb-4 p-3 rounded-md text-sm bg-red-100 text-red-700';
                window.scrollTo(0, 0);
            }
        });
    };

    handleFormSubmit('profile-form');
    handleFormSubmit('password-form');
});
</script>

<?php require_once 'common/bottom.php'; ?>