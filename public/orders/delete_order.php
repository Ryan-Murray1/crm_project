<?php
include("../../db.php");
$errors = [];

// Get order ID from URL
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid order ID.");
}
$order_id = intval($_GET['order_id']);

// Handle confirmation POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    // Begin transaction
    mysqli_begin_transaction($conn);
    try {
        // 1. Restore stock for each order item
        $items_sql = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
        $stmt = $conn->prepare($items_sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $items_result = $stmt->get_result();
        while ($item = $items_result->fetch_assoc()) {
            $conn->query("UPDATE products SET stock_quantity = stock_quantity + {$item['quantity']} WHERE product_id = {$item['product_id']}");
        }
        $stmt->close();

        // 2. Delete order items
        $del_items = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $del_items->bind_param("i", $order_id);
        $del_items->execute();
        $del_items->close();

        // 3. Delete order
        $del_order = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $del_order->bind_param("i", $order_id);
        $del_order->execute();
        $del_order->close();

        mysqli_commit($conn);
        header("Location: view_orders.php?message=Order deleted successfully");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $errors[] = "Failed to delete order: " . $e->getMessage();
    }
}

// Fetch order summary for confirmation
$order = null;
$stmt = $conn->prepare("SELECT o.order_id, o.order_date, o.status, o.total_amount, c.first_name, c.last_name FROM orders o JOIN customers c ON o.customer_id = c.customer_id WHERE o.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $order = $result->fetch_assoc();
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light no-card-hover">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-3 text-center text-accent">Delete Order</h2>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($order): ?>
                        <p class="mb-3 text-center">Are you sure you want to delete the following order?</p>
                        <div class="mb-3">
                            <div class="card bg-light border">
                                <div class="card-body p-3">
                                    <p class="mb-1"><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
                                    <p class="mb-1"><strong>Customer:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                                    <p class="mb-1"><strong>Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
                                    <p class="mb-1"><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                                    <p class="mb-0"><strong>Total Amount:</strong> <?php echo htmlspecialchars($order['total_amount']); ?></p>
                                </div>
                            </div>
                        </div>
                        <form method="POST" class="d-flex flex-column gap-2">
                            <input type="hidden" name="confirm" value="yes">
                            <button type="submit" class="btn btn-danger w-100">Yes, Delete</button>
                            <a href="view_orders.php" class="btn btn-link">Cancel</a>
                        </form>
                    <?php else: ?>
                        <p class="mb-2 text-center">Order not found.</p>
                        <div class="text-center">
                            <a href="view_orders.php" class="btn btn-link">Back to Orders List</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
