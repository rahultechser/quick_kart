<?php
require_once __DIR__ . '/../../common/config.php';

// Protect all admin pages
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Quick Kart - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { -webkit-user-select: none; user-select: none; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .sidebar-link.active { background-color: #1D4ED8; color: white; }
    </style>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="flex justify-between items-center p-4 bg-white border-b-2 border-gray-200">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Admin Dashboard</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($admin_username); ?></span>
                <a href="login.php?logout=1" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-sign-out-alt fa-lg"></i>
                </a>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">