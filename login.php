<?php
require_once 'common/config.php';

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// If user is already logged in, redirect to home
// --- THE FIX IS HERE ---
// Instead of calling a non-existent function is_logged_in(),
// we directly check the session variable.
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// --- AJAX Handler ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

    if ($_POST['action'] === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $response['message'] = 'Please fill in all fields.';
        } else {
            $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $response = ['status' => 'success', 'message' => 'Login successful! Redirecting...'];
                } else {
                    $response['message'] = 'Invalid email or password.';
                }
            } else {
                $response['message'] = 'Invalid email or password.';
            }
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'signup') {
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($phone) || empty($email) || empty($password)) {
            $response['message'] = 'Please fill all fields for signup.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Invalid email format.';
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $response['message'] = 'An account with this email already exists.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $conn->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
                $insert_stmt->bind_param("ssss", $name, $phone, $email, $hashed_password);
                if ($insert_stmt->execute()) {
                    // Automatically log in the new user
                    $_SESSION['user_id'] = $insert_stmt->insert_id;
                    $_SESSION['user_name'] = $name;
                    $response = ['status' => 'success', 'message' => 'Signup successful! Redirecting...'];
                } else {
                    $response['message'] = 'Failed to create account. Please try again.';
                }
                $insert_stmt->close();
            }
            $stmt->close();
        }
    }
    
    echo json_encode($response);
    $conn->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Login / Sign Up - Quick Kart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { -webkit-user-select: none; user-select: none; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="w-full max-w-md p-6">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-blue-600">Quick Kart</h1>
        <p class="text-gray-500">Your one-stop shop</p>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Tabs -->
        <div class="flex">
            <button id="login-tab-btn" class="w-1/2 p-4 text-center font-semibold text-white bg-blue-600">Login</button>
            <button id="signup-tab-btn" class="w-1/2 p-4 text-center font-semibold text-gray-600 bg-gray-200">Sign Up</button>
        </div>

        <!-- Forms Container -->
        <div class="p-6">
            <!-- Login Form -->
            <form id="login-form">
                <div id="login-message" class="hidden mb-4 text-center text-sm p-2 rounded"></div>
                <input type="hidden" name="action" value="login">
                <div class="mb-4">
                    <label for="login-email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="login-email" name="email" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="mb-6">
                    <label for="login-password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="login-password" name="password" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <button type="submit" id="login-btn" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center justify-center">
                    <span class="btn-text">Login</span>
                    <i class="fas fa-spinner fa-spin ml-2 hidden"></i>
                </button>
            </form>

            <!-- Signup Form (hidden by default) -->
            <form id="signup-form" class="hidden">
                 <div id="signup-message" class="hidden mb-4 text-center text-sm p-2 rounded"></div>
                <input type="hidden" name="action" value="signup">
                <div class="mb-4">
                    <label for="signup-name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" id="signup-name" name="name" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div class="mb-4">
                    <label for="signup-phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="tel" id="signup-phone" name="phone" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div class="mb-4">
                    <label for="signup-email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="signup-email" name="email" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div class="mb-6">
                    <label for="signup-password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="signup-password" name="password" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm" required>
                </div>
                <button type="submit" id="signup-btn" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 flex items-center justify-center">
                    <span class="btn-text">Sign Up</span>
                    <i class="fas fa-spinner fa-spin ml-2 hidden"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const loginTabBtn = document.getElementById('login-tab-btn');
    const signupTabBtn = document.getElementById('signup-tab-btn');
    const loginForm = document.getElementById('login-form');
    const signupForm = document.getElementById('signup-form');

    loginTabBtn.addEventListener('click', () => {
        loginForm.classList.remove('hidden');
        signupForm.classList.add('hidden');
        loginTabBtn.classList.replace('bg-gray-200', 'bg-blue-600');
        loginTabBtn.classList.replace('text-gray-600', 'text-white');
        signupTabBtn.classList.replace('bg-blue-600', 'bg-gray-200');
        signupTabBtn.classList.replace('text-white', 'text-gray-600');
    });

    signupTabBtn.addEventListener('click', () => {
        signupForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
        signupTabBtn.classList.replace('bg-gray-200', 'bg-blue-600');
        signupTabBtn.classList.replace('text-gray-600', 'text-white');
        loginTabBtn.classList.replace('bg-blue-600', 'bg-gray-200');
        loginTabBtn.classList.replace('text-white', 'text-gray-600');
    });

    // Universal Form Handler
    const handleFormSubmit = async (form, messageEl, buttonEl) => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const spinner = buttonEl.querySelector('.fa-spinner');
            const btnText = buttonEl.querySelector('.btn-text');
            
            spinner.classList.remove('hidden');
            btnText.classList.add('hidden');
            buttonEl.disabled = true;
            messageEl.classList.add('hidden');

            try {
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                messageEl.textContent = result.message;
                messageEl.classList.remove('hidden');
                if (result.status === 'success') {
                    messageEl.classList.remove('bg-red-100', 'text-red-700');
                    messageEl.classList.add('bg-green-100', 'text-green-700');
                    setTimeout(() => window.location.href = 'index.php', 1000);
                } else {
                    messageEl.classList.remove('bg-green-100', 'text-green-700');
                    messageEl.classList.add('bg-red-100', 'text-red-700');
                }
            } catch (error) {
                messageEl.textContent = 'A network error occurred. Please try again.';
                messageEl.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                messageEl.classList.add('bg-red-100', 'text-red-700');
            } finally {
                spinner.classList.add('hidden');
                btnText.classList.remove('hidden');
                buttonEl.disabled = false;
            }
        });
    };

    handleFormSubmit(loginForm, document.getElementById('login-message'), document.getElementById('login-btn'));
    handleFormSubmit(signupForm, document.getElementById('signup-message'), document.getElementById('signup-btn'));
</script>

</body>
</html>