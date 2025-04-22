<?php
    // Include the database connection file
    include("../../db.php");

    // Helper function to show status messages
    function showMessage() {
        if (isset($_GET['status'])) {
            $status = $_GET['status'];
            if ($status === 'added') {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Product added successfully!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            } elseif ($status === 'updated') {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Product updated successfully!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            } elseif ($status === 'deleted') {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Product deleted successfully!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }
        }
        if (isset($_GET['message'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($_GET['message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        }
    }

    // Handle search/filter
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
    $search_sql = "";
    $params = [];
    $types = "";
    if ($search_term !== '') {
        // Search by name or product_id
        $search_sql = " WHERE name LIKE ? OR product_id = ?";
        $params[] = "%$search_term%";
        $params[] = is_numeric($search_term) ? intval($search_term) : 0;
        $types = "si";
    }

    // SQL query to select data from the products table
    $sql = "SELECT * FROM products" . $search_sql;
    $result = false;
    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        if ($search_term !== '') {
            $stmt->bind_param($types, ...$params);
        }
        // Execute the query
        $stmt->execute();
        // Get the result
        $result = $stmt->get_result();
        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing query: " . $conn->error;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
                        <h2 class="mb-0 text-accent">Product List</h2>
                        <a href="add_product.php" class="btn btn-custom">+ Add New Product</a>
                    </div>
                    <?php if (function_exists('showMessage')) showMessage(); ?>
                    <form method="get" action="view_products.php" class="row g-2 mb-4">
                        <div class="col-12 col-md-6 col-lg-4">
                            <input type="text" name="search" class="form-control" placeholder="Search by name or ID" value="<?php echo htmlspecialchars($search_term); ?>" />
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-custom">Search</button>
                        </div>
                        <?php if ($search_term !== ''): ?>
                            <div class="col-auto">
                                <a href="view_products.php" class="btn btn-link">Clear</a>
                            </div>
                        <?php endif; ?>
                    </form>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Stock Quantity</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                // Fetch and display each product from the result
                                while ($row = $result->fetch_assoc()) {
                                    $product_id = htmlspecialchars($row["product_id"]);
                                    echo "<tr>
                                            <td>" . $product_id . "</td>
                                            <td>" . htmlspecialchars($row["name"]) . "</td>
                                            <td>" . htmlspecialchars($row["description"]) . "</td>
                                            <td>" . htmlspecialchars($row["price"]) . "</td>
                                            <td>" . htmlspecialchars($row["stock_quantity"]) . "</td>
                                            <td>
                                                <a href='edit_product.php?product_id=" . $product_id . "' class='btn btn-sm btn-link'>Edit</a>
                                                <a href='delete_product.php?product_id=" . $product_id . "' class='btn btn-sm btn-link text-danger'>Delete</a>
                                            </td>
                                        </tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No products found.</p>
                    <?php endif; ?>
                    <div class="d-flex justify-content-end mt-4">
                        <a href="../dashboard.php" class="btn btn-link">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>