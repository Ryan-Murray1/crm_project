<?php 
    // Include the database connection file
    include("../../db.php");

    // Function to sanitize input
    function sanitizeInput($data) {
        return htmlspecialchars(trim($data));
    }

    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Initialize error array
        $errors = array();

        // Sanitize and get data from the POST request
        $customer_id = filter_var($_POST['customer_id'], FILTER_VALIDATE_INT); // Validates the customer ID
        $first_name = sanitizeInput($_POST['first_name']);
        $last_name = sanitizeInput($_POST['last_name']);
        $email = sanitizeInput($_POST['email']);
        $phone_number = sanitizeInput($_POST['phone_number']);
        $address = sanitizeInput($_POST['address']);

        // Validate fields
        if ($customer_id === false) $errors[] = "Invalid customer ID.";
        if (strlen($first_name) > 255) $errors[] = "First name too long.";
        if (strlen($last_name) > 255) $errors[] = "Last name too long.";
        if (strlen($email) > 255) $errors[] = "Email too long.";
        if (strlen($phone_number) > 15) $errors[] = "Phone number too long.";
        if (!preg_match("/^[a-zA-Z ]*$/", $first_name)) $errors[] = "First name must contain only letters and spaces.";
        if (!preg_match("/^[a-zA-Z ]*$/", $last_name)) $errors[] = "Last name must contain only letters and spaces.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
        if (!preg_match("/^[0-9\-\(\)\/\+\s]*$/", $phone_number)) $errors[] = "Phone number can contain only numbers, spaces, and symbols like - + ( ) /.";

        // If errors exist, display and stop
        if (!empty($errors)) {
            // Display errors
            echo "<div class='error'>";
            foreach ($errors as $error) {
                echo htmlspecialchars($error) . "<br>";
            }
            echo "</div>";
            echo '<a href="javascript:history.back()">&larr; Go Back</a> | <a href="dashboard.php">Dashboard</a>';
            exit;
        }

        // If no errors, proceed with database update
        $sql = "UPDATE customers SET first_name= ?, last_name= ?, email= ?, phone_number= ?, address= ? 
                WHERE customer_id= ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            // Handle statement preparation error
            die("<div class='error'>Error preparing the statement: " . htmlspecialchars($conn->error) . "</div>");
        }

        // Bind parameters to the statement
        mysqli_stmt_bind_param($stmt, "sssssi", $first_name, $last_name, $email, $phone_number, $address, $customer_id);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // Redirect after successful update
            header("Location: ../dashboard.php?message=Customer updated successfully");
            exit;
        } else {
            // Handle update error
            echo "<div class='error'>Error updating record: " . htmlspecialchars(mysqli_error($conn)) . "</div>";
            echo '<a href="javascript:history.back()">&larr; Go Back</a> | <a href="dashboard.php">Dashboard</a>';
        }

        // Close the statement and connection
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    } else {
        // If accessed directly, redirect to dashboard
        header("Location: dashboard.php");
        exit;
    }
?>