<?php
include("../../db.php");
$errors = [];

// Get feedback ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid feedback ID.");
}
$feedback_id = intval($_GET['id']);

// Optional: Confirm deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    // Delete feedback
    $stmt = $conn->prepare("DELETE FROM feedback WHERE feedback_id = ?");
    $stmt->bind_param("i", $feedback_id);
    if ($stmt->execute()) {
        header("Location: view_feedback.php?message=Feedback deleted successfully");
        exit;
    } else {
        $errors[] = "Database error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch feedback for confirmation display (optional)
$stmt = $conn->prepare("SELECT f.feedback_id, CONCAT(c.first_name, ' ', c.last_name) AS customer_name, f.comments FROM feedback f JOIN customers c ON f.customer_id = c.customer_id WHERE f.feedback_id = ?");
$stmt->bind_param("i", $feedback_id);
$stmt->execute();
$result = $stmt->get_result();
$feedback = $result && $result->num_rows > 0 ? $result->fetch_assoc() : null;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/CSS/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-light no-card-hover">
    <div class="container py-4 d-flex flex-column justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card card-custom bg-secondary-custom shadow-sm p-4">
                    <h2 class="mb-3 text-center text-accent">Delete Feedback</h2>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($feedback): ?>
                        <p class="mb-3 text-center">Are you sure you want to delete the following feedback?</p>
                        <div class="mb-3">
                            <div class="card bg-light border">
                                <div class="card-body p-3">
                                    <p class="mb-1"><strong>ID:</strong> <?php echo htmlspecialchars($feedback['feedback_id']); ?></p>
                                    <p class="mb-1"><strong>Customer:</strong> <?php echo htmlspecialchars($feedback['customer_name']); ?></p>
                                    <p class="mb-0"><strong>Comments:</strong> <?php echo htmlspecialchars($feedback['comments']); ?></p>
                                </div>
                            </div>
                        </div>
                        <form method="POST" class="d-flex flex-column gap-2">
                            <input type="hidden" name="confirm" value="yes">
                            <button type="submit" class="btn btn-danger w-100">Yes, Delete</button>
                            <a href="view_feedback.php" class="btn btn-link">Cancel</a>
                        </form>
                    <?php else: ?>
                        <p class="mb-2 text-center">Feedback entry not found.</p>
                        <div class="text-center">
                            <a href="view_feedback.php" class="btn btn-link">Back to Feedback List</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
