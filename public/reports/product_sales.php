<?php
    // Include database connection
    include("../../db.php");

    // Get the start date and end date from the form (or set default if not set)
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

    //Top selling products query
    $sql_top_products = "SELECT
         p.name AS product_name,
         SUM(oi.quantity) AS total_sold
         FROM order_items oi
         JOIN products p ON oi.product_id = p.product_id
         JOIN orders o ON oi.order_id = o.order_id
         ";

    // Apply date range filter if provided
    if ($start_date && $end_date) {
        $sql_top_products .= " WHERE o.order_date BETWEEN '$start_date' AND '$end_date'";
    }

    $sql_top_products .= "
        GROUP BY oi.product_id, p.name
        ORDER BY total_sold DESC
        LIMIT 10
    ";

    // Execute query
    $result_top_products = $conn->query($sql_top_products);
    $top_products = $result_top_products ? $result_top_products->fetch_all(MYSQLI_ASSOC) : [];

    // Define date format for grouping sales (daily sales)
    $date_format = "DATE(o.order_date)";

    //Sales by period query
    $sql_sales_period = "SELECT
        $date_format AS sale_period,
        SUM(oi.quantity) AS total_sold
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        ";

    // Apply date range filter if provided
    if ($start_date && $end_date) {
        $sql_sales_period .= " WHERE o.order_date BETWEEN '$start_date' AND '$end_date'";
    }

    $sql_sales_period .= "
        GROUP BY $date_format ORDER BY $date_format
    ";

    // Execute query
    $result_sales_period = $conn->query($sql_sales_period);
    $sales_by_period = $result_sales_period ? $result_sales_period->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-4 text-accent">Product Sales Report</h2>
                    <form method="GET" class="row g-2 align-items-end mb-4">
                        <div class="col-12 col-md-5 col-lg-3">
                            <label for="start_date" class="form-label">From:</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
                        </div>
                        <div class="col-12 col-md-5 col-lg-3">
                            <label for="end_date" class="form-label">To:</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-custom">Filter</button>
                        </div>
                    </form>
                    <h3 class="mb-3 text-primary-emphasis">Top Selling Products</h3>
                    <div class="table-responsive mb-5">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Total Sold</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['total_sold']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <h3 class="mb-3 text-primary-emphasis">Sales by Period</h3>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales_by_period as $sale): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sale['sale_period']); ?></td>
                                        <td><?php echo htmlspecialchars($sale['total_sold']); ?></td>
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