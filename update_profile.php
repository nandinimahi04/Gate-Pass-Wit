<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include('connection.php');

$faculty_id = $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($name == '' || $email == '') {
    echo "Name and email are required.";
    exit();
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email format.";
    exit();
}

$sql = "UPDATE user_regis SET full_name = ?, email = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "Prepare failed: " . $conn->error;
    exit();
}

$stmt->bind_param("ssi", $name, $email, $faculty_id);

if ($stmt->execute()) {
    // Redirect back or show success message
    header("Location: faculty_dashboard.php?msg=Profile updated successfully");
    exit();
} else {
    echo "Update failed: " . $stmt->error;
}

$stmt->close();
$conn->close();
