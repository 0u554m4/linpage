<?php

session_start();
require_once '../config.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
}

// Fetch all orders
$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard - Ensemble Lin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold">Admin Dashboard</h1>
                    </div>
                    <div class="flex items-center">
                        <a href="logout.php" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h2 class="text-lg leading-6 font-medium text-gray-900">Orders</h2>
                    </div>
                    <div class="border-t border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if ($orders && $orders->num_rows > 0): ?>
                                        <?php while ($order = $orders->fetch_assoc()): ?>
                                            <?php
                                                $total_price = isset($order['total_price']) ? (float)$order['total_price'] : 0.0;
                                                $status = $order['status'] ?? 'pending';
                                            ?>
                                            <tr>
                                                <td class="px-6 py-4 text-sm text-gray-500">#<?php echo (int)$order['id']; ?></td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($order['name']); ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($order['phone']); ?></div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900">
                                                        Color: <?php echo htmlspecialchars($order['color']); ?><br />
                                                        Size: <?php echo htmlspecialchars($order['size']); ?><br />
                                                        Quantity: <?php echo (int)$order['quantity']; ?>
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($order['wilaya']); ?><br />
                                                        <?php echo htmlspecialchars($order['address']); ?>
                                                    </div>
                                                </td>

                                                <td class="px-6 py-4 text-sm text-gray-500">
                                                    <?php echo number_format((float)$order['price'], 0, ',', ' '); ?> DA
                                                </td>


                                                <td class="px-6 py-4">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        <?php
                                                            switch ($status) {
                                                                case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                                case 'processing': echo 'bg-blue-100 text-blue-800'; break;
                                                                case 'completed': echo 'bg-green-100 text-green-800'; break;
                                                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                                                default: echo 'bg-gray-100 text-gray-800';
                                                            }
                                                        ?>">
                                                        <?php echo ucfirst($status); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500">
                                                    <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?>
                                                </td>
                                                <td class="px-6 py-4 text-sm font-medium">
                                                    <form method="POST" class="inline-block">
                                                        <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>" />
                                                        <select name="status" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md">
                                                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                        </select>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No orders found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
