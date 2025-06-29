<?php
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password1 = $_POST['password1'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $captcha = trim($_POST['captcha'] ?? '');
    $captcha_answer = trim($_POST['captcha_answer'] ?? '');

    if ($email === '' || $password1 === '' || $password2 === '') {
        echo "<script>alert('❌ All fields are required!'); window.location.href='index.php';</script>";
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('❌ Invalid email format!'); window.location.href='index.php';</script>";
        exit();
    }
    if ($password1 !== $password2 || strlen($password1) < 6) {
        echo "<script>alert('❌ Passwords do not match or are too short!'); window.location.href='index.php';</script>";
        exit();
    }
    if ($captcha === '' || $captcha_answer === '' || $captcha != $captcha_answer) {
        echo "<script>alert('❌ Incorrect CAPTCHA answer!'); window.location.href='index.php';</script>";
        exit();
    }

    // Account lockout logic
    $lockout_file = __DIR__ . '/lockout_forgot.json';
    $lockout_data = file_exists($lockout_file) ? json_decode(file_get_contents($lockout_file), true) : [];
    $now = time();
    $lockout_key = md5(strtolower($email));
    if (isset($lockout_data[$lockout_key])) {
        $entry = $lockout_data[$lockout_key];
        if (isset($entry['locked_until']) && $now < $entry['locked_until']) {
            $wait = ceil(($entry['locked_until'] - $now) / 60);
            echo "<script>alert('❌ Too many failed attempts. Please try again in $wait minutes.'); window.location.href='index.php';</script>";
            exit();
        }
    }

    $check_sql = "SELECT id FROM user_regis WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows === 1) {
        $new_password = password_hash($password1, PASSWORD_DEFAULT);
        $update_sql = "UPDATE user_regis SET password = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $new_password, $email);
        if ($update_stmt->execute()) {
            // Reset lockout counter
            unset($lockout_data[$lockout_key]);
            file_put_contents($lockout_file, json_encode($lockout_data));
            // Send email notification
            $to = $email;
            $subject = 'Your Password Has Been Changed';
            $message = "Hello,\n\nYour password for the WIT Pass portal has been changed. If you did not request this change, please contact support immediately.\n\nRegards,\nWIT Pass Team";
            $headers = 'From: support@witcollege.com' . "\r\n" .
                       'Reply-To: support@witcollege.com' . "\r\n" .
                       'X-Mailer: PHP/' . phpversion();
            @mail($to, $subject, $message, $headers);
            echo "<script>alert('✅ Password updated successfully! Please login.'); window.location.href='index.php';</script>";
        } else {
            // Failed update, count as failed attempt
            $lockout_data[$lockout_key]['fails'][] = $now;
            // Keep only fails in the last hour
            $lockout_data[$lockout_key]['fails'] = array_filter($lockout_data[$lockout_key]['fails'], function($t) use ($now) { return $t > $now - 3600; });
            if (count($lockout_data[$lockout_key]['fails']) >= 5) {
                $lockout_data[$lockout_key]['locked_until'] = $now + 3600;
            }
            file_put_contents($lockout_file, json_encode($lockout_data));
            echo "<script>alert('❌ Error updating password!'); window.location.href='index.php';</script>";
        }
        $update_stmt->close();
    } else {
        // Failed attempt, count as failed
        $lockout_data[$lockout_key]['fails'][] = $now;
        // Keep only fails in the last hour
        $lockout_data[$lockout_key]['fails'] = array_filter($lockout_data[$lockout_key]['fails'], function($t) use ($now) { return $t > $now - 3600; });
        if (count($lockout_data[$lockout_key]['fails']) >= 5) {
            $lockout_data[$lockout_key]['locked_until'] = $now + 3600;
        }
        file_put_contents($lockout_file, json_encode($lockout_data));
        echo "<script>alert('❌ Email not found!'); window.location.href='index.php';</script>";
    }
    $check_stmt->close();
    $conn->close();
} else {
    header('Location: index.php');
    exit();
}
?> 