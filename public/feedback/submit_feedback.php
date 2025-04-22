<?php
    // Include the database connection file
    include("../../db.php");

    // Initialize errors array
    $errors = array();

    // Define variables at the top to avoid undefined variable warnings
    $customer_id = null;
    $product_id = $rating = $comments = null;

    // Set customer_id from POST or GET
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $customer_id = filter_var($_POST['customer_id'], FILTER_VALIDATE_INT);
        $product_id = isset($_POST['product_id']) && $_POST['product_id'] !== '' ? filter_var($_POST['product_id'], FILTER_VALIDATE_INT) : null;
        $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT);
        $comments = htmlspecialchars($_POST['comments']);
    } elseif (isset($_GET['customer_id'])) {
        $customer_id = filter_var($_GET['customer_id'], FILTER_VALIDATE_INT);
    }

    // Validate customer ID
    if ($customer_id === false || $customer_id <= 0) {
        $errors[] = "Invalid customer ID.";
    }

    // Validate product ID if provided (optional)
    if ($product_id !== null && ($product_id === false || $product_id <= 0)) {
        $errors[] = "Invalid product ID.";
    }

    // Validate rating
    if ($rating === false || $rating <= 0 || $rating > 5) {
        $errors[] = "Rating must be between 1 and 5.";
    }

    // Validate comments
    if (isset($comments) && empty($comments)) {
        $errors[] = "Comments cannot be empty.";
    }

    // Allow feedback for ordered products only
    if ($product_id !== null && $customer_id !== null && empty($errors)) {
        // 1. Check if the product has been ordered by the customer
        $sql = "SELECT 1 FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                WHERE o.customer_id = ? AND oi.product_id = ? AND o.status = 'Completed'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $customer_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "You cannot provide feedback for this product as it has not been ordered.";
        }
        $stmt->close();
    }

    // If there are no errors, proceed with database insertion
    if (empty($errors) && $_SERVER["REQUEST_METHOD"] == "POST") {
        // Prepare the SQL statement (allowing NULL for product_id)
        $stmt = $conn->prepare("INSERT INTO feedback (customer_id, product_id, rating, comments) VALUES (?, ?, ?, ?)");
        if ($product_id === null) {
            $null = null;
            $stmt->bind_param("iiis", $customer_id, $null, $rating, $comments);
        } else {
            $stmt->bind_param("iiis", $customer_id, $product_id, $rating, $comments);
        }

        if ($stmt->execute()) {
            header("Location: view_feedback.php?message=Feedback submitted successfully");
            exit;
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }

        $stmt->close();
    }

    // Fetch customers for dropdowns
    $customers = mysqli_query($conn, "SELECT customer_id, first_name, last_name FROM customers");
    // Fetch products for the selected customer
    $products = [];
    if ($customer_id) {
        $sql = "SELECT DISTINCT oi.product_id, p.name
                FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN products p ON oi.product_id = p.product_id
                WHERE o.customer_id = $customer_id AND o.status = 'Completed'";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $products[] = $row;
            }
        }
    } else {
        // If no customer is selected, show an empty array (no products)
        $products = [];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-3 text-center text-accent">Submit Feedback</h2>
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <!-- Customer selection form (GET) -->
                    <form method="GET" action="submit_feedback.php" id="customerForm" class="mb-4">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select name="customer_id" id="customer_id" required class="form-select" onchange="document.getElementById('customerForm').submit();">
                                <option value="">Select a customer</option>
                                <?php
                                mysqli_data_seek($customers, 0);
                                while ($row = mysqli_fetch_assoc($customers)): ?>
                                    <option value="<?php echo $row['customer_id']; ?>" <?php if (isset($customer_id) && $customer_id == $row['customer_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </form>
                    <!-- Feedback submission form (POST) -->
                    <form method="POST" action="submit_feedback.php">
                        <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer_id); ?>">
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product</label>
                            <select name="product_id" id="product_id" class="form-select">
                                <option value="">Select Product</option>
                                <?php if (empty($products)): ?>
                                    <option value="" disabled>No products available for this customer</option>
                                <?php endif; ?>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['product_id']; ?>">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating (1-5)</label>
                            <select name="rating" id="rating" required class="form-select">
                                <option value="">Select rating</option>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comments" class="form-label">Comments</label>
                            <textarea name="comments" id="comments" rows="5" required class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-custom w-100">Submit Feedback</button>
                    </form>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="../dashboard.php" class="btn btn-link">&larr; Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
