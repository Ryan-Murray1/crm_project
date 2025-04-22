<?php
// Include the database connection file
include("../../db.php");

// Initialize errors array
$errors = array();

// Function to sanitize user input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input));
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
    } elseif (strlen($name) > 255) {
        $errors[] = "Product name cannot exceed 255 characters.";
    }

    // Validate description
    if (empty($description)) {
        $errors[] = "Product description is required.";
    } elseif (strlen($description) > 1000) {
        $errors[] = "Product description cannot exceed 1000 characters.";
    }

    // Validate price (must be a positive number with up to 2 decimal places)
    if (empty($price)) {
        $errors[] = "Price is required.";
    } elseif (!preg_match("/^\d+(\.\d{1,2})?$/", $price)) {
        $errors[] = "Price must be a valid number with up to 2 decimal places.";
    } elseif ($price <= 0) {
        $errors[] = "Price must be greater than 0.";
    }

    // Validate stock quantity (must be a positive integer or zero)
    if ($stock_quantity === "" || $stock_quantity === null) {
        $errors[] = "Stock quantity is required.";
    } elseif (!filter_var($stock_quantity, FILTER_VALIDATE_INT) || $stock_quantity < 0) {
        $errors[] = "Stock quantity must be a positive whole number or zero.";
    }

    // Check for duplicate product name (excluding current product)
    $check_sql = "SELECT COUNT(*) FROM products WHERE name = ? AND product_id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $name, $product_id);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();
    if ($count > 0) {
        $errors[] = "A product with this name already exists.";
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
            // Redirect after successful update
            header("Location: view_products.php?message=Product updated successfully");
            exit;
        } else {
            $errors[] = "Error updating product: " . htmlspecialchars(mysqli_error($conn));
        }

        // Close the statement and connection
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-3 text-center text-accent">Edit Product</h2>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form action="edit_product.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['product_id'] ?? ''); ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" id="name" name="name" maxlength="255" required class="form-control" value="<?php echo htmlspecialchars($row['name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Product Description</label>
                            <textarea id="description" name="description" maxlength="1000" required rows="3" class="form-control"><?php echo htmlspecialchars($row['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" required class="form-control" value="<?php echo htmlspecialchars($row['price'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" min="0" maxlength="11" required class="form-control" value="<?php echo htmlspecialchars($row['stock_quantity'] ?? ''); ?>">
                        </div>
                        <button type="submit" class="btn btn-custom w-100">Update Product</button>
                    </form>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="view_products.php" class="btn btn-link">&larr; Back to Product List</a>
                        <a href="dashboard.php" class="btn btn-link">Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>