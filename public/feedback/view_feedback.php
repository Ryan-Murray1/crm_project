<?php
// Connect to the database
include("../../db.php");

// Initialize errors array
$errors = array();

// Get filters from the URL (via GET request)
// If no value is passed, set it as an empty string
$filter_name = isset($_GET['customer_name']) ? $_GET['customer_name'] : '';
$filter_date = isset($_GET['created_at']) ? $_GET['created_at'] : '';
$filter_rating = isset($_GET['rating']) ? $_GET['rating'] : '';

// Start building the base SQL query
// We're joining the feedback table with customers and products to get names
$sql = "SELECT 
            f.feedback_id, 
            CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
            p.name AS product_name,
            f.rating,
            f.comments, 
            f.created_at
        FROM feedback f
        JOIN customers c ON f.customer_id = c.customer_id
        LEFT JOIN products p ON f.product_id = p.product_id
        WHERE 1 = 1"; // This makes adding more conditions easier (we just keep adding AND ...)

// Dynamically add conditions based on which filters are used
if (!empty($filter_name)) {
    // Adds a condition to match the full name using LIKE for partial matches
    $sql .= " AND CONCAT(c.first_name, ' ', c.last_name) LIKE ?";
}
if (!empty($filter_date)) {
    // Filter results by exact date match (format: YYYY-MM-DD)
    $sql .= " AND DATE(f.created_at) = ?";
}
if (!empty($filter_rating)) {
    // Only show ratings greater than or equal to the selected rating
    $sql .= " AND f.rating >= ?";
}

// Order results by newest feedback first
$sql .= " ORDER BY f.created_at DESC";

// Prepare the SQL statement for execution
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    // Bind the correct parameters depending on which filters are set
    if (!empty($filter_name) && !empty($filter_date) && !empty($filter_rating)) {
        $name_param = "%$filter_name%"; // Add wildcards for LIKE
        $date_param = $filter_date;
        $rating_param = $filter_rating;
        mysqli_stmt_bind_param($stmt, "ssi", $name_param, $date_param, $rating_param);
    } elseif (!empty($filter_name) && !empty($filter_date)) {
        $name_param = "%$filter_name%";
        $date_param = $filter_date;
        mysqli_stmt_bind_param($stmt, "ss", $name_param, $date_param);
    } elseif (!empty($filter_name) && !empty($filter_rating)) {
        $name_param = "%$filter_name%";
        $rating_param = $filter_rating;
        mysqli_stmt_bind_param($stmt, "si", $name_param, $rating_param);
    } elseif (!empty($filter_date) && !empty($filter_rating)) {
        $date_param = $filter_date;
        $rating_param = $filter_rating;
        mysqli_stmt_bind_param($stmt, "si", $date_param, $rating_param);
    } elseif (!empty($filter_name)) {
        $name_param = "%$filter_name%";
        mysqli_stmt_bind_param($stmt, "s", $name_param);
    } elseif (!empty($filter_date)) {
        $date_param = $filter_date;
        mysqli_stmt_bind_param($stmt, "s", $date_param);
    } elseif (!empty($filter_rating)) {
        $rating_param = $filter_rating;
        mysqli_stmt_bind_param($stmt, "i", $rating_param);
    }

    // Run the query
    mysqli_stmt_execute($stmt);

    // Get the result set from the executed statement
    $result = mysqli_stmt_get_result($stmt);
} else {
    // If preparing the statement failed, show an error
    $errors[] = "Database error: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback</title>
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
                        <h2 class="mb-0 text-accent">Customer Feedback</h2>
                    </div>
                    <?php if (isset($_GET['message'])): ?>
                        <div class="alert alert-success mb-3">
                            <?php echo htmlspecialchars($_GET['message']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mb-3">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <form method="get" class="row g-2 mb-4 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Customer Name</label>
                            <input type="text" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($filter_name); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Created Date</label>
                            <input type="date" name="created_at" class="form-control" value="<?php echo htmlspecialchars($filter_date); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Minimum Rating</label>
                            <input type="number" name="rating" min="1" max="5" class="form-control" value="<?php echo htmlspecialchars($filter_rating); ?>">
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-custom">Filter</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Product</th>
                                    <th>Rating</th>
                                    <th>Comments</th>
                                    <th>Submitted At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (isset($result) && mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['feedback_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['product_name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($row['rating']); ?></td>
                                        <td><?php echo htmlspecialchars($row['comments']); ?></td>
                                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                        <td>
                                            <a class="btn btn-link p-0" href="edit_feedback.php?id=<?php echo $row['feedback_id']; ?>">Edit</a> |
                                            <a class="btn btn-link text-danger p-0" href="delete_feedback.php?id=<?php echo $row['feedback_id']; ?>">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">No feedback found.</td></tr>
                            <?php endif; ?>
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
</body>
</html>
