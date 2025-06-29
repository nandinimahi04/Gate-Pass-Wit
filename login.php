<?php
session_start();
require_once 'connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type']; // this matches 'Student' or 'Faculty'

    $stmt = $conn->prepare("SELECT id, full_name, password, role FROM user_regis WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            if (strtolower($user['role']) === strtolower($user_type)) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                if ($user_type === 'student') {
                    header("Location: student_dashboard.php");
                } else {
                    header("Location: faculty_dashboard.php");
                }
                exit();
            } else {
                echo "<script>alert('❌ User type mismatch!'); window.location.href='index.php';</script>";
            }
        } else {
            echo "<script>alert('❌ Incorrect password!'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('❌ User not found!'); window.location.href='index.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
