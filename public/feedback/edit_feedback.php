<?php
    // Include the database connection file
    include("../../db.php");
    
    // Initialize errors array
    $errors = [];
    $feedback = null;

    // Get feedback ID from URL
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        die("Invalid feedback ID provided.");
    }
    $feedback_id = intval($_GET['id']);

    // Fetch feedback from database
    $sql = "SELECT * FROM feedback WHERE feedback_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $feedback_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $feedback = $result->fetch_assoc();
    } else {
        die("Feedback not found.");
    }
    $stmt->close();

    // Fetch customer and products for dropdowns
    $customer = [];
    $result = $conn->query("SELECT customer_id, first_name, last_name FROM customers");
    if ($result) {
        $customer = $result->fetch_all(MYSQLI_ASSOC);
    }

    $products = [];
    $result = $conn->query("SELECT product_id, name FROM products");
    if ($result) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-3 text-center text-accent">Edit Feedback #<?php echo htmlspecialchars($feedback['feedback_id']); ?></h2>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="update_feedback.php">
                        <input type="hidden" name="feedback_id" value="<?php echo htmlspecialchars($feedback_id); ?>">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select name="customer_id" id="customer_id" required class="form-select">
                                <?php foreach ($customer as $c): ?>
                                    <option value="<?php echo $c['customer_id']; ?>"
                                        <?php if ($c['customer_id'] == $feedback['customer_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product (Optional)</label>
                            <select name="product_id" id="product_id" class="form-select">
                                <option value="">Select Product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['product_id']; ?>"
                                        <?php if ($feedback['product_id'] == $product['product_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating (1-5)</label>
                            <select name="rating" id="rating" required class="form-select">
                                <option value="">Select rating</option>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php if ($feedback['rating'] == $i) echo 'selected'; ?>>
                                        <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comments" class="form-label">Comments</label>
                            <textarea name="comments" id="comments" rows="5" required class="form-control"><?php echo htmlspecialchars($feedback['comments']); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-custom w-100">Update Feedback</button>
                    </form>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="view_feedback.php" class="btn btn-link">&larr; Back to Feedback List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
