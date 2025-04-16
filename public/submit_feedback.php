<?php
    // Include the database connection file
    include("../db.php");

    // Initialize errors array
    $errors = array();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize and valaidate input data
        $customer_id = filter_var($_POST['customer_id'], FILTER_VALIDATE_INT);
        $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);  
        $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT);
        $comments = htmlspecialchars($_POST['comments']);
    }

    // Validate customer ID
    if ($customer_id === false || $customer_id <= 0) {
        $errors[] = "Invalid customer ID.";
    }

    // Validate rating
    if ($rating === false || $rating <= 0 || $rating > 5) {
        $errors[] = "Rating must be between 1 and 5.";
    }

    // Validate comments
    if (empty($comments)) {
        $errors[] = "Comments cannot be empty.";
    }

    // If there are no errors, proceed with database insertion
    if (empty($errors)) {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO feedback (customer_id, product_id, rating, comments) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $customer_id, $product_id, $rating, $comments);

        if ($stmt->execute()) {
            header("Location: dashboard.php?message=Feedback submitted successfully");
            exit;
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }

        $stmt->close();
}

    // Fetch cudtomers and products for dropdowns
    $customers = mysqli_query($conn, "SELECT cudtomer_id, first_name, last_name FROM customers");
    $products = mysqli_query($conn, "SELECT product_id, name FROM products");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback</title>

    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="../assets/CSS/styles.css">


</head>
<body>
    <h1>Submit Feedback</h1>

    <?php 
        if (isset($errors) && !empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
    <?php endif; ?>

    <form method="POST" action="submit_feedback.php">
    <label for="customer_id">Customer:</label>
        <select name="customer_id" id="customer_id" required>
            <option value="">Select a customer</option>
            <?php while ($row = mysqli_fetch_assoc($customers)): ?>
                <option value="<?php echo $row['customer_id']; ?>">
                    <?php echo $row['first_name'] . ' ' . $row['last_name']; ?></option>
            </option>
            <?php endwhile; ?>
        </select><br>

        <label for="product_id">Product (Optional):</label>
        <select name="product_id" id="product_id">
            <option value="">Select Product</option>
            <?php while ($row = mysqli_fetch_assoc($products)): ?>
                <option value="<?php echo $row['product_id']; ?>">
                    <?php echo $row['name']; ?>
                </option>
            <?php endwhile; ?>
        </select><br>

        <label for="rating">Rating (1-5):</label>
        <select name="rating" id="rating" required>
            <option value="">Select rating</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
            <?php endfor; ?>
        </select><br>

        <label for="comments">Comments:</label>
        <textarea name="comments" id="comments" rows="5" required></textarea><br>

        <button type="submit">Submit Feedback</button>
    </form>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>

