<?php
    // Include the database connection file
    include("../db.php");

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
                header("Location: dashboard.php?message=Order item added successfully");
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
        
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="../assets/CSS/styles.css">

</head>
<body>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
            <p><a href="dashboard.php">Back to Dashboard</a></p>
        </div>
    <?php endif; ?>
</body>
</html>