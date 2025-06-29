<?php
include('connection.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'faculty') {
    echo "<script>alert('Unauthorized access! Redirecting to login.'); window.location.href='index.php';</script>";
    exit();
}

$faculty_id = $_SESSION['user_id'];

// Fetch faculty info (with class and dept)
$stmt = $conn->prepare("SELECT full_name, email, class AS faculty_class, dept AS faculty_dept FROM user_regis WHERE id = ?");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$faculty_result = $stmt->get_result();
$faculty = $faculty_result->fetch_assoc();
$stmt->close();

$faculty_class = $faculty['faculty_class'];
$faculty_dept = $faculty['faculty_dept'];

// Fetch gate pass requests by matching class and dept
function fetchRequests($conn, $status, $faculty_class, $faculty_dept) {
    $sql = "SELECT gp.id, ur.full_name AS student_name, ur.class AS student_year, ur.dept AS student_dept,
                   ur.email AS student_email, gp.reason, gp.comments 
            FROM gate_pass gp 
            LEFT JOIN user_regis ur ON gp.student_id = ur.id 
            WHERE gp.status = ? 
              AND ur.class = ? 
              AND ur.dept = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $status, $faculty_class, $faculty_dept);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}


$pendingRequests = fetchRequests($conn, 0, $faculty_class, $faculty_dept);
$approvedRequests = fetchRequests($conn, 1, $faculty_class, $faculty_dept);
$rejectedRequests = fetchRequests($conn, 2, $faculty_class, $faculty_dept);

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Faculty Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #232526 0%, #414345 100%);
        font-family: 'Montserrat', sans-serif;
        color: #f3f3f3;
    }
    .sidebar {
        height: 100vh;
        width: 250px;
        background: rgba(44, 44, 84, 0.95);
        color: white;
        padding: 20px;
        position: fixed;
        top: 0; left: 0;
        overflow-y: auto;
        border-radius: 0 20px 20px 0;
        box-shadow: 0 8px 32px 0 rgba(44, 44, 84, 0.2);
        backdrop-filter: blur(8px);
    }
    .sidebar a {
        color: #fff;
        text-decoration: none;
        display: block;
        padding: 10px;
        border-radius: 10px;
        font-weight: 600;
        transition: background 0.2s;
    }
    .sidebar a.active, .sidebar a:hover {
        background: rgba(106,130,251,0.3);
    }
    .main-content {
        margin-left: 270px;
        padding: 40px 20px;
    }
    .card {
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.17);
        background: rgba(30, 30, 40, 0.85);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.10);
        backdrop-filter: blur(8px);
        color: #f3f3f3;
    }
    .btn-success {
        background: linear-gradient(90deg, #6a82fb 0%, #fc5c7d 100%);
        border: none;
        color: #fff;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(106, 130, 251, 0.2);
    }
    .btn-danger {
        background: linear-gradient(90deg, #fc5c7d 0%, #6a82fb 100%);
        border: none;
        color: #fff;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(252, 92, 125, 0.2);
    }
    .btn-dark {
        background: linear-gradient(90deg, #232526 0%, #414345 100%);
        border: none;
        color: #fff;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(35, 37, 38, 0.2);
    }
    .form-control {
        background: rgba(40,40,50,0.8);
        border-radius: 10px;
        border: 1px solid #6a82fb;
        color: #f3f3f3;
    }
    .form-control:focus {
        border-color: #38ef7d;
        box-shadow: 0 0 0 2px #38ef7d44;
        background: rgba(40,40,50,0.95);
        color: #fff;
    }
    .form-label {
        color: #6a82fb;
        font-weight: 600;
    }
    .card h3, .card h4 {
        color: #6a82fb;
        font-weight: 700;
    }
    .alert {
        border-radius: 12px;
        font-weight: 600;
        text-align: center;
        margin: 10px auto;
        max-width: 400px;
    }
    .alert-success {
        background: linear-gradient(90deg, #6a82fb 0%, #fc5c7d 100%);
        color: #fff;
        border: none;
    }
    .alert-danger {
        background: linear-gradient(90deg, #fc5c7d 0%, #6a82fb 100%);
        color: #fff;
        border: none;
    }
    .alert-warning {
        background: linear-gradient(90deg, #f7971e 0%, #ffd200 100%);
        color: #232526;
        border: none;
    }
    .is-invalid {
        border-color: #fc5c7d !important;
        box-shadow: 0 0 0 2px #fc5c7d44 !important;
    }
    .is-valid {
        border-color: #6a82fb !important;
        box-shadow: 0 0 0 2px #6a82fb44 !important;
    }
    .invalid-feedback, .valid-feedback {
        display: block;
        font-size: 0.95em;
        margin-top: 0.25rem;
        text-align: left;
    }
</style>
</head>
<body>

<div class="sidebar">
    <h4>Faculty Dashboard</h4>
    <p>Hello, <strong><?php echo htmlspecialchars($faculty['full_name']); ?></strong></p>
    <a href="#pendingRequests" onclick="showSection('pendingRequests')">View Pending Requests</a>
    <a href="#approvedRequests" onclick="showSection('approvedRequests')">Approved Requests</a>
    <a href="#rejectedRequests" onclick="showSection('rejectedRequests')">Rejected Requests</a>
    <a href="#profile" onclick="showSection('profile')">Profile</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main-content">
    <h2>Welcome, <?php echo htmlspecialchars($faculty['full_name']); ?></h2>
    <div id="messageBox" class="alert d-none"></div>

    <!-- Pending Requests -->
    <div id="pendingRequests" class="card p-4 d-none">
        <h4>Pending Requests</h4>
        <table class="table table-bordered">
            <thead><tr><th>Student Name</th><th>Year</th><th>Department</th><th>Reason</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if(empty($pendingRequests)): ?>
                <tr><td colspan="4" class="text-center">No pending requests.</td></tr>
            <?php else: ?>
            <?php foreach ($pendingRequests as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['student_year']); ?></td>
                    <td><?php echo htmlspecialchars($row['student_dept']); ?></td>
                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                    <td>
                        <button class="btn btn-success btn-sm" onclick="updateRequest(<?php echo $row['id']; ?>, 'approved')">Approve</button>
                        <button class="btn btn-danger btn-sm" onclick="updateRequest(<?php echo $row['id']; ?>, 'rejected')">Reject</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Approved Requests -->
    <div id="approvedRequests" class="card p-4 d-none">
        <h4>Approved Requests</h4>
        <table class="table table-bordered">
            <thead><tr><th>Student Name</th><th>Year</th><th>Department</th><th>Reason</th></tr></thead>
            <tbody>
            <?php if(empty($approvedRequests)): ?>
                <tr><td colspan="3" class="text-center">No approved requests.</td></tr>
            <?php else: ?>
            <?php foreach ($approvedRequests as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['student_year']); ?></td>
                    <td><?php echo htmlspecialchars($row['student_dept']); ?></td>
                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Rejected Requests -->
    <div id="rejectedRequests" class="card p-4 d-none">
        <h4>Rejected Requests</h4>
        <table class="table table-bordered">
            <thead><tr><th>Student Name</th><th>Year</th><th>Department</th><th>Reason</th><th>Comments</th></tr></thead>
            <tbody>
            <?php if(empty($rejectedRequests)): ?>
                <tr><td colspan="4" class="text-center">No rejected requests.</td></tr>
            <?php else: ?>
            <?php foreach ($rejectedRequests as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['student_year']); ?></td>
                    <td><?php echo htmlspecialchars($row['student_dept']); ?></td>
                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                    <td><?php echo isset($row['comments']) ? htmlspecialchars($row['comments']) : '-'; ?></td>
                </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Profile -->
    <div id="profile" class="card p-4 d-none">
        <h4>Profile</h4>
        <form action="update_profile.php" method="POST">
            <div class="mb-3">
                <label for="facultyName" class="form-label">Name</label>
                <input type="text" class="form-control" id="facultyName" name="name" value="<?php echo htmlspecialchars($faculty['full_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($faculty['email']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<script>
function showSection(sectionId) {
    document.querySelectorAll('.card').forEach(card => card.classList.add('d-none'));
    const section = document.getElementById(sectionId);
    if (section) section.classList.remove('d-none');

    document.querySelectorAll('.sidebar a').forEach(link => link.classList.remove('active'));
    const activeLink = document.querySelector(`.sidebar a[href="#${sectionId}"]`);
    if (activeLink) activeLink.classList.add('active');
}
function updateRequest(requestId, status) {
    let formData = new FormData();
    formData.append("request_id", requestId);
    formData.append("status", status);

    if (status === 'rejected') {
        const comment = prompt("Please enter a reason for rejection:");
        if (comment === null || comment.trim() === "") {
            alert("Rejection cancelled: comment required.");
            return;
        }
        formData.append("comments", comment);
    }

    fetch("faculty_approve.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === "success") {
            showMessage(`Request ${status} successfully!`, "success");
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage("Error: " + data, "danger");
        }
    })
    .catch(error => {
        showMessage("Error: " + error, "danger");
    });
}

function showMessage(msg, type) {
    const box = document.getElementById('messageBox');
    box.className = `alert alert-${type}`;
    box.textContent = msg;
    box.classList.remove('d-none');
    setTimeout(() => box.classList.add('d-none'), 3000);
}

document.addEventListener('DOMContentLoaded', () => {
    // Show the initial section or default to pendingRequests
    const initialSection = window.location.hash ? window.location.hash.substring(1) : 'pendingRequests';
    showSection(initialSection);
});

// Add input validation for profile update form
function validateInput(input, type) {
    if (type === 'email') {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(input.value.trim());
    } else if (type === 'text') {
        return input.value.trim().length > 0;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    // Profile form validation
    const profileForm = document.querySelector('#profile form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            let valid = true;
            const name = profileForm.querySelector('#facultyName');
            const email = profileForm.querySelector('#email');
            if (!validateInput(name, 'text')) {
                name.classList.add('is-invalid');
                valid = false;
            } else {
                name.classList.remove('is-invalid');
                name.classList.add('is-valid');
            }
            if (!validateInput(email, 'email')) {
                email.classList.add('is-invalid');
                valid = false;
            } else {
                email.classList.remove('is-invalid');
                email.classList.add('is-valid');
            }
            if (!valid) {
                e.preventDefault();
            }
        });
    }
});
</script>

</body>
</html>
