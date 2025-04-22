<?php
    // Include the database connection file
    include("../../db.php");

    // Function to sanitize input
    function sanitizeInput($data) {
        return htmlspecialchars(trim($data));
    }

    // Initialize variables to preserve form values
    $first_name = $last_name = $email = $phone_number = $address = "";
    $errors = [];

    // Check if the form is submitted using POST method
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Sanitize and validate input data
        $first_name = sanitizeInput($_POST["first_name"]);
        $last_name = sanitizeInput($_POST["last_name"]);
        $email = sanitizeInput($_POST["email"]);
        $phone_number = sanitizeInput($_POST["phone_number"]);
        $address = sanitizeInput($_POST["address"]);

        // Max length checks
        if (strlen($first_name) > 255) $errors[] = "First name too long.";
        if (strlen($last_name) > 255) $errors[] = "Last name too long.";
        if (strlen($email) > 255) $errors[] = "Email too long.";
        if (strlen($phone_number) > 15) $errors[] = "Phone number too long.";

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
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssss", $first_name, $last_name, $email, $phone_number, $address);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: ../dashboard.php?message=Customer added successfully");
                    exit;
                } else {
                    $errors[] = "Database error: " . $conn->error;
                }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-3 text-center text-accent">Add Customer</h2>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="add_customer.php">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" name="first_name" maxlength="255" required class="form-control" value="<?php echo htmlspecialchars($first_name); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" maxlength="255" required class="form-control" value="<?php echo htmlspecialchars($last_name); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" maxlength="255" required class="form-control" value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="tel" id="phone_number" name="phone_number" maxlength="15" required class="form-control" value="<?php echo htmlspecialchars($phone_number); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea id="address" name="address" rows="3" required class="form-control"><?php echo htmlspecialchars($address); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-custom w-100">Add Customer</button>
                    </form>
                    <a href="../dashboard.php" class="btn btn-link d-block mt-3">&larr; Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
