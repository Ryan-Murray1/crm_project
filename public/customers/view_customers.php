<?php
    // Include the database connection file
    include("../../db.php");

    // SQL query to retrieve data from customers table
    $sql = "SELECT * FROM customers";
    $result = $conn->query($sql);

    // Check if the query returned any results
    if ($result === false) {
        die("Error fetching customers: " . $conn->error);
    }

    // Helper function to display status messages
    function showMessage() {
        if (isset($_GET['status'])) {
            $status = $_GET['status'];
            if ($status === 'added') {
                echo '<div class="alert alert-success">Customer added successfully!</div>';
            } elseif ($status === 'updated') {
                echo '<div class="alert alert-success">Customer updated successfully!</div>';
            }
        }
        if (isset($_GET['message'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</div>';
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column min-vh-100">
        <div class="row justify-content-center flex-grow-1">
            <div class="col-12">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="mb-0 text-accent">Customer List</h2>
                        <a href="add_customer.php" class="btn btn-custom">+ Add New Customer</a>
                    </div>
                    <?php showMessage(); ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer ID</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Phone Number</th>
                                    <th>Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            // Fetch and display each customer from the result
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                            <td>" . htmlspecialchars($row["customer_id"]) . "</td>
                                            <td>" . htmlspecialchars($row["first_name"]) . "</td>
                                            <td>" . htmlspecialchars($row["last_name"]) . "</td>
                                            <td>" . htmlspecialchars($row["email"]) . "</td>
                                            <td>" . htmlspecialchars($row["phone_number"]) . "</td>
                                            <td>" . htmlspecialchars($row["address"]) . "</td>
                                            <td>
                                                <a class='btn btn-link p-0' href='edit_customer.php?customer_id=" . htmlspecialchars($row["customer_id"]) . "'>Edit</a> |
                                                <a class='btn btn-link text-danger p-0' href='delete_customer.php?customer_id=" . htmlspecialchars($row["customer_id"]) . "'>Delete</a>
                                            </td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>No customers found.</td></tr>";
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <a href="../dashboard.php" class="btn btn-link">&larr; Back to Dashboard</a>
                        <span class="text-muted small">&copy; 2025 CRM Project. All rights reserved.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php
        // Close the database connection
        $conn->close();
    ?>
</body>
</html>
