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

                // Redirect to confirmation page
                header("Location: order_confirmation.php?order_id=" . $order_id);
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
    <link rel="stylesheet" href="../assets/CSS/styles.css">
</head>
<body>

    <!-- ================ HEADER SECTION ================ -->
    <header>
        <h1>Create New Order</h1>
        <a href="../dashboard.php" class="back-link">&larr; Back to Dashboard</a>
    </header>

    <!-- =============== ERROR MESSAGES SECTION =============== -->
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- =============== SUCCESS MESSAGE SECTION =============== -->
    <?php if ($success): ?>
        <div class="success">
            <p><?php echo htmlspecialchars($success); ?></p>
        </div>
    <?php endif; ?>

    <!-- ================ ORDER FORM SECTION ================ -->
    <div class="form-container">
        <form method="POST" action="" id="orderForm">
            
            <!-- ========== CUSTOMER SELECTION ========== -->
            <div class="form-group">
                <label for="customer_id">Select Customer:</label>
                <select name="customer_id" id="customer_id" required class="form-control">
                    <option value="">-- Select Customer --</option>
                    <?php while ($customer = mysqli_fetch_assoc($customers_result)): ?>
                        <option value="<?php echo htmlspecialchars($customer['customer_id']); ?>">
                            <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- ========== ORDER ITEMS SECTION ========== -->
            <div id="orderItems">
                <h3>Order Items</h3>
                <div class="order-item">
                    <select name="items[0][product_id]" id="product_id" class="product-select form-control" required>
                        <option value="">-- Select Product --</option>
                        <?php 
                        mysqli_data_seek($products_result, 0);
                        while ($product = mysqli_fetch_assoc($products_result)): 
                        ?>
                            <option value="<?php echo htmlspecialchars($product['product_id']); ?>"
                                    data-price="<?php echo htmlspecialchars($product['price']); ?>"
                                    data-stock="<?php echo htmlspecialchars($product['stock_quantity']); ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                                (Stock: <?php echo htmlspecialchars($product['stock_quantity']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <input type="number" name="items[0][quantity]" placeholder="Quantity" min="1" required class="form-control">
                    <input type="hidden" name="items[0][price]" class="price-input">
                </div>
            </div>

            <!-- ========== ACTION BUTTONS ========== -->
            <button type="button" onclick="addOrderItem()" class="btn btn-secondary">Add Another Item</button>
            <button type="submit" class="btn btn-primary">Create Order</button>
        </form>
    </div>   

    <!-- ================ JAVASCRIPT SECTION ================ -->
    <script>
        let itemCount = 1;

        // Function to dynamically add new order item rows
        function addOrderItem() {
            const container = document.getElementById('orderItems');
            const newItem = document.createElement('div');
            newItem.className = 'order-item';

            // Clone product dropdown
            const originalSelect = document.getElementById('product_id');
            const select = originalSelect.cloneNode(true);
            select.name = `items[${itemCount}][product_id]`;
            select.id = ''; // remove ID to prevent duplicates

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
            newItem.appendChild(select);
            newItem.appendChild(quantity);
            newItem.appendChild(price);

            // Add new item to DOM
            container.appendChild(newItem);
            itemCount++;
        }

        // Set price value when product selection changes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('product-select')) {
                const selected = e.target.options[e.target.selectedIndex];
                const priceInput = e.target.parentNode.querySelector('.price-input');
                priceInput.value = selected.dataset.price;
            }
        });
    </script>

</body>
</html>

