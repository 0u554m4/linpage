<?php
session_start();
require_once '../config.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $stmt = $conn->prepare("INSERT INTO orders (name, phone, color, size, quantity, wilaya, address, price, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("ssssissd", $_POST['name'], $_POST['phone'], $_POST['color'], $_POST['size'], $_POST['quantity'], $_POST['wilaya'], $_POST['address'], $_POST['price']);
    $stmt->execute();
}

// Handle Update (status or details)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $stmt = $conn->prepare("UPDATE orders SET name=?, phone=?, color=?, size=?, quantity=?, wilaya=?, address=?, price=?, status=? WHERE id=?");
    $stmt->bind_param("ssssissdsi", $_POST['name'], $_POST['phone'], $_POST['color'], $_POST['size'], $_POST['quantity'], $_POST['wilaya'], $_POST['address'], $_POST['price'], $_POST['status'], $_POST['order_id']);
    $stmt->execute();
}

// Handle Status Change Only
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'status') {
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $_POST['status'], $_POST['order_id']);
    $stmt->execute();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
}

// Fetch Orders
$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard - Orders</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
<script>
function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this order?")) {
        window.location.href = "?delete=" + id;
    }
}
</script>
</head>
<body class="bg-gray-100">
<div class="min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 flex justify-between h-16">
            <div class="flex items-center">
                <h1 class="text-xl font-bold">Admin Dashboard</h1>
            </div>
            <div class="flex items-center gap-4">
                <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                    <i class="fas fa-plus"></i> New Order
                </button>
                <a href="logout.php" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5">
                <h2 class="text-lg font-medium">Orders</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2">ID</th>
                            <th class="px-4 py-2">Customer</th>
                            <th class="px-4 py-2">Details</th>
                            <th class="px-4 py-2">Price</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while ($o = $orders->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-2">#<?= $o['id'] ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($o['name']) ?><br><span class="text-gray-500"><?= htmlspecialchars($o['phone']) ?></span></td>
                            <td class="px-4 py-2">
                                Color: <?= htmlspecialchars($o['color']) ?><br>
                                Size: <?= htmlspecialchars($o['size']) ?><br>
                                Qty: <?= $o['quantity'] ?>
                            </td>
                            <td class="px-4 py-2"><?= number_format($o['price'], 0, ',', ' ') ?> DA</td>
                            <td class="px-4 py-2">
                                <form method="POST">
                                    <input type="hidden" name="action" value="status">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <select name="status" onchange="this.form.submit()" class="border rounded p-1 text-sm">
                                        <?php foreach (['pending','processing','completed','cancelled'] as $st): ?>
                                            <option value="<?= $st ?>" <?= $o['status']===$st ? 'selected':'' ?>><?= ucfirst($st) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-2"><?= date('Y-m-d H:i', strtotime($o['created_at'])) ?></td>
                            <td class="px-4 py-2">
                                <button onclick="openEditModal(<?= htmlspecialchars(json_encode($o)) ?>)" class="text-blue-600 hover:underline">Edit</button> |
                                <button onclick="confirmDelete(<?= $o['id'] ?>)" class="text-red-600 hover:underline">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Create Modal -->
<div id="createModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Create Order</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <input type="text" name="name" placeholder="Customer Name" class="border p-2 w-full mb-2" required>
            <input type="text" name="phone" placeholder="Phone" class="border p-2 w-full mb-2" >
            <input type="text" name="color" placeholder="Color" class="border p-2 w-full mb-2">
            <input type="text" name="size" placeholder="Size" class="border p-2 w-full mb-2">
            <input type="number" name="quantity" placeholder="Quantity" class="border p-2 w-full mb-2" required>
            <input type="text" name="wilaya" placeholder="Wilaya" class="border p-2 w-full mb-2">
            <textarea name="address" placeholder="Address" class="border p-2 w-full mb-2"></textarea>
            <input type="number" name="price" step="0.01" placeholder="Price" class="border p-2 w-full mb-2" required>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="px-3 py-1 border rounded">Cancel</button>
                <button class="px-3 py-1 bg-green-500 text-white rounded">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Edit Order</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="order_id" id="edit_order_id">
            <input type="text" name="name" id="edit_name" class="border p-2 w-full mb-2" required>
            <input type="text" name="phone" id="edit_phone" class="border p-2 w-full mb-2" >
            <input type="text" name="color" id="edit_color" class="border p-2 w-full mb-2">
            <input type="text" name="size" id="edit_size" class="border p-2 w-full mb-2">
            <input type="number" name="quantity" id="edit_quantity" class="border p-2 w-full mb-2" required>
            <input type="text" name="wilaya" id="edit_wilaya" class="border p-2 w-full mb-2">
            <textarea name="address" id="edit_address" class="border p-2 w-full mb-2"></textarea>
            <input type="number" name="price" step="0.01" id="edit_price" class="border p-2 w-full mb-2" required>
            <select name="status" id="edit_status" class="border p-2 w-full mb-2">
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-3 py-1 border rounded">Cancel</button>
                <button class="px-3 py-1 bg-blue-500 text-white rounded">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(order) {
    document.getElementById('edit_order_id').value = order.id;
    document.getElementById('edit_name').value = order.name;
    document.getElementById('edit_phone').value = order.phone;
    document.getElementById('edit_color').value = order.color;
    document.getElementById('edit_size').value = order.size;
    document.getElementById('edit_quantity').value = order.quantity;
    document.getElementById('edit_wilaya').value = order.wilaya;
    document.getElementById('edit_address').value = order.address;
    document.getElementById('edit_price').value = order.price;
    document.getElementById('edit_status').value = order.status;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>

</body>
</html>
