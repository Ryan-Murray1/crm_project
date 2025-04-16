<?php
// Connect to the database
include("../db.php");

// Initialize errors array
$errors = array();

// Get filters from the URL (via GET request)
// If no value is passed, set it as an empty string
$filter_name = isset($_GET['customer_name']) ? $_GET['customer_name'] : '';
$filter_date = isset($_GET['created_at']) ? $_GET['created_at'] : '';
$filter_rating = isset($_GET['rating']) ? $_GET['rating'] : '';

// Start building the base SQL query
// We're joining the feedback table with customers to get names
$sql = "SELECT 
            f.feedback_id, 
            CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
            f.rating,
            f.comments, 
            f.created_at
        FROM feedback f
        JOIN customers c ON f.customer_id = c.customer_id
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

<!-- HTML output begins -->
<!DOCTYPE html>
<html>
<head>
    <title>Customer Feedback</title>

    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="../assets/CSS/styles.css">
 
    
</head>
<body>
    <h1>Customer Feedback</h1>

    <!-- Filter form -->
    <form method="get">
        <label>Customer Name:</label>
        <input type="text" name="customer_name" value="<?php echo htmlspecialchars($filter_name); ?>">

        <label>Created Date:</label>
        <input type="date" name="created_at" value="<?php echo htmlspecialchars($filter_date); ?>">

        <label>Minimum Rating:</label>
        <input type="number" name="rating" min="1" max="5" value="<?php echo htmlspecialchars($filter_rating); ?>">

        <button type="submit">Filter</button>
    </form>

    <!-- Feedback table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Rating</th>
                <th>Comments</th>
                <th>Submitted At</th>
            </tr>
        </thead>
        <tbody>
        <!-- Check if we have results -->
        <?php if (isset($result) && mysqli_num_rows($result) > 0): ?>
            <!-- Loop through each row and show it -->
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['feedback_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['rating']); ?></td>
                    <td><?php echo htmlspecialchars($row['comments']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- Show message if no results found -->
            <tr><td colspan="5">No feedback found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
