<?php
    // Include the database connection file
    include("../db.php");

    // SQL query to retrieve data from customers table
    $sql = "SELECT * FROM customers";
    $result = $conn->query($sql);

    // Check if the query returned any results
    if ($result === false) {
        die("Error fetching customers: " . $conn->error);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
        
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="../assets/CSS/styles.css">

</head>
<body>

    <h2>Customer List</h2>

    <table>
        <tr>
            <th>Customer ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>

        <?php
        // Fetch and display each customer from the result
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row["customer_id"]) . "</td>
                        <td>" . htmlspecialchars($row["first_name"]) . "</td>
                        <td>" . htmlspecialchars($row["last_name"]) . "</td>
                        <td>" . htmlspecialchars($row["email"]) . "</td>
                        <td>" . htmlspecialchars($row["phone_number"]) . "</td>
                        <td>" . htmlspecialchars($row["address"]) . "</td>
                        <td>
                            <a href='edit_customer.php?customer_id=" . htmlspecialchars($row["customer_id"]) . "'>Edit</a> | 
                            <a href='delete_customer.php?customer_id=" . htmlspecialchars($row["customer_id"]) . "' onclick=\"return confirm('Are you sure you want to delete this customer?')\">Delete</a>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No customers found.</td></tr>";
        }
        ?>

    </table>

    <br><a href="dashboard.php">Back to Dashboard</a>

    <?php
        // Close the database connection
        $conn->close();
    ?>

</body>
</html>
