<?php
include("../../db.php");

// Initialize variables
$errors = [];
$success = '';
$orders = [];

try {
    // Fetch orders with customer details and total amounts
    $sql = "SELECT o.order_id, 
                   o.order_date, 
                   o.status, 
                   o.total_amount,
                   c.first_name, 
                   c.last_name
            FROM orders o
            JOIN customers c ON o.customer_id = c.customer_id
            ORDER BY o.order_date DESC";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            error_log("Result Error: " . mysqli_error($conn));
            $errors[] = "There was a problem fetching orders. Please try again later.";
        }

        mysqli_stmt_close($stmt);
    } else {
        error_log("Prepare Error: " . mysqli_error($conn));
        $errors[] = "There was a problem preparing the query.";
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    $errors[] = "Unexpected error occurred.";
}
?>

<?php
include("../../db.php");

// Initialize variables
$errors = [];
$success = '';
$orders = [];

// Use try-catch style with error logging for better handling
try {
    $sql = "SELECT o.order_id, 
                   o.order_date, 
                   o.status, 
                   o.total_amount,
                   c.first_name, 
                   c.last_name
            FROM orders o
            JOIN customers c ON o.customer_id = c.customer_id
            ORDER BY o.order_date DESC";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            error_log("Result Error: " . mysqli_error($conn));
            $errors[] = "There was a problem fetching orders. Please try again later.";
        }

        mysqli_stmt_close($stmt);
    } else {
        error_log("Prepare Error: " . mysqli_error($conn));
        $errors[] = "There was a problem preparing the query.";
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    $errors[] = "Unexpected error occurred.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders</title>

    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="../../assets/CSS/styles.css">

</head>
<body>
    <header>
        <h1>Order List</h1>
        <a href="../dashboard.php" class="back-link">&larr; Back to Dashboard</a>
        <a href="new_order.php" class="btn btn-primary">Create New Order</a>
    </header>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success">
            <p><?php echo htmlspecialchars($success); ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($orders)): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
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
                    <?php foreach ($orders as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($row['order_date']))); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>$<?php echo htmlspecialchars(number_format($row['total_amount'], 2)); ?></td>
                            <td>
                                <a href="view_order_details.php?id=<?php echo urlencode($row['order_id']); ?>" 
                                   class="btn btn-small btn-info">Details</a>
                                <a href="update_order_status.php?id=<?php echo urlencode($row['order_id']); ?>" 
                                   class="btn btn-small btn-secondary">Update Status</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="no-data">No orders found.</p>
    <?php endif; ?>
</body>
</html>




