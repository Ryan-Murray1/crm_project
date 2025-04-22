<?php
// Include the database connection file
include("../../db.php");

// Initialize variables
$errors = [];
$orders = null;
$order_items = [];

// Validate order ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid order ID.");
}

$order_id = intval($_GET['id']);

// Fetch order details
$sql = "SELECT * FROM orders WHERE order_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && $result->num_rows > 0) {
    $orders = mysqli_fetch_assoc($result);
} else {
    die("Order not found.");
}
mysqli_stmt_close($stmt);

// Fetch order items
$sql = "SELECT oi.*, p.name AS product_name FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    $order_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Fetch all customers and products for dropdowns
$customers = [];
$result = mysqli_query($conn, "SELECT customer_id, first_name, last_name FROM customers");
if ($result) {
    $customers = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$products = [];
$result = mysqli_query($conn, "SELECT product_id, name, price, stock_quantity FROM products");
if ($result) {
    $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Order</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-3 text-center text-accent">Edit Order #<?php echo htmlspecialchars($order_id); ?></h2>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="update_order.php">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select name="customer_id" id="customer_id" required class="form-select">
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['customer_id']; ?>"
                                        <?php if ($customer['customer_id'] == $orders['customer_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" required class="form-select">
                                <?php
                                $statuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
                                foreach ($statuses as $status) {
                                    echo '<option value="' . htmlspecialchars($status) . '"';
                                    if ($orders['status'] === $status) echo ' selected';
                                    echo '>' . htmlspecialchars($status) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <h3 class="mt-4 mb-3">Order Items</h3>
                        <div id="order-items">
                            <?php foreach ($order_items as $idx => $item): ?>
                                <div class="row g-2 align-items-center mb-2 order-item-row">
                                    <div class="col-7 col-md-8">
                                        <select name="items[<?php echo $idx; ?>][product_id]" required class="form-select">
                                            <?php foreach ($products as $prod): ?>
                                                <option value="<?php echo $prod['product_id']; ?>"
                                                    <?php if ($prod['product_id'] == $item['product_id']) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($prod['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-5 col-md-4">
                                        <input type="number" name="items[<?php echo $idx; ?>][quantity]" min="1"
                                               value="<?php echo htmlspecialchars($item['quantity']); ?>" required class="form-control">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="btn btn-custom w-100 mt-3">Update Order</button>
                    </form>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="view_orders.php" class="btn btn-link">&larr; Back to Orders List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
