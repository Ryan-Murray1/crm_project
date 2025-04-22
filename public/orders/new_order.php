<?php
    // Connect to the database
    include("../../db.php");

    // ===============================
    // 1. Initialize Variables
    // ===============================
    $errors = [];
    $success = '';

    // ===============================
    // 2. Fetch Customers and Products
    // ===============================
    $customer_query = "SELECT customer_id, first_name, last_name FROM customers";
    $customer_result = mysqli_query($conn, $customer_query);

    $product_query = "SELECT product_id, name, price, stock_quantity FROM products";
    $product_result = mysqli_query($conn, $product_query);

    // ===============================
    // 3. Handle Form Submission
    // ===============================
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // -------------------------------
        // 3.1 Sanitize and Validate Inputs
        // -------------------------------
        $customer_id = filter_var($_POST['customer_id'], FILTER_VALIDATE_INT);
        $order_date = date('Y-m-d H-i-s');
        $status = 'Pending';

        if (!$customer_id) {
            $errors[] = "Please select a valid customer.";
        }

        // Proceed if no validation errors
        if (empty($errors)) {
            // Begin transaction
            mysqli_begin_transaction($conn);

            try {
                // -------------------------------
                // 3.2 Insert Order
                // -------------------------------
                $order_sql = "INSERT INTO orders (customer_id, order_date, status) VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $order_sql);
                mysqli_stmt_bind_param($stmt, "iss", $customer_id, $order_date, $status);

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error creating order: " . mysqli_error($conn));
                }

                $order_id = mysqli_insert_id($conn);
                $total_amount = 0;

                // -------------------------------
                // 3.3 Process Each Order Item
                // -------------------------------
                foreach ($_POST['items'] as $item) {
                    $product_id = filter_var($item['product_id'], FILTER_VALIDATE_INT);
                    $quantity = filter_var($item['quantity'], FILTER_VALIDATE_INT);
                    $price = filter_var($item['price'], FILTER_VALIDATE_FLOAT);

                    if ($product_id === false || $quantity === false || $price === false) {
                        throw new Exception("Invalid item data.");
                    }

                    // 3.3.1 Check Stock Availability
                    $stock_check = mysqli_prepare($conn, "SELECT stock_quantity FROM products WHERE product_id = ?");
                    mysqli_stmt_bind_param($stock_check, "i", $product_id);
                    mysqli_stmt_execute($stock_check);
                    $stock_result = mysqli_stmt_get_result($stock_check);
                    $stock = mysqli_fetch_assoc($stock_result)['stock_quantity'];

                    if ($stock < $quantity) {
                        throw new Exception("Insufficient stock for product ID: " . $product_id);
                    }

                    // 3.3.2 Insert Order Item
                    $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $item_sql);
                    mysqli_stmt_bind_param($stmt, "iiid", $order_id, $product_id, $quantity, $price);

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Error creating order item: " . mysqli_error($conn));
                    }

                    // 3.3.3 Update Product Stock
                    $update_stock = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
                    $stmt = mysqli_prepare($conn, $update_stock);
                    mysqli_stmt_bind_param($stmt, "ii", $quantity, $product_id);

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Error updating stock: " . mysqli_error($conn));
                    }

                    // 3.3.4 Track Total Amount
                    $total_amount += $price * $quantity;
                }

                // -------------------------------
                // 3.4 Update Order Total
                // -------------------------------
                $update_total = "UPDATE orders SET total_amount = ? WHERE order_id = ?";
                $stmt = mysqli_prepare($conn, $update_total);
                mysqli_stmt_bind_param($stmt, "ii", $total_amount, $order_id);

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error updating total amount: " . mysqli_error($conn));
                }

                // Commit transaction
                mysqli_commit($conn);
                $success = "Order created successfully.";

                // Redirect to orders list with status message
                header("Location: view_orders.php?message=Order added successfully");
                exit;

            } catch (Exception $e) {
                // Rollback on error
                mysqli_rollback($conn);
                error_log($e->getMessage(), 3, '/var/logs/error.log');
                $errors[] = "Something went wrong. Please try again later.";
            }
        }
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ================= HEAD SECTION ================= -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-3 text-center text-accent">Create New Order</h2>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <p class="mb-0"><?php echo htmlspecialchars($success); ?></p>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="" id="orderForm">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Select Customer</label>
                            <select name="customer_id" id="customer_id" class="form-select" required>
                                <option value="">-- Select Customer --</option>
                                <?php 
                                mysqli_data_seek($customer_result, 0);
                                while ($customer = mysqli_fetch_assoc($customer_result)): ?>
                                    <option value="<?php echo htmlspecialchars($customer['customer_id']); ?>">
                                        <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <h3 class="mt-4 mb-3">Order Items</h3>
                        <div id="orderItems">
                            <div class="row g-2 align-items-center mb-2 order-item">
                                <div class="col-7 col-md-8">
                                    <select name="items[0][product_id]" id="product_id" class="product-select form-select" required>
                                        <option value="">-- Select Product --</option>
                                        <?php 
                                        mysqli_data_seek($product_result, 0);
                                        while ($product = mysqli_fetch_assoc($product_result)): ?>
                                            <option value="<?php echo htmlspecialchars($product['product_id']); ?>"
                                                    data-price="<?php echo htmlspecialchars($product['price']); ?>"
                                                    data-stock="<?php echo htmlspecialchars($product['stock_quantity']); ?>">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                                (Stock: <?php echo htmlspecialchars($product['stock_quantity']); ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-5 col-md-4">
                                    <input type="number" name="items[0][quantity]" placeholder="Quantity" min="1" required class="form-control">
                                    <input type="hidden" name="items[0][price]" class="price-input">
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="addOrderItem()" class="btn btn-secondary mb-2">Add Another Item</button>
                        <button type="submit" class="btn btn-custom w-100">Create Order</button>
                    </form>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="../dashboard.php" class="btn btn-link">&larr; Back to Dashboard</a>
                        <a href="view_orders.php" class="btn btn-link">View Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let itemCount = 1;
        // Function to dynamically add new order item rows
        function addOrderItem() {
            const container = document.getElementById('orderItems');
            const newItem = document.createElement('div');
            newItem.className = 'row g-2 align-items-center mb-2 order-item';
            // Clone product dropdown
            const originalSelect = document.getElementById('product_id');
            const select = originalSelect.cloneNode(true);
            select.name = `items[${itemCount}][product_id]`;
            select.id = '';
            select.value = '';
            select.classList.add('form-select');
            // Create quantity input
            const quantity = document.createElement('input');
            quantity.type = 'number';
            quantity.name = `items[${itemCount}][quantity]`;
            quantity.placeholder = 'Quantity';
            quantity.min = '1';
            quantity.required = true;
            quantity.className = 'form-control';
            // Create hidden price input
            const price = document.createElement('input');
            price.type = 'hidden';
            price.name = `items[${itemCount}][price]`;
            price.className = 'price-input';
            price.required = true;
            // Append elements to order item container
            const col1 = document.createElement('div');
            col1.className = 'col-7 col-md-8';
            col1.appendChild(select);
            const col2 = document.createElement('div');
            col2.className = 'col-5 col-md-4';
            col2.appendChild(quantity);
            col2.appendChild(price);
            newItem.appendChild(col1);
            newItem.appendChild(col2);
            container.appendChild(newItem);
            itemCount++;
        }
        // Set price value when product selection changes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('product-select')) {
                const selected = e.target.options[e.target.selectedIndex];
                const priceInput = e.target.parentNode.parentNode.querySelector('.price-input');
                priceInput.value = selected.dataset.price;
            }
        });
    </script>
</body>
</html>
