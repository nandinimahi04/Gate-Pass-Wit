<?php
session_start();
include('connection.php');

// Check if user is logged in and is faculty
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'faculty') {
    echo "Unauthorized";
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request method";
    exit();
}

// Validate required POST parameters
if (!isset($_POST['request_id'], $_POST['status'])) {
    echo "Missing parameters";
    exit();
}

$request_id = intval($_POST['request_id']);
$status_str = $_POST['status'];

// Map status strings to integer codes
$status_map = ['approved' => 1, 'rejected' => 2];
if (!array_key_exists($status_str, $status_map)) {
    echo "Invalid status";
    exit();
}

$status = $status_map[$status_str];

// Comments only required for rejection
$comments = null;
if ($status === 2) { // rejected
    if (!isset($_POST['comments']) || trim($_POST['comments']) === '') {
        echo "Comments required for rejection";
        exit();
    }
    $comments = trim($_POST['comments']);
}

if ($status === 2) {
    // Update status and comments for rejected
    $stmt = $conn->prepare("UPDATE gate_pass SET status = ?, comments = ? WHERE id = ?");
    $stmt->bind_param("isi", $status, $comments, $request_id);
} else {
    // Update status and clear comments for approved
    $stmt = $conn->prepare("UPDATE gate_pass SET status = ?, comments = NULL WHERE id = ?");
    $stmt->bind_param("ii", $status, $request_id);
}

if ($stmt->execute()) {
    // After updating status and comments for rejection, send email notifications
    if ($status === 2) {
        // Fetch student and faculty emails
        $fetch_sql = "SELECT ur.email AS student_email, ur.full_name AS student_name, gp.reason, gp.comments, f.email AS faculty_email, f.full_name AS faculty_name FROM gate_pass gp LEFT JOIN user_regis ur ON gp.student_id = ur.id LEFT JOIN user_regis f ON f.id = ? WHERE gp.id = ?";
        $fetch_stmt = $conn->prepare($fetch_sql);
        $faculty_id = $_SESSION['user_id'];
        $fetch_stmt->bind_param("ii", $faculty_id, $request_id);
        $fetch_stmt->execute();
        $fetch_stmt->bind_result($student_email, $student_name, $reason, $comments, $faculty_email, $faculty_name);
        if ($fetch_stmt->fetch()) {
            // Email to student
            $to_student = $student_email;
            $subject_student = 'Gate Pass Application Rejected';
            $message_student = "Hello $student_name,\n\nYour gate pass application has been rejected.\nReason: $reason\nComments: $comments\n\nRegards,\nWIT Pass Team";
            $headers = 'From: support@witcollege.com' . "\r\n" .
                       'Reply-To: support@witcollege.com' . "\r\n" .
                       'X-Mailer: PHP/' . phpversion();
            @mail($to_student, $subject_student, $message_student, $headers);
            // Email to faculty
            $to_faculty = $faculty_email;
            $subject_faculty = 'Gate Pass Application Rejection Processed';
            $message_faculty = "Hello $faculty_name,\n\nYou have rejected a gate pass application for $student_name.\nReason: $reason\nComments: $comments\n\nRegards,\nWIT Pass Team";
            @mail($to_faculty, $subject_faculty, $message_faculty, $headers);
        }
        $fetch_stmt->close();
    }
    echo "success";
} else {
    echo "Database error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
