<?php
session_start();
require_once '../config.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Initialize message variables
$success_message = '';
$error_message = '';

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $stmt = $conn->prepare("INSERT INTO orders (name, phone, color, size, quantity, wilaya, address, price, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("ssssissd", $_POST['name'], $_POST['phone'], $_POST['color'], $_POST['size'], $_POST['quantity'], $_POST['wilaya'], $_POST['address'], $_POST['price']);
    
    if ($stmt->execute()) {
        $success_message = "Order created successfully!";
    } else {
        $error_message = "Error creating order: " . $conn->error;
    }
    $stmt->close();
}

// Handle Update (status or details)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $stmt = $conn->prepare("UPDATE orders SET name=?, phone=?, color=?, size=?, quantity=?, wilaya=?, address=?, price=?, status=? WHERE id=?");
    $stmt->bind_param("ssssissdsi", $_POST['name'], $_POST['phone'], $_POST['color'], $_POST['size'], $_POST['quantity'], $_POST['wilaya'], $_POST['address'], $_POST['price'], $_POST['status'], $_POST['order_id']);
    
    if ($stmt->execute()) {
        $success_message = "Order updated successfully!";
    } else {
        $error_message = "Error updating order: " . $conn->error;
    }
    $stmt->close();
}

// Handle Status Change Only
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'status') {
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $_POST['status'], $_POST['order_id']);
    
    if ($stmt->execute()) {
        $success_message = "Status updated successfully!";
    } else {
        $error_message = "Error updating status: " . $conn->error;
    }
    $stmt->close();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    
    if ($stmt->execute()) {
        $success_message = "Order deleted successfully!";
    } else {
        $error_message = "Error deleting order: " . $conn->error;
    }
    $stmt->close();
}

// Fetch Orders
$result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");

if (!$result) {
    die("Database error: " . $conn->error);
}

// Get statistics
$stats = [
    'total_orders' => 0,
    'pending_orders' => 0,
    'completed_orders' => 0,
    'total_revenue' => 0
];

$stats_query = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'completed' THEN price * quantity ELSE 0 END) as revenue
    FROM orders
");

if ($stats_query && $row = $stats_query->fetch_assoc()) {
    $stats = [
        'total_orders' => $row['total'] ?? 0,
        'pending_orders' => $row['pending'] ?? 0,
        'completed_orders' => $row['completed'] ?? 0,
        'total_revenue' => $row['revenue'] ?? 0
    ];
}

