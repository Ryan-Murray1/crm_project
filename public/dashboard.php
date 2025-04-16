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
    
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="../assets/CSS/styles.css">

</head>
<body>
    <header>
        <h1>Welcome to the CRM Dashboard</h1>
    </header>

    <section>
        <h2>Quick Links</h2>
        <p>From here, you can manage your customers, view orders, and analyze feedback.</p>
    </section>

    <?php if ($message): ?>
        <div class="success">
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <nav>
        <ul>
            <!-- Customer Management -->
            <li class="nav-section">
                <h3>Customers</h3>
                <ul>
                    <li><a href="customers/view_customers.php">View Customers</a></li>
                    <li><a href="customers/add_customer.php">Add Customer</a></li>
                </ul>
            </li>

            <!-- Product Management -->
            <li class="nav-section">
                <h3>Products</h3>
                <ul>
                    <li><a href="products/view_products.php">View Products</a></li>
                    <li><a href="products/add_product.php">Add Product</a></li>
                </ul>
            </li>

            <!-- Order Management -->
            <li class="nav-section">
                <h3>Orders</h3>
                <ul>
                    <li><a href="orders/create_order.php">Create Order</a></li>
                    <li><a href="orders/view_orders.php">View Orders</a></li>
                </ul>
            </li>

            <!-- Feedback Management -->
            <li class="nav-section">
                <h3>Feedback</h3>
                <ul>
                    <li><a href="feedback/view_feedback.php">View Feedback</a></li>
                    <li><a href="feedback/submit_feedback.php">Submit Feedback</a></li>
                </ul>
            </li>

            <!-- Reports -->
            <li class="nav-section">
                <h3>Reports</h3>
                <ul>
                    <li><a href="reports/sales_report.php">Sales Report</a></li>
                    <li><a href="reports/customer_report.php">Customer Analysis</a></li>
                    <li><a href="reports/feedback_analysis.php">Feedback Analysis</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <footer>
        <p>&copy; 2025 CRM Project. All rights reserved.</p>
    </footer>
    
</body>
</html>