<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Quick Kart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* To disable text selection */
        body {
            -webkit-user-select: none; /* Safari */
            -ms-user-select: none; /* IE 10 and IE 11 */
            user-select: none; /* Standard syntax */
        }
        /* Custom scrollbar for webkit browsers */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .active-nav { color: #1D4ED8; border-bottom: 2px solid #1D4ED8; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-800">
    <div class="relative min-h-screen md:flex flex-col" id="app-container">
        
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <div class="flex-1 pb-20">
            <!-- Top Header -->
            <header class="bg-white shadow-md p-4 flex items-center justify-between sticky top-0 z-40">
                <button id="sidebar-toggle" class="text-gray-700 text-xl">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="index.php" class="text-2xl font-bold text-blue-600">Quick Kart</a>
                <a href="cart.php" class="relative text-gray-700 text-xl">
                    <i class="fas fa-shopping-cart"></i>
                    <?php
                    if (isset($_SESSION['user_id'])) {
                        $cart_count_query = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = {$_SESSION['user_id']}");
                        $cart_count = $cart_count_query->fetch_assoc()['total'] ?? 0;
                        if ($cart_count > 0) {
                            echo "<span class='absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center'>$cart_count</span>";
                        }
                    }
                    ?>
                </a>
            </header>

            <main class="p-4">