<?php 
    // Include the database connection file
    include("../../db.php");

    // Initialize errors array
    $errors = array();

    // Validate customer ID from URL
    if (!isset($_GET['customer_id'])) {
        $errors[] = "Invalid customer ID provided.";
    } else {
        // Use FILTER_VALIDATE_INT for strict validation
        $customer_id = filter_var($_GET['customer_id'], FILTER_VALIDATE_INT);
        if ($customer_id === false || $customer_id <= 0) {
            $errors[] = "Customer ID must be a valid positive number.";
        }
    }

    // Confirmation check to prevent accidental deletions
    $confirmed = (isset($_GET['confirm']) && $_GET['confirm'] === 'true');
    if (!$confirmed) {
        $errors[] = "Please confirm the deletion.";
    }

    // Fetch customer details for confirmation display
    $customer_details = null;
    if (isset($customer_id) && $customer_id !== false && $customer_id > 0) {
        $stmt = mysqli_prepare($conn, "SELECT first_name, last_name, email, phone_number, address FROM customers WHERE customer_id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $customer_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($result && $row = mysqli_fetch_assoc($result)) {
                $customer_details = $row;
            }
            mysqli_stmt_close($stmt);
        }
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
                header("Location: ../dashboard.php?message=Customer deleted successfully");
                exit;
            } else {
                $errors[] = "Error deleting customer: " . htmlspecialchars(mysqli_error($conn));
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Error preparing statement: " . htmlspecialchars(mysqli_error($conn));
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
    
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="../assets/CSS/styles.css">

</head>
<body class="bg-light no-card-hover">

    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="card card-custom bg-secondary-custom shadow-sm p-4">
            <h2 class="mb-3 text-center text-accent">Delete Customer</h2>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($customer_id) && $customer_id !== false && $customer_id > 0 && !$confirmed): ?>
                <div class="text-center mb-4 p-4">
                    <p class="mb-2 text-accent fw-semibold">
                        Are you sure you want to delete this customer? This action cannot be undone.
                    </p>
                    <?php if ($customer_details): ?>
                        <div class="mb-3">
                            <div class="card bg-light border">
                                <div class="card-body p-3">
                                    <h5 class="card-title mb-2 text-primary"><?php echo htmlspecialchars($customer_details['first_name'] . ' ' . $customer_details['last_name']); ?></h5>
                                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($customer_details['email']); ?></p>
                                    <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($customer_details['phone_number']); ?></p>
                                    <p class="mb-0"><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($customer_details['address'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <a href="delete_customer.php?customer_id=<?php echo urlencode($customer_id); ?>&confirm=true"
                       class="btn btn-danger w-100"
                       onclick="return confirm('Are you sure you want to delete this customer? This action cannot be undone.');">
                        Confirm Deletion
                    </a>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between">
                <a href="view_customers.php" class="btn btn-link">&larr; Back to Customer List</a>
                <a href="../dashboard.php" class="btn btn-link">Dashboard</a>
            </div>
        </div>
    </div>

    <!-- Add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
