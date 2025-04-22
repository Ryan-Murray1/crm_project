<?php
    // Include the database connection file
    include("../../db.php");

    // Helper function to sanitize input
    function sanitize_input($data) {
        return htmlspecialchars(trim($data));
    }

    $errors = array();

    // Validate and sanitize input
    $product_id = isset($_POST['product_id']) ? filter_var($_POST['product_id'], FILTER_VALIDATE_INT) : false;
    $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
    $description = isset($_POST['description']) ? sanitize_input($_POST['description']) : '';
    $price = isset($_POST['price']) ? sanitize_input($_POST['price']) : '';
    $stock_quantity = isset($_POST['stock_quantity']) ? sanitize_input($_POST['stock_quantity']) : '';

    if ($product_id === false) {
        $errors[] = "Invalid product ID.";
    }
    if (empty($name) || strlen($name) > 255) {
        $errors[] = "Product name is required and must not exceed 255 characters.";
    }
    if (empty($description) || strlen($description) > 1000) {
        $errors[] = "Product description is required and must not exceed 1000 characters.";
    }
    if (empty($price) || !preg_match('/^\d+(\.\d{1,2})?$/', $price) || $price <= 0) {
        $errors[] = "Price must be a valid number greater than 0 with up to 2 decimal places.";
    }
    if ($stock_quantity === "" || !filter_var($stock_quantity, FILTER_VALIDATE_INT) || $stock_quantity < 0) {
        $errors[] = "Stock quantity must be a positive whole number or zero.";
    }

    if (empty($errors)) {
        // Prepare the statement
        $sql = "UPDATE products SET name=?, description=?, price=?, stock_quantity=? WHERE product_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            $price = floatval($price);
            $stock_quantity = intval($stock_quantity);
            mysqli_stmt_bind_param($stmt, "ssdii", $name, $description, $price, $stock_quantity, $product_id);
            if (mysqli_stmt_execute($stmt)) {
                // Redirect after successful update
                header("Location: view_products.php?message=Product updated successfully");
                exit;
            } else {
                $errors[] = "Database error: " . htmlspecialchars(mysqli_error($conn));
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Error preparing statement: " . htmlspecialchars(mysqli_error($conn));
        }
        mysqli_close($conn);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Product</title>
    <link rel="stylesheet" href="../assets/CSS/styles.css">
</head>
<body>
    <h2>Update Product</h2>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="edit_product.php?product_id=<?php echo urlencode($product_id); ?>">Back to Edit Product</a>
        </div>
    <?php endif; ?>
</body>
</html>