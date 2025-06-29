<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_coordinator = $_POST['class_coordinator'] ?? '';
    $hod = $_POST['hod'] ?? '';
    $reason = trim($_POST['reason'] ?? '');

    if ($class_coordinator && $hod && $reason) {

        // Step 1: Fetch student details
        $student_query = "SELECT full_name, email, class FROM user_regis WHERE id = ?";
        $stmt = $conn->prepare($student_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();

        if (!$student) {
            die("Student record not found.");
        }

        $student_name = $student['full_name'];
        $student_email = $student['email'];
        $student_year = $student['class'];

        // Step 2: Insert into gate_pass table
        $insert_query = "INSERT INTO gate_pass 
            (student_id, student_name, student_email, student_year, class_coordinator, hod, reason, status, request_time) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())";

        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("issssss", $student_id, $student_name, $student_email, $student_year, $class_coordinator, $hod, $reason);

        if ($stmt->execute()) {
            // Send email notification to student
            $to = $student_email;
            $subject = 'Gate Pass Application Submitted';
            $message = "Hello $student_name,\n\nYour gate pass application has been submitted with the following reason: $reason.\n\nYour request is pending approval.\n\nRegards,\nWIT Pass Team";
            $headers = 'From: support@witcollege.com' . "\r\n" .
                       'Reply-To: support@witcollege.com' . "\r\n" .
                       'X-Mailer: PHP/' . phpversion();
            @mail($to, $subject, $message, $headers);
            header("Location: student_dashboard.php?success=1");
            exit();
        } else {
            die("Error inserting gate pass: " . $stmt->error);
        }
    } else {
        die("All fields are required.");
    }
} else {
    header("Location: student_dashboard.php");
    exit();
}
?>
