<?php 
    // Include the database connection file
    include("../db.php");

    // Function to sanitize input
    function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;        
    }

    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $errors = array();

    // Sanitize and get data from the POST request
    $id = filter_var($_POST['customer_id'], FILTER_VALIDATE_INT); // Validates the customer ID
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone_number = sanitizeInput($_POST['phone_number']);
    $address = sanitizeInput($_POST['address']);
    }

    // Validate fields
    if ($id === false) $errors[] = "Invalid customer ID.";
    if (!preg_match("/^[a-zA-Z ]*$/", $first_name)) $errors[] = "First name must contain only letters and spaces.";
    if (!preg_match("/^[a-zA-Z ]*$/", $last_name)) $errors[] = "Last name must contain only letters and spaces.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (!preg_match("/^[0-9- ]*$/", $phone_number)) $errors[] = "Invalid phone number format.";

    // If errors exist, display and stop
    if (!empty($errors)) {
        echo "<div class='error'>";
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
        echo "</div>";
        exit;
    }

    // If no errors, proceed with database update
    if (empty($errors)) {
        // SQL query to update data in customers table
        $sql = "UPDATE customers SET first_name= ?, last_name= ?, email= ?, phone_number= ?, address= ? 
                WHERE customer_id= ?";

        // Prepare the statement
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            die("Error preparing the statement: " . $conn->error);
        }

        // Bind parameters to the statement
        mysqli_stmt_bind_param($stmt, "sssssi", $first_name, $last_name, $email, $phone_number, $address, $id);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // Redirect after successful update
            header("Location: dashboard.php?status=updated");
            exit;
        } else {
            echo "<div class='error'>Error updating record: " . mysqli_error($conn) . "</div>";
        }

    // Close the statement
    mysqli_stmt_close($stmt);
        
    // Close the database connection
    mysqli_close($conn);
    }
?>