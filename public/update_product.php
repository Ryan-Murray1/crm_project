<?php
    // Include the database connection file
    include("../db.php");

    // Get form data
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];

    // SQL query to update data in products table
    $sql = "UPDATE products 
            SET name=?, description=?, price=?, stock_quantity=? 
            WHERE product_id=?";

    // Prepare the statement
    $stmt = mysqli_prepare($conn, $sql);

    // Bind parameters to the statement
    mysqli_stmt_bind_param($stmt, "ssiiii", $name, $description, $price, $stock_quantity, $product_id);

    // Execute the statement
    mysqli_stmt_execute($stmt);

    echo "Product updated successfully. <a href='dashboard.php'>Back to Dashboard</a>";

    // Close the statement
    mysqli_stmt_close($stmt);

    // Close the connection
    mysqli_close($conn);
            
?>