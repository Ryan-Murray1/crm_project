<?php
// Include the database connection file
include("../../db.php");

// Initialize error array
$errors = [];

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
    $customer_id = filter_var($_POST['customer_id'], FILTER_VALIDATE_INT);
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    $items = isset($_POST['items']) ? $_POST['items'] : [];

    if (!$order_id || !$customer_id || empty($status) || empty($items)) {
        $errors[] = "Missing or invalid order details.";
    }
} else {
    die("Invalid request method.");
}

// Validate order items
$clean_items = [];
foreach ($items as $item) {
    $product_id = filter_var($item['product_id'], FILTER_VALIDATE_INT);
    $quantity = filter_var($item['quantity'], FILTER_VALIDATE_INT);

    if (!$product_id || !$quantity || $quantity < 1) {
        $errors[] = "Invalid product or quantity in order items.";
    } else {
        $clean_items[] = [
            'product_id' => $product_id,
            'quantity' => $quantity
        ];
    }
}
if (empty($clean_items)) {
    $errors[] = "No valid order items provided.";
}

// Proceed with database update
if (empty($errors)) {
    mysqli_begin_transaction($conn);

    try {
        // Update order main info
        $sql = "UPDATE orders SET customer_id = ?, status = ? WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $customer_id, $status, $order_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update order.");
        }
        $stmt->close();

        // Delete existing order items
        $conn->query("DELETE FROM order_items WHERE order_id = $order_id");

        // Insert new order items and update stock
        $total_amount = 0;
        foreach ($clean_items as $item) {
            // Fetch price for product
            $prod_stmt = $conn->prepare("SELECT price FROM products WHERE product_id = ?");
            $prod_stmt->bind_param("i", $item['product_id']);
            $prod_stmt->execute();
            $prod_stmt->bind_result($price);
            $prod_stmt->fetch();
            $prod_stmt->close();

            // Insert order item
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $price);
            if (!$item_stmt->execute()) {
                throw new Exception("Failed to insert order item.");
            }
            $item_stmt->close();

            // Update stock (optional: you may want to adjust for difference in quantities)
            $conn->query("UPDATE products SET stock_quantity = stock_quantity - {$item['quantity']} WHERE product_id = {$item['product_id']}");

            $total_amount += $price * $item['quantity'];
        }

        // Update order total
        $stmt = $conn->prepare("UPDATE orders SET total_amount = ? WHERE order_id = ?");
        $stmt->bind_param("di", $total_amount, $order_id);
        $stmt->execute();
        $stmt->close();

        mysqli_commit($conn);

        // Redirect with success
        header("Location: view_orders.php?message=Order updated successfully");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $errors[] = "Failed to update order: " . $e->getMessage();
    }
}

// Display errors if any
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<p style='color:red'>" . htmlspecialchars($error) . "</p>";
    }
    echo "<p><a href='edit_orders.php?id=" . urlencode($order_id) . "'>Back to Edit Order</a></p>";
}
?>
