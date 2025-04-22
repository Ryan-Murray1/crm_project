<?php
    // Include the database connection file
    include("../db.php");

    // Optionally, add any authentication checks here to ensure only authorized users can access the dashboard
    // Example: if (!isset($_SESSION['logged_in'])) { header('Location: login.php');
    
    $message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Dashboard</title>
    
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Link to external CSS file (your overrides) -->
    <link rel="stylesheet" href="assets/CSS/styles.css">
    <!-- (Optional) Bootstrap JS for interactive components -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Link to Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

</head>
<body class="bg-light dashboard-animate">
    <div class="container py-4 d-flex flex-column justify-content-between min-vh-100">
        <header>
            <h1 class="display-5 text-center mb-3 text-accent">Welcome to the CRM Dashboard</h1>
        </header>

        <section class="text-center mb-4">
            <h2 class="h4 text-primary" style="color: var(--primary) !important;">Quick Links</h2>
            <p class="lead">From here, you can manage your customers, view orders, and analyze feedback.</p>
        </section>

        <?php if ($message): ?>
            <div class="alert alert-info text-center mb-4" style="border-color: var(--accent); color: var(--accent);">
                <p class="mb-0"><?php echo $message; ?></p>
            </div>
        <?php endif; ?>

        <nav class="mb-4">
            <!-- Quick Links - Start of grid -->
            <div class="row g-4 justify-content-center">
                <!-- Customers -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="card card-custom bg-secondary-custom h-100">
                        <div class="card-body">
                            <h3 class="card-title text-primary">Customers</h3>
                            <ul class="list-unstyled">
                                <li><a href="customers/add_customer.php" class="text-accent">Add Customer</a></li>
                                <li><a href="customers/view_customers.php" class="text-accent">View Customers</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Products -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="card card-custom bg-secondary-custom h-100">
                        <div class="card-body">
                            <h3 class="card-title text-primary">Products</h3>
                            <ul class="list-unstyled">
                                <li><a href="products/view_products.php" class="text-accent">View Products</a></li>
                                <li><a href="products/add_product.php" class="text-accent">Add Product</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Orders -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="card card-custom bg-secondary-custom h-100">
                        <div class="card-body">
                            <h3 class="card-title text-primary">Orders</h3>
                            <ul class="list-unstyled">
                                <li><a href="orders/new_order.php" class="text-accent">Create Order</a></li>
                                <li><a href="orders/view_orders.php" class="text-accent">View Orders</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Feedback -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="card card-custom bg-secondary-custom h-100">
                        <div class="card-body">
                            <h3 class="card-title text-primary">Feedback</h3>
                            <ul class="list-unstyled">
                                <li><a href="feedback/view_feedback.php" class="text-accent">View Feedback</a></li>
                                <li><a href="feedback/submit_feedback.php" class="text-accent">Submit Feedback</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Reports -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="card card-custom bg-secondary-custom h-100">
                        <div class="card-body">
                            <h3 class="card-title text-primary">Reports</h3>
                            <ul class="list-unstyled">
                                <li><a href="reports/product_sales.php" class="text-accent">Sales Report</a></li>
                                <li><a href="reports/customer_orders.php" class="text-accent">Customer Orders</a></li>
                                <li><a href="reports/feedback_analysis.php" class="text-accent">Feedback Analysis</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div> 
            <!-- Quick Links - End of grid -->
        </nav>
        <footer class="text-center text-muted pt-3">
            <p class="mb-0">&copy; 2025 CRM Project. All rights reserved.</p>
        </footer>
    </div> <!-- End of container -->
</body>
</html>
