<?php
    // Include the database connection file
    include("../db.php");

    // Initialize errors array
    $errors = array();

    // Check if product ID is provided in the POST request
    if (!isset($_POST['product_id'])) {
        $errors[] = "No product ID provided.";
    } else {
        // Sanitize input and check if it's a valid number
        $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);

        if (!is_numeric($product_id) || $product_id <= 0) {
            $errors[] = "Product ID must be a valid positive number.";
        }
    }

    // Confirmation check to prevent accidental deletions
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
                header("Location: dashboard.php?message=Product deleted successfully");
                exit;
            } else {
                $errors[] = "Error deleting product: " . mysqli_error($conn);
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Error preparing statement: " . mysqli_error($conn);
        }
    }

    // Close the database connection
    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Product</title>
        
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
