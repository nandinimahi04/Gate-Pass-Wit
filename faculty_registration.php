<?php

require_once 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $dept = mysqli_real_escape_string($conn, $_POST['dept']);
    $class_teacher = mysqli_real_escape_string($conn, $_POST['class_teacher']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    $role = "Faculty";
    $prn_no =NULL;

    $sql = "INSERT INTO user_regis (prn_no, full_name, email, password, class, dept, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssss", $prn_no, $fullname, $email, $password, $class_teacher, $dept, $role);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Faculty Registration Successful!'); window.location.href='index.php';</script>";
        } else {
            echo "Error: " . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "SQL Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
} else {
    echo "Invalid Request Method!";
}
?>
