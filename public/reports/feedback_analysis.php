<?php
    // Database connection
    include("../../db.php");

    $min_rating = isset($_GET['min_rating']) ? (float) $_GET['min_rating'] : 0;

    // Feedback analysis report to display average product ratings and most-reviewed products
    $sql = "SELECT 
                p.name AS product_name,
                COUNT(f.feedback_id) AS num_reviews,
                AVG(f.rating) AS avg_rating
            FROM products p
            LEFT JOIN feedback f ON p.product_id = f.product_id
            GROUP BY p.product_id
            HAVING (avg_rating >= $min_rating OR avg_rating IS NULL)
            ORDER BY avg_rating DESC, num_reviews DESC"; 

    // Execute the query
    $result = $conn->query($sql);
    $feedback_data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    // Fetch recent feedback entries
    $SQL = "SELECT 
                f.feedback_id,
                p.name AS product_name,
                CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
                f.rating,
                f.comments,
                f.created_at
            FROM feedback f
            JOIN products p ON f.product_id = p.product_id
            JOIN customers c ON f.customer_id = c.customer_id
            ORDER BY f.created_at DESC
            LIMIT 10";
    
    // Execute the query
    $result = $conn->query($SQL);
    $recent_feedback = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Analysis Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-4 text-accent">Feedback Analysis Report</h2>
                    <form method="GET" class="row g-2 align-items-end mb-4">
                        <div class="col-12 col-md-6 col-lg-4">
                            <label for="min_rating" class="form-label">Minimum Rating:</label>
                            <select name="min_rating" id="min_rating" class="form-select">
                                <option value="0" <?= $min_rating == 0 ? 'selected' : '' ?>>All</option>
                                <option value="1" <?= $min_rating == 1 ? 'selected' : '' ?>>1</option>
                                <option value="2" <?= $min_rating == 2 ? 'selected' : '' ?>>2</option>
                                <option value="3" <?= $min_rating == 3 ? 'selected' : '' ?>>3</option>
                                <option value="4" <?= $min_rating == 4 ? 'selected' : '' ?>>4</option>
                                <option value="5" <?= $min_rating == 5 ? 'selected' : '' ?>>5</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-custom">Filter</button>
                        </div>
                    </form>
                    <div class="table-responsive mb-5">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Number of Reviews</th>
                                    <th>Average Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($feedback_data as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                                        <td><?= htmlspecialchars($row['num_reviews']) ?></td>
                                        <td><?= round($row['avg_rating'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <h3 class="mb-3 text-primary-emphasis">Recent Feedback</h3>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Customer Name</th>
                                    <th>Rating</th>
                                    <th>Comments</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_feedback as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                        <td><?= $row['rating'] ?></td>
                                        <td><?= htmlspecialchars($row['comments']) ?></td>
                                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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
