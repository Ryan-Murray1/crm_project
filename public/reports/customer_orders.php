<?php

    include("../../db.php");

    // Fetch all customers for the dropdown
    $customers = [];
    $result = $conn->query("SELECT customer_id, first_name, last_name FROM customers ORDER BY last_name, first_name");
    if ($result) {
        $customers = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get selected customer (if any)
    $selected_customer = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : null;

    // Build SQL for orders
    $sql = "SELECT o.*, c.first_name, c.last_name from orders o
            JOIN customers c ON o.customer_id = c.customer_id";

    // Add WHERE clause if a customer is selected
    if ($selected_customer) {
        $sql .= " WHERE o.customer_id = $selected_customer";
    }

    // Sort by order date
    $sql .= " ORDER BY o.order_date DESC";

    // Execute query
    $result = $conn->query($sql);
    $orders = [];
    if ($result) {
        $orders = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Group orders by customer
    $grouped_orders = [];
    foreach ($orders as $order) {
        $customer_id = $order['customer_id'];
        if (!isset($grouped_orders[$customer_id])) {
            $grouped_orders[$customer_id] = [
            'customer_name' => $order['first_name'] . ' ' . $order['last_name'],
            'orders' => []
        ];
    }
    $grouped_orders[$customer_id]['orders'][] = $order;
}   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders by Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-4 text-accent">Orders by Customer</h2>
                    <form method="GET" class="row g-2 align-items-end mb-4">
                        <div class="col-12 col-md-6 col-lg-4">
                            <label for="customer_id" class="form-label">Filter by Customer:</label>
                            <select name="customer_id" id="customer_id" class="form-select" onchange="this.form.submit()">
                                <option value="">All customers</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['customer_id']; ?>" <?php if ($selected_customer == $customer['customer_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                    <?php if (empty($grouped_orders)): ?>
                        <div class="alert alert-info">No orders found.</div>
                    <?php else: ?>
                        <?php foreach ($grouped_orders as $customer_id => $data): ?>
                            <h3 class="mt-4 mb-3 text-primary-emphasis"><?php echo htmlspecialchars($data['customer_name']); ?></h3>
                            <div class="table-responsive mb-4">
                                <table class="table table-striped align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['orders'] as $order): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                                <td><?php echo htmlspecialchars($order['status']); ?></td>
                                                <td><?php echo htmlspecialchars($order['total_amount']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="d-flex justify-content-end mt-4">
                        <a href="../dashboard.php" class="btn btn-link">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>