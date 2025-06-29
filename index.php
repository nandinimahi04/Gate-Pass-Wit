<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gate Pass Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
    body {
        background: linear-gradient(135deg, #232526 0%, #414345 100%);
        font-family: 'Montserrat', sans-serif;
        color: #f3f3f3;
    }

    .card {
        margin-top: 50px;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        background: rgba(30, 30, 40, 0.85);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.10);
        backdrop-filter: blur(8px);
        color: #f3f3f3;
    }

    .navbar {
        background: rgba(44, 44, 84, 0.95);
        border-radius: 0 0 20px 20px;
        box-shadow: 0 4px 16px rgba(44, 44, 84, 0.2);
    }

    .navbar-brand,
    .nav-link {
        color: #fff !important;
        font-weight: 700;
    }

    .container {
        max-width: 800px;
    }

    .btn-primary {
        background: linear-gradient(90deg, #232526 0%, #6a82fb 100%);
        border: none;
        color: #fff;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(106, 130, 251, 0.2);
    }

    .btn-secondary {
        background: linear-gradient(90deg, #232526 0%, #fc5c7d 100%);
        border: none;
        color: #fff;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(252, 92, 125, 0.2);
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
        border-color:rgb(227, 255, 238);
        box-shadow: 0 0 0 2px #38ef7d44;
        background: rgba(40,40,50,0.95);
        color: #fff;
    }

    .form-label {
        color: #6a82fb;
        font-weight: 600;
    }

    .card h3, .card h4 {
        color:rgb(255, 255, 255);
        font-weight: 700;
    }

    .text-center a {
        color: #a18cd1;
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
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <h3>Walchand Institute of Technology, Solapur</h3>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact Us</a></li>
                    <!--<li class="nav-item"><a class="nav-link" href="#" onclick="showRegisterOptions()">Register</a></li>-->
                </ul>
            </div>
        </div>
    </nav>

    <!-- Login Page -->
    <div class="container d-flex justify-content-center">
        <div class="card p-4" style="width: 400px;">
            <h3 class="text-center">Login</h3>
            <form id="loginForm" method="POST" action="login.php">

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <input type="hidden" name="user_type" id="userType">
                <div class="d-flex justify-content-between mb-3">
                    <button type="submit" class="btn btn-primary w-50 me-2" onclick="UserType('student')">Login as
                        Student</button>
                    <button type="submit" class="btn btn-secondary w-50" onclick="UserType('faculty')">Login as
                        Faculty</button>
                    <script>
                    function UserType(type) {
                        document.getElementById("userType").value = type;
                    }
                    </script>
                </div>
                <div class="text-center">
                    <a href="#" class="text-decoration-none" onclick="showForgotPassword()">Forgot Password?</a> |
                    <a href="#" onclick="showRegisterOptions()" class="text-decoration-none">Register</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Register Options -->
    <div class="container d-flex justify-content-center mt-5 d-none" id="registerOptions">
        <div class="card p-4 text-center" style="width: 400px;">
            <h3>Select Registration Type</h3>
            <button class="btn btn-primary w-100 mt-3" onclick="showRegisterForm('student')">Register as
                Student</button>
            <button class="btn btn-secondary w-100 mt-3" onclick="showRegisterForm('faculty')">Register as
                Faculty</button>
        </div>
    </div>


    <!-- Student Registration -->
    <div class="container d-flex justify-content-center mt-5 d-none" id="studentRegister">
        <div class="card p-4" style="width: 400px;">
            <h3 class="text-center">Student Registration</h3>
            <form action="student_registration.php" method="POST">
                <div class="mb-3">
                    <label for="prn_no" class="form-label">PRN No</label>
                    <input type="text" class="form-control" id="prn_no" name="prn_no" required>
                </div>
                <div class="mb-3">
                    <label for="studentName" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="studentName" name="studentname" required>
                </div>
                <div class="mb-3">
                    <label for="studentEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="studentEmail" name="studentemail" required>
                </div>
                <div class="mb-3">
                    <label for="studentDept" class="form-label">Department</label>
                    <select class="form-control" id="studentDept" name="dept" required>
                        <option value="">Select Department</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Mechanical Engineering">Mechanical Engineering</option>
                        <option value="Electrical and Computer Engineering">Electrical and Computer Engineering</option>
                        <option value="Civil Engineering">Civil Engineering</option>
                        <option value="Electronics and Communication">Electronics and Communication</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="studentYear" class="form-label">Class</label>
                    <select class="form-control" id="studentYear" name="year" required>
                        <option value="">Select Year</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="studentPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="studentPassword" name="studentpassword" required>
                </div>
                <button type="submit" class="btn btn-success w-100" name="submit">Register</button>
            </form>



        </div>
    </div>

    <!-- Faculty Register Page -->
    <div class="container d-flex justify-content-center mt-5 d-none" id="facultyRegister">
        <div class="card p-4" style="width: 400px;">
            <h3 class="text-center">Faculty Registration</h3>
            <form action="faculty_registration.php" method="POST">
                <div class="mb-3">
                    <label for="facultyName" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="facultyName" name="fullname" required>
                </div>
                <div class="mb-3">
                    <label for="facultyEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="facultyEmail" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="facultyDept" class="form-label">Department</label>
                    <select class="form-control" id="facultyDept" name="dept" required>
                        <option value="">Select Department</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Mechanical Engineering">Mechanical Engineering</option>
                        <option value="Electrical and Computer Engineering">Electrical and Computer Engineering</option>
                        <option value="Civil Engineering">Civil Engineering</option>
                        <option value="Electronics and Communication">Electronics and Communication</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="class_teacher" class="form-label">Class: </label>
                    <select class="form-control" id="class_teacher" name="class_teacher" required>
                        <option value="">Select Year</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="facultyPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="facultyPassword" name="password" required>
                </div>
                <button type="submit" class="btn btn-success w-100" onclick="registerUser('faculty')">Register</button>
            </form>
        </div>
    </div>

    <!-- Forgot password Page -->
    <div class="container d-flex justify-content-center mt-5 d-none" id="forgotPassword">
        <div class="card p-4" style="width: 400px;">
            <h3 class="text-center">Forgot Password</h3>
            <form id="forgotPasswordForm" method="POST" action="forgot_password.php">
                <div class="mb-3">
                    <label for="forgotEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="forgotEmail" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="forgotPassword1" class="form-label">Enter New Password</label>
                    <input type="password" class="form-control" id="forgotPassword1" name="password1" required minlength="6">
                </div>
                <div class="mb-3">
                    <label for="forgotPassword2" class="form-label">Re-enter New Password</label>
                    <input type="password" class="form-control" id="forgotPassword2" name="password2" required minlength="6">
                    <div class="invalid-feedback">Passwords do not match.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" id="captchaLabel"></label>
                    <input type="text" class="form-control" id="captchaInput" name="captcha" required>
                    <input type="hidden" id="captchaAnswer" name="captcha_answer">
                    <div class="invalid-feedback">Incorrect answer to the CAPTCHA.</div>
                </div>
                <button type="submit" class="btn btn-success w-100">Submit</button>
            </form>
        </div>
    </div>


    <div class="container mt-5" id="contact">
        <h2>Contact Us</h2>
        <p>If you have any queries, reach out to us at:</p>
        <p>Email: support@witcollege.com</p>
        <p>Phone: +91 7020987866</p>
    </div>
<script>
    function showRegisterOptions() {
        document.getElementById("registerOptions").classList.remove("d-none");
        document.getElementById("studentRegister").classList.add("d-none");
        document.getElementById("facultyRegister").classList.add("d-none");
    }

    function showRegisterForm(type) {
        document.getElementById("registerOptions").classList.add("d-none");
        if (type === 'student') {
            document.getElementById("studentRegister").classList.remove("d-none");
            document.getElementById("facultyRegister").classList.add("d-none");
        } else {
            document.getElementById("facultyRegister").classList.remove("d-none");
            document.getElementById("studentRegister").classList.add("d-none");
        }
    }

    function registerUser(type) {
        alert(`${type.charAt(0).toUpperCase() + type.slice(1)} Registration Successful! Please Login.`);
    }

    // Add input validation for login and registration forms
    function validateInput(input, type) {
        if (type === 'email') {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(input.value.trim());
        } else if (type === 'password') {
            return input.value.trim().length >= 6;
        } else if (type === 'text') {
            return input.value.trim().length > 0;
        }
        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Login form validation
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                let valid = true;
                const email = loginForm.querySelector('#email');
                const password = loginForm.querySelector('#password');
                if (!validateInput(email, 'email')) {
                    email.classList.add('is-invalid');
                    valid = false;
                } else {
                    email.classList.remove('is-invalid');
                    email.classList.add('is-valid');
                }
                if (!validateInput(password, 'password')) {
                    password.classList.add('is-invalid');
                    valid = false;
                } else {
                    password.classList.remove('is-invalid');
                    password.classList.add('is-valid');
                }
                if (!valid) {
                    e.preventDefault();
                }
            });
        }
        // Registration forms validation (student & faculty)
        ['studentRegister', 'facultyRegister'].forEach(function(formId) {
            const regForm = document.querySelector(`#${formId} form`);
            if (regForm) {
                regForm.addEventListener('submit', function(e) {
                    let valid = true;
                    regForm.querySelectorAll('input, select').forEach(function(input) {
                        let type = input.type === 'email' ? 'email' : (input.type === 'password' ? 'password' : 'text');
                        if (!validateInput(input, type)) {
                            input.classList.add('is-invalid');
                            valid = false;
                        } else {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                        }
                    });
                    if (!valid) {
                        e.preventDefault();
                    }
                });
            }
        });
        // Forgot password form validation
        const forgotForm = document.getElementById('forgotPasswordForm');
        if (forgotForm) {
            forgotForm.addEventListener('submit', function(e) {
                let valid = true;
                const email = forgotForm.querySelector('#forgotEmail');
                const pass1 = forgotForm.querySelector('#forgotPassword1');
                const pass2 = forgotForm.querySelector('#forgotPassword2');
                const captchaInput = forgotForm.querySelector('#captchaInput');
                const captchaAnswer = forgotForm.querySelector('#captchaAnswer');
                if (!validateInput(email, 'email')) {
                    email.classList.add('is-invalid');
                    valid = false;
                } else {
                    email.classList.remove('is-invalid');
                    email.classList.add('is-valid');
                }
                if (!validateInput(pass1, 'password')) {
                    pass1.classList.add('is-invalid');
                    valid = false;
                } else {
                    pass1.classList.remove('is-invalid');
                    pass1.classList.add('is-valid');
                }
                if (pass1.value !== pass2.value || pass2.value.length < 6) {
                    pass2.classList.add('is-invalid');
                    valid = false;
                } else {
                    pass2.classList.remove('is-invalid');
                    pass2.classList.add('is-valid');
                }
                if (captchaInput && captchaAnswer && captchaInput.value.trim() != captchaAnswer.value) {
                    captchaInput.classList.add('is-invalid');
                    valid = false;
                } else if (captchaInput) {
                    captchaInput.classList.remove('is-invalid');
                    captchaInput.classList.add('is-valid');
                }
                if (!valid) {
                    e.preventDefault();
                }
            });
        }
        // CAPTCHA for forgot password
        const captchaLabel = document.getElementById('captchaLabel');
        const captchaInput = document.getElementById('captchaInput');
        const captchaAnswer = document.getElementById('captchaAnswer');
        if (captchaLabel && captchaInput && captchaAnswer) {
            // Generate two random numbers
            const a = Math.floor(Math.random() * 10) + 1;
            const b = Math.floor(Math.random() * 10) + 1;
            captchaLabel.textContent = `What is ${a} + ${b}?`;
            captchaAnswer.value = a + b;
        }
    });

    function showForgotPassword() {
        document.getElementById("forgotPassword").classList.remove("d-none");
        document.getElementById("registerOptions").classList.add("d-none");
        document.getElementById("studentRegister").classList.add("d-none");
        document.getElementById("facultyRegister").classList.add("d-none");
        document.querySelector('.container.d-flex.justify-content-center .card').parentElement.classList.add("d-none");
    }

 </script>


</body>

</html>