$orders = $result;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Order Management</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Additional inline styles for the PHP template */
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: var(--spacing-md);
        }
        
        .stat-icon.primary { background: linear-gradient(135deg, var(--primary-100), var(--primary-50)); color: var(--primary-600); }
        .stat-icon.warning { background: linear-gradient(135deg, #fef3c7, #fffbeb); color: #d97706; }
        .stat-icon.success { background: linear-gradient(135deg, #d1fae5, #ecfdf5); color: var(--success-600); }
        .stat-icon.info { background: linear-gradient(135deg, #cffafe, #e6fffa); color: #0891b2; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="admin-navbar">
        <div class="nav-container">
            <div class="nav-title">
                <i class="fas fa-store" style="color: var(--primary-600);"></i>
                Admin Dashboard
            </div>
            <div class="nav-actions">
                <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="btn btn-success">
                    <i class="fas fa-plus"></i> 
                    <span>New Order</span>
                </button>
                <a href="logout.php" class="btn-danger-link" style="padding: var(--spacing-sm) var(--spacing-md);">
                    <i class="fas fa-sign-out-alt"></i> 
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?= number_format($stats['total_orders']) ?></div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i> 
                    +<?= number_format($stats['total_orders'] > 0 ? round(($stats['completed_orders'] / $stats['total_orders']) * 100) : 0) ?>% completion rate
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-label">Pending Orders</div>
                <div class="stat-value"><?= number_format($stats['pending_orders']) ?></div>
                <div class="stat-change" style="color: #d97706; background-color: #fef3c7;">
                    <i class="fas fa-hourglass-half"></i> 
                    Awaiting processing
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-label">Completed</div>
                <div class="stat-value"><?= number_format($stats['completed_orders']) ?></div>
                <div class="stat-change">
                    <i class="fas fa-check"></i> 
                    Successfully delivered
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value"><?= number_format($stats['total_revenue'], 0, ',', ' ') ?> DA</div>
                <div class="stat-change">
                    <i class="fas fa-chart-line"></i> 
                    From completed orders
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success_message) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error_message) ?>
        </div>
        <?php endif; ?>

        <!-- Orders Table Card -->
        <div class="dashboard-card">
            <div class="card-header flex-split">
                <div class="card-title">
                    <i class="fas fa-list" style="color: var(--primary-600);"></i>
                    Orders Management
                </div>
                <div class="flex-center gap-sm">
                    <span class="status-badge status-active">
                        <i class="fas fa-circle" style="font-size: 8px;"></i>
                        Active Orders: <?= $stats['pending_orders'] + $stats['completed_orders'] ?>
                    </span>
                </div>
            </div>
            
            <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Product Details</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders->num_rows > 0): ?>
                            <?php while ($o = $orders->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span style="font-weight: 600; color: var(--gray-900);">#<?= str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                </td>
                                <td>
                                    <div style="font-weight: 600;"><?= htmlspecialchars($o['name']) ?></div>
                                    <span class="text-gray">
                                        <i class="fas fa-phone" style="font-size: 12px;"></i> 
                                        <?= htmlspecialchars($o['phone']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="flex-center" style="justify-content: flex-start; gap: var(--spacing-sm);">
                                        <span class="status-badge" style="background: var(--gray-100); color: var(--gray-700);">
                                            <i class="fas fa-palette"></i> <?= htmlspecialchars($o['color']) ?>
                                        </span>
                                        <span class="status-badge" style="background: var(--gray-100); color: var(--gray-700);">
                                            <i class="fas fa-ruler"></i> <?= htmlspecialchars($o['size']) ?>
                                        </span>
                                        <span class="status-badge" style="background: var(--gray-100); color: var(--gray-700);">
                                            <i class="fas fa-cubes"></i> x<?= $o['quantity'] ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($o['wilaya'])): ?>
                                        <span class="text-gray" style="display: block; margin-top: var(--spacing-xs);">
                                            <i class="fas fa-map-marker-alt" style="font-size: 12px;"></i> 
                                            Wilaya <?= htmlspecialchars($o['wilaya']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: var(--primary-600);">
                                        <?= number_format($o['price'], 0, ',', ' ') ?> DA
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="status">
                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                        <select name="status" onchange="this.form.submit()" class="form-control" style="width: auto; min-width: 120px; padding: 4px 8px;">
                                            <?php 
                                            $statuses = [
                                                'pending' => '⏳ Pending',
                                                'processing' => '🔄 Processing',
                                                'completed' => '✅ Completed',
                                                'cancelled' => '❌ Cancelled'
                                            ];
                                            foreach ($statuses as $value => $label): 
                                            ?>
                                                <option value="<?= $value ?>" <?= $o['status'] === $value ? 'selected' : '' ?>>
                                                    <?= $label ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?= date('d M Y', strtotime($o['created_at'])) ?></div>
                                    <span class="text-gray"><?= date('H:i', strtotime($o['created_at'])) ?></span>
                                </td>
                                <td>
                                    <div class="flex-center gap-sm">
                                        <button onclick="openEditModal(<?= htmlspecialchars(json_encode($o)) ?>)" class="btn-link" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="confirmDelete(<?= $o['id'] ?>)" class="btn-danger-link" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-inbox"></i>
                                    </div>
                                    <h3 style="margin-bottom: var(--spacing-sm);">No orders yet</h3>
                                    <p style="color: var(--gray-500);">Create your first order to get started</p>
                                    <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="btn btn-primary" style="margin-top: var(--spacing-md);">
                                        <i class="fas fa-plus"></i> Create Order
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Table Footer with Summary -->
            <?php if ($orders->num_rows > 0): ?>
            <div style="padding: var(--spacing-md) var(--spacing-lg); border-top: 1px solid var(--gray-200); background-color: var(--gray-50);">
                <div class="flex-split">
                    <span class="text-gray">
                        <i class="fas fa-list-ul"></i> 
                        Showing <?= $orders->num_rows ?> orders
                    </span>
                    <span class="text-gray">
                        Last updated: <?= date('d M Y H:i') ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Create Modal -->
    <div id="createModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-title">
                <span><i class="fas fa-plus-circle" style="color: var(--success-600);"></i> Create New Order</span>
                <button onclick="document.getElementById('createModal').classList.add('hidden')" class="btn-link" style="font-size: 1.25rem;">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label class="form-label">Customer Name *</label>
                    <input type="text" name="name" placeholder="Enter customer name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" placeholder="Enter phone number" class="form-control">
                </div>
                
                <div class="flex-split gap-md">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" placeholder="e.g., Black, Red" class="form-control">
                    </div>
                    
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Size</label>
                        <input type="text" name="size" placeholder="e.g., M, L, XL" class="form-control">
                    </div>
                    
                    <div class="form-group" style="flex: 0.5;">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="quantity" placeholder="1" min="1" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Wilaya</label>
                    <input type="text" name="wilaya" placeholder="Enter wilaya number" class="form-control">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" placeholder="Enter complete address" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Price (DA) *</label>
                    <input type="number" name="price" step="0.01" placeholder="Enter price" class="form-control" required>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="btn btn-outline">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-title">
                <span><i class="fas fa-edit" style="color: var(--primary-600);"></i> Edit Order</span>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="btn-link" style="font-size: 1.25rem;">&times;</button>
            </div>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="order_id" id="edit_order_id">
                
                <div class="form-group">
                    <label class="form-label">Customer Name *</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" id="edit_phone" class="form-control">
                </div>
                
                <div class="flex-split gap-md">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" id="edit_color" class="form-control">
                    </div>
                    
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Size</label>
                        <input type="text" name="size" id="edit_size" class="form-control">
                    </div>
                    
                    <div class="form-group" style="flex: 0.5;">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="quantity" id="edit_quantity" min="1" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Wilaya</label>
                    <input type="text" name="wilaya" id="edit_wilaya" class="form-control">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" id="edit_address" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Price (DA) *</label>
                    <input type="number" name="price" step="0.01" id="edit_price" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="edit_status" class="form-control">
                        <option value="pending">⏳ Pending</option>
                        <option value="processing">🔄 Processing</option>
                        <option value="completed">✅ Completed</option>
                        <option value="cancelled">❌ Cancelled</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="btn btn-outline">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function confirmDelete(id) {
        if (confirm("Are you sure you want to delete this order? This action cannot be undone.")) {
            window.location.href = "?delete=" + id;
        }
    }
    
    function openEditModal(order) {
        document.getElementById('edit_order_id').value = order.id;
        document.getElementById('edit_name').value = order.name || '';
        document.getElementById('edit_phone').value = order.phone || '';
        document.getElementById('edit_color').value = order.color || '';
        document.getElementById('edit_size').value = order.size || '';
        document.getElementById('edit_quantity').value = order.quantity || '';
        document.getElementById('edit_wilaya').value = order.wilaya || '';
        document.getElementById('edit_address').value = order.address || '';
        document.getElementById('edit_price').value = order.price || '';
        document.getElementById('edit_status').value = order.status || 'pending';
        
        document.getElementById('editModal').classList.remove('hidden');
    }
    
    // Close modals when clicking outside
    window.onclick = function(event) {
        const modals = document.querySelectorAll('.modal-overlay');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
    </script>
</body>
</html>
<?php $conn->close(); ?>