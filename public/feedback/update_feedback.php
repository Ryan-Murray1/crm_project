<?php
    // Connect to the database
    include("../../db.php");   

    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $feedback_id = filter_var($_POST['feedback_id'], FILTER_VALIDATE_INT);
        $customer_id = filter_var($_POST['customer_id'], FILTER_VALIDATE_INT);
        $product_id = isset($_POST['product_id']) && $_POST['product_id'] !== '' ? filter_var($_POST['product_id'], FILTER_VALIDATE_INT) : null;
        $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT);
        $comments = htmlspecialchars($_POST['comments']);
    } else {
        die ("Invalid request method");
    }

    // Validate input
    if (!$feedback_id ||!$customer_id || !$rating || empty($comments)) {
        $errors[] = "All required fields must be filled out.";
    }
    if ($rating < 1 || $rating > 5) {
        $errors[] = "Rating must be between 1 and 5";
    }
    if ($product_id !== null && ($product_id === false || $product_id <=0)) {
        $errors[] = "Invalid product ID";
    }

    // Update feedback in the database
    if (empty($errors)) {
        $sql = "UPDATE feedback SET customer_id = ?, product_id = ?, rating = ?, comments = ? WHERE feedback_id = ?";
        $stmt = $conn->prepare($sql);
        if ($product_id === null) {
            $null = null;
            $stmt->bind_param("iiisi", $customer_id, $null, $rating, $comments, $feedback_id);
        } else {
            $stmt->bind_param("iiisi", $customer_id, $product_id, $rating, $comments, $feedback_id);
        }
    
        if ($stmt->execute()) {
            header("Location: view_feedback.php?message=Feedback updated successfully");
            exit;
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:red'>" . htmlspecialchars($error) . "</p>";
        }
        echo "<p><a href='edit_feedback.php?id=" . urlencode($feedback_id) . "'>Back to Edit Feedback</a></p>";
    }
    ?>


?>