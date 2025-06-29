<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'student') {
    echo "<script>alert('Unauthorized access! Redirecting to login.'); window.location.href='index.php';</script>";
    exit();
}

$student_id = $_SESSION['user_id'];

// Handle Profile Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_name = trim($_POST['studentName'] ?? '');
    $new_email = trim($_POST['email'] ?? '');

    if ($new_name === '' || $new_email === '') {
        $update_error = "Name and Email cannot be empty.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $update_error = "Invalid email format.";
    } else {
        $update_query = "UPDATE user_regis SET full_name = ?, email = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssi", $new_name, $new_email, $student_id);
        if ($update_stmt->execute()) {
            $update_stmt->close();
            header("Location: student_dashboard.php?update_success=1");
            exit();
        } else {
            $update_error = "Failed to update profile: " . $update_stmt->error;
            $update_stmt->close();
        }
    }
}

// Fetch updated student details
$student_query = "SELECT full_name, email, class, dept FROM user_regis WHERE id = ?";
$student_stmt = $conn->prepare($student_query);
$student_stmt->bind_param("i", $student_id);
$student_stmt->execute();
$student_data = $student_stmt->get_result()->fetch_assoc();
$student_stmt->close();

// Fetch HOD based on department
$department = $student_data['dept'] ?? '';

$hod_name = '';

switch (strtolower($department)) {
    case 'computer science':
    case 'cs':
        $hod_name = 'Prof. Ajit Mali';
        break;
    case 'information technology':
    case 'it':
        $hod_name = 'Prof. Rainey Lobo';
        break;
    case 'entc':
    case 'electronics and telecommunication':
        $hod_name = 'Prof. Rajani';
        break;
    default:
        $hod_name = 'HOD Not Assigned';
        break;
}


// Fetch all gate pass requests
$gp_query = "SELECT * FROM gate_pass WHERE student_id = ? ORDER BY request_time DESC";
$gp_stmt = $conn->prepare($gp_query);
$gp_stmt->bind_param("i", $student_id);
$gp_stmt->execute();
$gp_result = $gp_stmt->get_result();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
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
        .sidebar a:hover {
            background: rgba(106, 130, 251, 0.3);
        }
        .main-content {
            margin-left: 270px;
            padding: 20px;
        }
        .card {
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.17);
            background: rgba(30, 30, 40, 0.85);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(8px);
            color: #f3f3f3;
        }
        .btn-primary {
            background: linear-gradient(90deg, #232526 0%, #6a82fb 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(106, 130, 251, 0.2);
        }
        .btn-success {
            background: linear-gradient(90deg, #6a82fb 0%, #fc5c7d 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(106, 130, 251, 0.2);
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
    <h4>Student Dashboard</h4>
    <a href="#applyPass" onclick="showSection('applyPass')">Apply for Gate Pass</a>
    <a href="#viewStatus" onclick="showSection('viewStatus')">View Status</a>
    <a href="#pastRequests" onclick="showSection('pastRequests')">Past Requests</a>
    <a href="#profileSettings" onclick="showSection('profileSettings')">Profile Settings</a>
    <a href="index.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
</div>

<div class="main-content">
    <h2>Welcome, <?php echo htmlspecialchars($student_data['full_name'] ?? 'Student'); ?>!</h2>

    <!-- Apply for Gate Pass -->
    <div id="applyPass" class="card p-4">
        <h4>Apply for Gate Pass</h4>
        <form action="student_apply.php" method="POST">
            <div class="mb-3">
                <label for="coordinator" class="form-label">Class Coordinator</label>
                <select class="form-control" id="coordinator" name="class_coordinator" required>
                    <option value="" selected disabled>Select Class Coordinator</option>
                    <option value="Ajit Mali">Prof. Ajit Mali</option>
                    <option value="Shrutali Narker">Prof. Shrutali Narker</option>
                    <option value="S Karpe">Prof. S Karpe</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="hod" class="form-label">Head of Department</label>
                <input type="text" class="form-control" id="hod" name="hod" value="<?php echo htmlspecialchars($hod_name); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="reason" class="form-label">Reason for Exit</label>
                <input type="text" class="form-control" id="reason" name="reason" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </form>
    </div>

    <!-- View Status of Requests -->
    <div id="viewStatus" class="card p-4 d-none">
        <h4>View Status of Requests</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Requested On</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $gp_result->data_seek(0);
                while ($row = $gp_result->fetch_assoc()):
                    if ($row['status'] == 0 || $row['status'] == 2):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                    <td>
                        <?php
                        if ($row['status'] == 0) {
                            echo '<span class="badge bg-warning text-dark">Pending</span>';
                        } elseif ($row['status'] == 2) {
                            echo '<span class="badge bg-danger">Rejected</span>';
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars(date("d M Y, H:i", strtotime($row['request_time']))); ?></td>
                </tr>
                <?php
                    endif;
                endwhile;
                ?>
            </tbody>
        </table>
    </div>

    <!-- Past Requests -->
    <div id="pastRequests" class="card p-4 d-none">
        <h4>Past Approved Requests</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Requested On</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $gp_result->data_seek(0);
                while ($row = $gp_result->fetch_assoc()):
                    if ($row['status'] == 1):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                    <td><span class="badge bg-success">Approved</span></td>
                    <td><?php echo htmlspecialchars(date("d M Y, H:i", strtotime($row['request_time']))); ?></td>
                </tr>
                <?php
                    endif;
                endwhile;
                ?>
            </tbody>
        </table>
    </div>

    <!-- Profile Settings -->
    <div id="profileSettings" class="card p-4 d-none">
        <h4>Profile Settings</h4>
        <?php if (!empty($update_error)): ?>
            <div class="alert alert-danger"><?php echo $update_error; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="hidden" name="update_profile" value="1" />
            <div class="mb-3">
                <label for="studentName" class="form-label">Name</label>
                <input type="text" class="form-control" id="studentName" name="studentName" value="<?php echo htmlspecialchars($student_data['full_name']); ?>" required />
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student_data['email']); ?>" required />
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<script>
function showSection(sectionId) {
    document.querySelectorAll('.card').forEach(card => card.classList.add('d-none'));
    document.getElementById(sectionId).classList.remove('d-none');
}

const urlParams = new URLSearchParams(window.location.search);

if (urlParams.get('update_success') === '1') {
    alert("✅ Profile updated successfully!");
    history.replaceState(null, '', window.location.pathname);
}
if (urlParams.get('success') === '1') {
    alert("✅ Gate pass request submitted successfully!");
    history.replaceState(null, '', window.location.pathname);
}

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
    const profileForm = document.querySelector('#profileSettings form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            let valid = true;
            const name = profileForm.querySelector('#studentName');
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

<?php
$gp_stmt->close();
$conn->close();
?>
