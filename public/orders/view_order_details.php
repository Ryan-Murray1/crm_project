<?php
// Include the database connection file
include("../../db.php");

// Initialize variables
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = null;
$order_items = [];
$errors = [];

if ($order_id > 0) {
    // Fetch order info
    $sql = "SELECT o.order_id, o.order_date, o.status, o.total_amount, c.first_name, c.last_name, c.email
            FROM orders o
            JOIN customers c ON o.customer_id = c.customer_id
            WHERE o.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $order = $result->fetch_assoc();
    } else {
        $errors[] = "Order not found.";
    }
    $stmt->close();

    // Fetch order items
    $sql_items = "SELECT oi.product_id, p.name, oi.quantity, oi.price
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.product_id
                  WHERE oi.order_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    while ($row = $result_items->fetch_assoc()) {
        $order_items[] = $row;
    }
    $stmt_items->close();
} else {
    $errors[] = "Invalid order ID.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="mb-0 text-accent">Order Details</h2>
                        <a href="view_orders.php" class="btn btn-link">&larr; Back to Orders</a>
                    </div>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mb-3">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($order): ?>
                        <div class="mb-4">
                            <div class="row mb-2">
                                <div class="col-12 col-md-6">
                                    <p class="mb-1"><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
                                    <p class="mb-1"><strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
                                    <p class="mb-1"><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                                    <p class="mb-1"><strong>Total Amount:</strong> <?php echo htmlspecialchars($order['total_amount']); ?></p>
                                </div>
                                <div class="col-12 col-md-6">
                                    <p class="mb-1"><strong>Customer:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?> (<?php echo htmlspecialchars($order['email']); ?>)</p>
                                </div>
                            </div>
                        </div>
                        <h3 class="mb-3">Order Items</h3>
                        <?php if (count($order_items) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product ID</th>
                                            <th>Product Name</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['product_id']); ?></td>
                                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                                <td><?php echo htmlspecialchars($item['price']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No items found for this order.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
