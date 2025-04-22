<?php
// Include the database connection file
include("../../db.php");

// Initialize errors array
$errors = array();
$show_confirm = false;
$product_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate product ID
    if (!isset($_POST['product_id'])) {
        $errors[] = "No product ID provided.";
    } else {
        $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
        if ($product_id === false || $product_id <= 0) {
            $errors[] = "Product ID must be a valid positive number.";
        }
    }
    // Confirmation check
    if (!isset($_POST['confirm']) || $_POST['confirm'] !== 'true') {
        $errors[] = "Please confirm the deletion.";
    }

    // If there are no errors, proceed with database deletion
    if (empty($errors)) {
        // SQL query to delete data from products table
        $sql = "DELETE FROM products WHERE product_id = ?";

        // Prepare the statement
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            // Bind parameters to the statement
            mysqli_stmt_bind_param($stmt, "i", $product_id);

            // Execute the query and check if delete was successful
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to product list with status message
                header("Location: view_products.php?message=Product deleted successfully");
                exit;
            } else {
                $errors[] = "Error deleting product: " . htmlspecialchars(mysqli_error($conn));
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Error preparing statement: " . htmlspecialchars(mysqli_error($conn));
        }
    }
    mysqli_close($conn);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['product_id'])) {
    // Validate product ID from GET
    $product_id = filter_var($_GET['product_id'], FILTER_VALIDATE_INT);
    if ($product_id === false || $product_id <= 0) {
        $errors[] = "Invalid product ID provided.";
    } else {
        // Fetch product info for confirmation display
        $sql = "SELECT name FROM products WHERE product_id = ?";

        // Prepare the statement
        $stmt = mysqli_prepare($conn, $sql);

        // Bind parameters to the statement
        mysqli_stmt_bind_param($stmt, "i", $product_id);

        // Execute the query
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($product = mysqli_fetch_assoc($result)) {
            $product_name = $product['name'];
            $show_confirm = true;
        } else {
            $errors[] = "Product not found.";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
} else {
    $errors[] = "Invalid request.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light no-card-hover">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-3 text-center text-accent">Delete Product</h2>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mb-3">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                            <div class="text-center mt-3">
                                <a href="view_products.php" class="btn btn-link">&larr; Back to Product List</a>
                                <a href="../dashboard.php" class="btn btn-link">Dashboard</a>
                            </div>
                        </div>
                    <?php elseif ($show_confirm): ?>
                        <div class="alert alert-warning mb-3">
                            <p class="mb-2">Are you sure you want to delete the product <strong><?php echo htmlspecialchars($product_name); ?></strong> (ID: <?php echo htmlspecialchars($product_id); ?>)?</p>
                            <form action="delete_product.php" method="POST" class="d-flex flex-column gap-2">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                                <input type="hidden" name="confirm" value="true">
                                <button type="submit" class="btn btn-danger w-100">Yes, Delete Product</button>
                                <a href="view_products.php" class="btn btn-link">Cancel</a>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
