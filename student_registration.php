<?php
require_once 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize and escape inputs
    $prn_no = mysqli_real_escape_string($conn, $_POST['prn_no']);
    $studentname = mysqli_real_escape_string($conn, $_POST['studentname']);
    $studentemail = mysqli_real_escape_string($conn, $_POST['studentemail']);
    $dept = mysqli_real_escape_string($conn, $_POST['dept']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $studentpassword = password_hash($_POST['studentpassword'], PASSWORD_DEFAULT);
    $role = "Student";

    // Check if email already exists
    $check_sql = "SELECT id FROM user_regis WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $studentemail);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        echo "<script>alert('❌ Email already registered!'); window.location.href='register_form.php';</script>";
        exit();
    }

    mysqli_stmt_close($check_stmt);

    // Insert student into DB
    $sql = "INSERT INTO user_regis (prn_no, full_name, email, password, class, dept, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssss", $prn_no, $studentname, $studentemail, $studentpassword, $year, $dept, $role);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('✅ Registration Successful!'); window.location.href='index.php';</script>";
        } else {
            echo "❌ Error executing query: " . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "❌ SQL Prepare Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
} else {
    echo "❌ Invalid Request Method!";
}
?>
