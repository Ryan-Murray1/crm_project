<?php
include("../../db.php");

$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

$orders = [];

if (!empty($search_term)) {
    // If there is a search term, fetch orders based on search term
    $sql = "SELECT o.order_id, o.order_date, o.status, o.total_amount, c.first_name, c.last_name
            FROM orders o
            JOIN customers c ON o.customer_id = c.customer_id
            WHERE o.order_id = ?
            OR c.first_name LIKE ?
            OR c.last_name LIKE ?
            OR o.status LIKE ?
            ORDER BY o.order_date DESC";

    $stmt = $conn->prepare($sql);

    $like = "%" . $search_term . "%";
    $order_id = is_numeric($search_term) ? intval($search_term) : 0;

    $stmt->bind_param("isss", $order_id, $like, $like, $like);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {
        $orders = $result->fetch_all(MYSQLI_ASSOC);
    }

    $stmt->close();
} else {
    // No search term, show all orders
    $sql = "SELECT o.order_id, o.order_date, o.status, o.total_amount, c.first_name, c.last_name
            FROM orders o
            JOIN customers c ON o.customer_id = c.customer_id
            ORDER BY o.order_date DESC";
    
    $result = $conn->query($sql);

    if ($result) {
        $orders = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="mb-0 text-accent">Orders</h2>
                        <div>
                            <a href="../dashboard.php" class="btn btn-link">&larr; Dashboard</a>
                            <a href="new_order.php" class="btn btn-custom">Add New Order</a>
                        </div>
                    </div>
                    <?php if (isset($_GET['message'])): ?>
                        <div class="alert alert-success mb-3">
                            <?php echo htmlspecialchars($_GET['message']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['status'])): ?>
                        <div class="alert alert-success mb-3">
                            <?php 
                                $status = $_GET['status'];
                                if ($status === 'added') echo 'Order added successfully!';
                                elseif ($status === 'updated') echo 'Order updated successfully!';
                                elseif ($status === 'deleted') echo 'Order deleted successfully!';
                            ?>
                        </div>
                    <?php endif; ?>
                    <?php if (count($orders) > 0): ?>
                        <form method="GET" action="view_orders.php" class="row g-2 mb-4 align-items-end">
                            <div class="col-md-6 col-lg-4">
                                <input type="text" name="search" class="form-control" placeholder="Search by Order ID, Customer Name, or Status" value="<?php echo htmlspecialchars($search_term); ?>" />
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-custom">Search</button>
                            </div>
                            <?php if (!empty($_GET['search'])): ?>
                                <div class="col-auto">
                                    <a href="view_orders.php" class="btn btn-link">Clear Search</a>
                                </div>
                            <?php endif; ?>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer Name</th>
                                        <th>Order Date</th>
                                        <th>Status</th>
                                        <th>Total Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                            <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                                            <td><?php echo htmlspecialchars($order['total_amount']); ?></td>
                                            <td>
                                                <a href="view_order_details.php?id=<?php echo urlencode($order['order_id']); ?>" class="btn btn-link">Details</a>
                                                <a href="edit_orders.php?id=<?php echo urlencode($order['order_id']); ?>" class="btn btn-link">Edit</a>
                                                <a href="delete_order.php?order_id=<?php echo urlencode($order['order_id']); ?>" class="btn btn-link text-danger" onclick="return confirm('Are you sure you want to delete this order? You will be asked to confirm.');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center mb-0">No orders found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
