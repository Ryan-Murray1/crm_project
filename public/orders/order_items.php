<?php
    // Include the database connection file
    include("../../db.php");

    // Initialize errors array
    $errors = array();

    // Function to sanitize user input
    function sanitizeInput($input) {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input);
        return $input;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize and valaidate input data
        $order_id = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
        $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
        $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    }

    // Validate order ID
    if ($order_id === false || $order_id <= 0) {
        $errors[] = "Invalid order ID.";
    }

    // Validate product ID
    if ($product_id === false || $product_id <= 0) {
        $errors[] = "Invalid product ID.";
    }

    // Validate quantity
    if ($quantity === false || $quantity <= 0) {
        $errors[] = "Quantity must be a positive number.";
    }

    // Validate price
    if ($price === false || $price <= 0) {
        $errors[] = "Price must be a positive number.";
    }

    // If there are no errors, proceed with database insertion
    if (empty($errors)) {
        // SQL query to insert data into order_items table
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)";

        // Prepare the statement
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            // Bind parameters to the statement
            mysqli_stmt_bind_param($stmt, "iiid", $order_id, $product_id, $quantity, $price);

            if (mysqli_stmt_execute($stmt)) {
                // Update order total
                $update_order_total_sql = "UPDATE orders SET total_amount = total_amount + (? * ?) WHERE order_id = ?";
                $update_stmt = mysqli_prepare($conn, $update_order_total_sql);
                if ($update_stmt) {
                    mysqli_stmt_bind_param($update_stmt, "dii", $price, $quantity, $order_id);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                }

                // Update product stock
                $update_stock_sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
                $stock_stmt = mysqli_prepare($conn, $update_stock_sql);
                if ($stock_stmt) {
                    mysqli_stmt_bind_param($stock_stmt, "ii", $quantity, $product_id);
                    mysqli_stmt_execute($stock_stmt);
                    mysqli_stmt_close($stock_stmt);
                }

                // Redirect to view_orders.php with status message for consistency
                header("Location: view_orders.php?message=Order item added successfully");
                exit;
            } else {
                $errors[] = "Database error: " . mysqli_error($conn);
            }
    
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Failed to prepare SQL statement.";
        }
    }
    
    mysqli_close($conn);
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Items</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-3 text-center text-accent">Order Items</h2>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="dashboard.php" class="btn btn-link">&larr; Back to Dashboard</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>