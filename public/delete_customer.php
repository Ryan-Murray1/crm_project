<?php 
    // Include the database connection file
    include("../db.php");

    // Initialize errors array
    $errors = array();

    // Check if customer ID is provided in the URL
    if (!isset($_GET['customer_id'])) {
        $errors[] = "Invalid customer ID provided.";
    } else {
        // Sanitize input and check if it's a valid number
        $customer_id = filter_var($_GET['customer_id'], FILTER_SANITIZE_NUMBER_INT);

        if (!is_numeric($customer_id) || $customer_id <= 0) {
            $errors[] = "Customer ID must be a valid positive number.";
        }
    }

     // Confirmation check to prevent accidental deletions
     if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'true') {
        $errors[] = "Please confirm the deletion.";
    }

    // If there are no errors, proceed with database deletion
    if (empty($errors)) {
        // SQL query to delete data from customers table
        $sql = "DELETE FROM customers WHERE customer_id = ?";

        // Prepare the statement
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            // Bind parameters to the statement
            mysqli_stmt_bind_param($stmt, "i", $customer_id);

            // Execute the query and check if delete was successful
            if (mysqli_stmt_execute($stmt)) {
                // Redirect after successful deletion
                header("Location: dashboard.php?message=Customer deleted successfully");
                exit;
            } else {
                $errors[] = "Error deleting customer: " . mysqli_error($conn);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Customer</title>
        
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