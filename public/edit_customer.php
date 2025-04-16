<?php
    // Include the database connection file
    include("../db.php");

        // Check if the customer ID is set
        if (!isset($_GET["customer_id"])) {
            echo "No customer selected.";
            exit;
        }
    

        // Get the customer ID from the URL
        $customer_id = $_GET["customer_id"];

        // SQL query to retrieve data from customers table
        $sql = "SELECT * FROM customers WHERE customer_id = ?";

        // Prepare the statement
        $stmt = mysqli_prepare($conn, $sql);

        // Bind parameters to the statement
        mysqli_stmt_bind_param($stmt, "i", $customer_id);

        // Execute the statement
        mysqli_stmt_execute($stmt);

        // Get the result
        $result = mysqli_stmt_get_result($stmt);

        // Fetch the row
        $row = mysqli_fetch_assoc($result); 

    if (isset($stmt)) {
        // Close the statement
        mysqli_stmt_close($stmt);

        // Close the connection
        mysqli_close($conn);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Customer</title>

    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="../assets/CSS/styles.css">

</head>
<body>
<form method="POST" action="update_customer.php">
        <input type="hidden" name="customer_id" value="<?php echo $row['customer_id']; ?>">
        
        <label>First Name: </label>
        <input type="text" name="first_name" value="<?php echo $row['first_name']; ?>"><br>

        <label>Last Name: </label>
        <input type="text" name="last_name" value="<?php echo $row['last_name']; ?>"><br>

        <label>Email: </label>
        <input type="email" name="email" value="<?php echo $row['email']; ?>"><br>

        <label>Phone: </label>
        <input type="text" name="phone_number" value="<?php echo $row['phone_number']; ?>"><br>

        <label>Address: </label>
        <input type="text" name="address" value="<?php echo $row['address']; ?>"><br>

        <button type="submit">Update</button>
</form>
    
</body>
</html>