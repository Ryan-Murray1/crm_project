<?php
    // Include the database connection file
    include("../db.php");

    // Initialize error array and form values
    $errors = array();
    $name = $description = $price = $stock_quantity = "";

    // Function to sanitize input
    function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Check if the form is submitted using POST method
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get form data
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        $price = sanitize_input($_POST['price']);
        $stock_quantity = sanitize_input($_POST['stock_quantity']);

        // Validate required fields
        if (empty($name)) {
            $errors[] = "Product name is required.";
        } elseif (strlen($name) > 50) {
            $errors[] = "Product name cannot exceed 50 characters.";
        }

        // Validate description
        if (empty($description)) {
            $errors[] = "Product description is required.";
        }

        // Validate price (must be a positive number with up to 2 decimal places)
        if (empty($price)) {
            $errors[] = "Price is required.";
        } elseif (!preg_match("/^\d+(\.\d{1,2})?$/", $price)) {
            $errors[] = "Price must be a valid number with up to 2 decimal places.";
        } elseif ($price <= 0) {
            $errors[] = "Price must be greater than 0.";
        }

        // Validate stock quantity (must be a positive integer)
        if (empty($stock_quantity)) {
            $errors[] = "Stock quantity is required.";
        } elseif (!filter_var($stock_quantity, FILTER_VALIDATE_INT) || $stock_quantity < 0) {
            $errors[] = "Stock quantity must be a positive whole number.";
        }

        // If there are no errors, insert into database
        if (empty($errors)) {

            // Convert price to float and stock to integer
            $price = floatval($price);
            $stock_quantity = intval($stock_quantity);

            // SQL query to insert data into products table
            $sql = "INSERT INTO products (name, description, price, stock_quantity)
                    VALUES (?, ?, ?, ?)";
            
            // Prepare the statement
            $stmt = mysqli_prepare($conn, $sql);

            // Bind parameters to the statement
            mysqli_stmt_bind_param($stmt, "ssdi", $name, $description, $price, $stock_quantity);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                
                echo "<div class='success'>New product added successfully. <a href='dashboard.php'>Back to Dashboard</a></div>";
            } else {
                // Database Error
                $errors[] = "Database error: " . $conn->error;;
            }
            
            // Close the statement
            mysqli_stmt_close($stmt);
        }

    // Close the connection
    mysqli_close($conn);
    }   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>

    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="../assets/CSS/styles.css">

</head>
<body>
    <h1>Add Product</h1>

     <!-- Display errors -->
     <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- The form -->
    <form action="add_product.php" method="POST">
        <label for="name">Product Name:</label>
        <input type="text" id="name" name="name" required
               value="<?php echo htmlspecialchars($name); ?>">

        <label for="description">Product Description:</label>
        <input type="text" id="description" name="description" required
               value="<?php echo htmlspecialchars($description); ?>">

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" min="0" required
               value="<?php echo htmlspecialchars($price); ?>">

        <label for="stock_quantity">Stock Quantity:</label>
        <input type="number" id="stock_quantity" name="stock_quantity" min="0" required
               value="<?php echo htmlspecialchars($stock_quantity); ?>">

        <input type="submit" value="Add Product">
    </form> 
</body>
</html>