<?php
// --- IMPORTANT ---
// 1. CREATE A NEW, EMPTY DATABASE NAMED 'quick_kart_db'
// 2. RUN THIS FILE ONCE IN YOUR BROWSER: http://localhost/path/to/install.php
// 3. AFTER SUCCESS, DELETE THIS FILE.

$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = 'root';
$db_name = 'quick_kart_db';

$messages = [];
$error = '';

try {
    // Establish Connection
    $conn = new mysqli($db_host, $db_user, $db_pass);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create Database if it doesn't exist
    $sql_createdb = "CREATE DATABASE IF NOT EXISTS $db_name";
    if ($conn->query($sql_createdb) === TRUE) {
        $messages[] = "Database '$db_name' created successfully or already exists.";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    $conn->select_db($db_name);

    // --- Table Creation Queries ---
    $sql_admins = "CREATE TABLE IF NOT EXISTS `admins` (...)"; // As before
    $sql_users = "CREATE TABLE IF NOT EXISTS `users` (...)"; // As before
    $sql_categories = "CREATE TABLE IF NOT EXISTS `categories` (...)"; // As before
    $sql_products = "CREATE TABLE IF NOT EXISTS `products` (...)"; // As before
    $sql_cart = "CREATE TABLE IF NOT EXISTS `cart` (...)"; // As before
    $sql_orders = "CREATE TABLE IF NOT EXISTS `orders` (...)"; // As before
    $sql_order_items = "CREATE TABLE IF NOT EXISTS `order_items` (...)"; // As before

    // --- (Copy the full SQL statements from the original generated code here) ---
    // For brevity, I'm using placeholders. Replace them with the full SQL from my previous response.
    $sql_admins = "CREATE TABLE IF NOT EXISTS `admins` ( `id` int(11) NOT NULL AUTO_INCREMENT, `username` varchar(50) NOT NULL, `password` varchar(255) NOT NULL, PRIMARY KEY (`id`), UNIQUE KEY `username` (`username`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $sql_users = "CREATE TABLE IF NOT EXISTS `users` ( `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(100) NOT NULL, `email` varchar(100) NOT NULL, `phone` varchar(20) NOT NULL, `password` varchar(255) NOT NULL, `address` text, `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `email` (`email`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $sql_categories = "CREATE TABLE IF NOT EXISTS `categories` ( `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(100) NOT NULL, `image` varchar(255) DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $sql_products = "CREATE TABLE IF NOT EXISTS `products` ( `id` int(11) NOT NULL AUTO_INCREMENT, `category_id` int(11) NOT NULL, `name` varchar(255) NOT NULL, `description` text NOT NULL, `price` decimal(10,2) NOT NULL, `stock` int(11) NOT NULL DEFAULT '0', `image` varchar(255) DEFAULT NULL, `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `category_id` (`category_id`), FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $sql_cart = "CREATE TABLE IF NOT EXISTS `cart` ( `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `product_id` int(11) NOT NULL, `quantity` int(11) NOT NULL, PRIMARY KEY (`id`), KEY `user_id` (`user_id`), KEY `product_id` (`product_id`), FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE, FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $sql_orders = "CREATE TABLE IF NOT EXISTS `orders` ( `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `total_amount` decimal(10,2) NOT NULL, `shipping_address` text NOT NULL, `phone` varchar(20) NOT NULL, `status` enum('Placed','Dispatched','Delivered','Cancelled') NOT NULL DEFAULT 'Placed', `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `user_id` (`user_id`), FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $sql_order_items = "CREATE TABLE IF NOT EXISTS `order_items` ( `id` int(11) NOT NULL AUTO_INCREMENT, `order_id` int(11) NOT NULL, `product_id` int(11) NOT NULL, `quantity` int(11) NOT NULL, `price` decimal(10,2) NOT NULL, PRIMARY KEY (`id`), KEY `order_id` (`order_id`), KEY `product_id` (`product_id`), FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE, FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // Correct logical order for table creation
    $tables = [
        'admins' => $sql_admins,
        'users' => $sql_users,
        'categories' => $sql_categories,
        'products' => $sql_products,
        'orders' => $sql_orders,
        'cart' => $sql_cart,
        'order_items' => $sql_order_items,
    ];
    
    // THE FIX: Disable foreign key checks before creating tables
    $conn->query("SET FOREIGN_KEY_CHECKS=0;");

    foreach ($tables as $name => $sql) {
        if ($conn->query($sql) === TRUE) {
            $messages[] = "Table '{$name}' created successfully.";
        } else {
            throw new Exception("Error creating table '{$name}': " . $conn->error);
        }
    }
    
    // THE FIX: Re-enable foreign key checks after creation
    $conn->query("SET FOREIGN_KEY_CHECKS=1;");

    // --- Insert Default Admin ---
    $admin_user = 'admin';
    $admin_pass = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO `admins` (username, password) VALUES (?, ?) ON DUPLICATE KEY UPDATE password=VALUES(password)");
    $stmt->bind_param("ss", $admin_user, $admin_pass);
    if ($stmt->execute()) {
        $messages[] = "Default admin user created. (Username: <strong>admin</strong>, Password: <strong>password</strong>)";
    } else {
        throw new Exception("Error creating admin user: " . $stmt->error);
    }
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $error = "DB ERROR: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Kart Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-2xl bg-white shadow-lg rounded-lg p-8">
        <h1 class="text-2xl font-bold text-green-600 mb-4 border-b pb-3">Quick Kart Installation</h1>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                <p class="font-bold">An Error Occurred</p>
                <p><?php echo $error; ?></p>
            </div>
        <?php else: ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p class="font-bold">Installation Successful!</p>
                <p>All tables and default admin user have been created.</p>
            </div>
            <div class="space-y-2 text-sm text-gray-700">
                <?php foreach ($messages as $message): ?>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span><?php echo $message; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
             <div class="mt-8 text-center bg-yellow-100 text-yellow-800 p-4 rounded-lg">
                <p class="font-bold">IMPORTANT:</p>
                <p>Please delete this <strong>install.php</strong> file from your server now for security reasons.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>