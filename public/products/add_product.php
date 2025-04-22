<?php
    // Include the database connection file
    include("../../db.php");

    // Initialize error array and form values
    $errors = array();
    $name = $description = $price = $stock_quantity = "";

    // Function to sanitize input
    function sanitize_input($data) {
        return htmlspecialchars(trim($data));
    }

    // Check if the form is submitted using POST method
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get form data
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        $price = sanitize_input($_POST['price']);
        $stock_quantity = sanitize_input($_POST['stock_quantity']);

        // Validate required fields
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

        // Validate stock quantity (must be a positive integer)
        if (empty($stock_quantity) && $stock_quantity !== "0") {
            $errors[] = "Stock quantity is required.";
        } elseif (!filter_var($stock_quantity, FILTER_VALIDATE_INT) || $stock_quantity < 0) {
            $errors[] = "Stock quantity must be a positive whole number.";
        }

        // Check for duplicate product name
        $check_sql = "SELECT COUNT(*) FROM products WHERE name = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $name);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();
        if ($count > 0) {
            $errors[] = "A product with this name already exists.";
        }

        // If there are no errors, insert into database
        if (empty($errors)) {
            // Convert price to float and stock to integer
            $price = floatval($price);
            $stock_quantity = intval($stock_quantity);

            // SQL query to insert data into products table
            $sql = "INSERT INTO products (name, description, price, stock_quantity)
                    VALUES (?, ?, ?, ?)";
            
            // Prepare the statement
            $stmt = mysqli_prepare($conn, $sql);

            // Bind parameters to the statement
            mysqli_stmt_bind_param($stmt, "ssdi", $name, $description, $price, $stock_quantity);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect after successful insertion
                header("Location: view_products.php?message=Product added successfully");
                exit;
            } else {
                // Database Error
                $errors[] = "Database error: " . htmlspecialchars($conn->error);
            }
            
            // Close the statement
            mysqli_stmt_close($stmt);
        }

        // Close the connection
        mysqli_close($conn);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-3 text-center text-accent">Add Product</h2>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form action="add_product.php" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" id="name" name="name" maxlength="255" required class="form-control" value="<?php echo htmlspecialchars($name); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Product Description</label>
                            <textarea id="description" name="description" maxlength="1000" required rows="3" class="form-control"><?php echo htmlspecialchars($description); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" required class="form-control" value="<?php echo htmlspecialchars($price); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" min="0" maxlength="11" required class="form-control" value="<?php echo htmlspecialchars($stock_quantity); ?>">
                        </div>
                        <button type="submit" class="btn btn-custom w-100">Add Product</button>
                    </form>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="view_products.php" class="btn btn-link">&larr; Back to Product List</a>
                        <a href="../dashboard.php" class="btn btn-link">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>