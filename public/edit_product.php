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

// Initialize $row for form pre-fill fallback
$row = [];

// If the form is submitted using POST method
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get form data
    $product_id = filter_var($_POST["product_id"], FILTER_VALIDATE_INT);
    $name = sanitizeInput($_POST["name"]);
    $description = sanitizeInput($_POST["description"]);
    $price = sanitizeInput($_POST["price"]);
    $stock_quantity = sanitizeInput($_POST["stock_quantity"]);

    // Validate product ID
    if ($product_id === false) {
        $errors[] = "Invalid product ID.";
    }

    // Validate product name
    if (empty($name)) {
        $errors[] = "Product name is required.";
    } elseif (strlen($name) > 50) {
        $errors[] = "Product name cannot exceed 50 characters.";
    }

    // Validate description
    if (empty($description)) {
        $errors[] = "Product description is required.";
    }

    // Validate price
    if (empty($price)) {
        $errors[] = "Price is required.";
    } elseif (!filter_var($price, FILTER_VALIDATE_FLOAT) || $price <= 0) {
        $errors[] = "Price must be a valid number greater than 0.";
    }
    

    // Validate stock quantity
    if (empty($stock_quantity)) {
        $errors[] = "Stock quantity is required.";
    } elseif (!filter_var($stock_quantity, FILTER_VALIDATE_INT) || $stock_quantity <= 0) {
        $errors[] = "Stock quantity must be a positive integer.";
    }

    // If no validation errors, update the database
    if (empty($errors)) {
        // Convert values
        $price = floatval($price);
        $stock_quantity = intval($stock_quantity);
        $product_id = intval($product_id);

        // SQL query to update the product
        $sql = "UPDATE products 
                SET name=?, description=?, price=?, stock_quantity=? 
                WHERE product_id=?";

        // Prepare the statement
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssddi", $name, $description, $price, $stock_quantity, $product_id);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='success'>Product updated successfully. <a href='dashboard.php'>Back to Dashboard</a></div>";
        } else {
            echo "<div class='error'>Error updating product: " . mysqli_error($conn) . "</div>";
        }

        // Close the statement and connection
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        exit;
    } else {
        // If errors, retrieve old values again for re-rendering form
        $row = [
            'product_id' => $product_id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock_quantity' => $stock_quantity
        ];
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["product_id"])) {
    // Get and validate product ID from GET request
    $product_id = filter_var($_GET["product_id"], FILTER_VALIDATE_INT);

    if ($product_id === false) {
        die("Invalid product ID provided.");
    }

    // SQL query to retrieve the product
    $sql = "SELECT * FROM products WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // $row already populated
    } else {
        die("Product not found.");
    }

    // Close the statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>

    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="../assets/CSS/styles.css">


</head>
<body>
    <h1>Edit Product</h1>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="edit_product.php" method="POST">
        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['product_id'] ?? ''); ?>">

        <label for="name">Product Name:</label>
        <input type="text" id="name" name="name" 
               value="<?php echo htmlspecialchars($row['name'] ?? ''); ?>" required>

        <label for="description">Product Description:</label>
        <input type="text" id="description" name="description" 
               value="<?php echo htmlspecialchars($row['description'] ?? ''); ?>" required>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" min="0" 
               value="<?php echo htmlspecialchars($row['price'] ?? ''); ?>" required>

        <label for="stock_quantity">Stock Quantity:</label>
        <input type="number" id="stock_quantity" name="stock_quantity" min="0" 
               value="<?php echo htmlspecialchars($row['stock_quantity'] ?? ''); ?>" required>

        <input type="submit" value="Update Product">
    </form>
</body>
</html>