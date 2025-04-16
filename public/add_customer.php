<?php
    // Include the database connection file
    include("../db.php");

    // Initialize variables to preserve form values
    $first_name = $last_name = $email = $phone_number = $address = "";

    // Initialize errors array
    $errors = array();

    // Check if the form is submitted using POST method
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
       
        // Function to sanitize input
        function sanitizeInput($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }
    
        // Sanitize and validate input data
        $first_name = sanitizeInput($_POST["first_name"]);
        $last_name = sanitizeInput($_POST["last_name"]);
        $email = sanitizeInput($_POST["email"]);
        $phone_number = sanitizeInput($_POST["phone_number"]);
        $address = sanitizeInput($_POST["address"]);

        // Validate first name and last name (allow only letters and spaces)
        if (!preg_match("/^[a-zA-Z ]*$/", $first_name)) {
            $errors[] = "First name must contain only letters and spaces.";
        }

        if (!preg_match("/^[a-zA-Z ]*$/", $last_name)) {
            $errors[] = "Last name must contain only letters and spaces.";
        } 
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        // Validate phone number
        if (!preg_match("/^[0-9\-\(\)\/\+\s]*$/", $phone_number)) {
            $errors[] = "Phone number can contain only numbers, spaces, and symbols like - + ( ) /.";
        }
        
        
        // If there are no errors, proceed with database insertion 
        if (empty($errors)) {
            $sql = "INSERT INTO customers (first_name, last_name, email, phone_number, address)
            VALUES (?, ?, ?, ?, ?)";

            // Prepare the statement
            $stmt = mysqli_prepare($conn, $sql);

            // Bind parameters to the statement
            if($stmt) {
                mysqli_stmt_bind_param($stmt, "sssss", $first_name, $last_name, $email, $phone_number, $address);

                // Execute the statement
                if (mysqli_stmt_execute($stmt)) {
                    // Redirect after successful insertion
                    header("Location: dashboard.php?status=added");
                    exit;
                } else {
                    $errors[] = "Database error: " . $conn->error;
                }
                       
                // Close the statement
                mysqli_stmt_close($stmt);
            } else {
                $errors[] = "Failed to prepare SQL statement: " . $conn->error;
            }
        }

        // Close the database connection
        mysqli_close($conn);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customer</title>
        
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="../assets/CSS/styles.css">

</head>
<body>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="add_customer.php">
        <label for="first_name">First Name</label>
        <input type="text" id="first_name" name="first_name" required 
               value="<?php echo htmlspecialchars($first_name); ?>">

        <label for="last_name">Last Name</label>
        <input type="text" id="last_name" name="last_name" required 
               value="<?php echo htmlspecialchars($last_name); ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required 
               value="<?php echo htmlspecialchars($email); ?>">

        <label for="phone_number">Phone Number</label>
        <input type="tel" id="phone_number" name="phone_number" required 
               value="<?php echo htmlspecialchars($phone_number); ?>">

        <label for="address">Address</label>
        <input type="text" id="address" name="address" required 
               value="<?php echo htmlspecialchars($address); ?>">

        <button type="submit">Add Customer</button>
    </form>

</body>
</html>
