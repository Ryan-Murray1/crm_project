<?php
    // Include the database connection file
    include("../../db.php");

    // Check if the customer ID is set
    if (!isset($_GET["customer_id"])) {
        echo "No customer selected.";
        exit;
    }

    // Get the customer ID from the URL
    $customer_id = $_GET["customer_id"];

    // SQL query to retrieve data from customers table
    $sql = "SELECT * FROM customers WHERE customer_id = ?";

    // Prepare the statement
    $stmt = mysqli_prepare($conn, $sql);

    // Bind parameters to the statement
    mysqli_stmt_bind_param($stmt, "i", $customer_id);

    // Execute the statement
    mysqli_stmt_execute($stmt);

    // Get the result
    $result = mysqli_stmt_get_result($stmt);

    // Fetch the row
    $row = mysqli_fetch_assoc($result);

    if (isset($stmt)) {
        // Close the statement
        mysqli_stmt_close($stmt);
        // Close the connection
        mysqli_close($conn);
    }

    // Check if customer was found
    if (!$row) {
        echo "Customer not found.";
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-3 text-center text-accent">Edit Customer</h2>
                    <form method="POST" action="update_customer.php">
                        <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($row['customer_id']); ?>">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" name="first_name" maxlength="255" required class="form-control" value="<?php echo htmlspecialchars($row['first_name']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" maxlength="255" required class="form-control" value="<?php echo htmlspecialchars($row['last_name']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" maxlength="255" required class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" id="phone_number" name="phone_number" maxlength="15" required class="form-control" value="<?php echo htmlspecialchars($row['phone_number']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea id="address" name="address" rows="3" required class="form-control"><?php echo htmlspecialchars($row['address']); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-custom w-100">Update</button>
                    </form>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="view_customers.php" class="btn btn-link">&larr; Back to Customer List</a>
                        <a href="../dashboard.php" class="btn btn-link">Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>