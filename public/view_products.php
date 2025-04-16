<?php
    // Include the database connection file
    include("../db.php");

    // Initialize result variable
    $result = false;

    // SQL query to select data from the products table
    $sql = "SELECT * FROM products";
    
     // Execute the query with wrror handling
     if ($stmt = $conn->prepare($sql)) {
        // Execute the query
        $stmt->execute();
        // Get the result
        $result = $stmt->get_result();
        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing query: " . $conn->error;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
        
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="../assets/CSS/styles.css">

</head>
<body>

    <h2>Product List</h2>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock Quantity</th>
                <th>Actions</th>
            </tr>

            <?php
            // Fetch and display each product from the result
            while ($row = $result->fetch_assoc()) {
                $product_id = htmlspecialchars($row["product_id"]);
                echo "<tr>
                        <td>" . $product_id . "</td>
                        <td>" . htmlspecialchars($row["name"]) . "</td>
                        <td>" . htmlspecialchars($row["description"]) . "</td>
                        <td>" . htmlspecialchars($row["price"]) . "</td>
                        <td>" . htmlspecialchars($row["stock_quantity"]) . "</td>
                        <td>
                            <a href='edit_product.php?product_id=" . $product_id . "'>Edit</a> |
                            <form method='POST' action='delete_product.php' style='display:inline;'>
                                <input type='hidden' name='product_id' value='" . $product_id . "'>
                                <input type='hidden' name='confirm' value='true'>
                                <button type='submit' onclick='return confirm(\"Are you sure you want to delete this product?\");'>Delete</button>
                            </form>
                        </td>
                    </tr>";
            }
            ?>
        </table>
        
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>

    <br><a href="dashboard.php">Back to Dashboard</a>

    <?php
        // Close the database connection
        $conn->close();
    ?>

</body>
</html>




    