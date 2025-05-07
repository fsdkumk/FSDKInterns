<?php
session_start();
include("db_connect.php");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        if ($password == $row['password']) {
            if (isset($_SESSION['user_id']) && $_SESSION['email'] !== $row['email']) {
                session_destroy();
                session_start();
            }

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['name'];

            $_SESSION['isAdmin'] = $row['role'] == "admin";

            switch ($row['role']) {
                case "admin":
                    header("Location: admin_dashboard.php");
                    break;
                case "lecturer":
                    header("Location: lecturer_dashboard.php");
                    break;
                case "student":
                    header("Location: student_dashboard.php");
                    break;
                default:
                    echo "<script>alert('Invalid role!');</script>";
                    break;
            }
            exit();
        } else {
            $_SESSION['login_error'] = 'Invalid Password';
        }
    } else {
        $_SESSION['login_error'] = 'Email Not Found';
    }
    $stmt->close();
}

if (isset($_POST['send_dummy_password'])) {
    $forgotEmail = $_POST['forgot_email'];
    $dummyPassword = 'dummy1234';

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $forgotEmail);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->bind_param("ss", $dummyPassword, $forgotEmail);
        $update->execute();

        echo "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Password Reset!',
                    text: 'Dummy password sent: $dummyPassword',
                    confirmButtonColor: '#064D51'
                });
            });
        </script>
        ";
    } else {
        echo "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Email Not Found!',
                    text: 'Please enter a registered email.',
                    confirmButtonColor: '#064D51'
                });
            });
        </script>
        ";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(to right, #C0F2F3, #81D8D0, #48CBC5, #1E9C99, #1E9C99, #064D51);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 60px 60px 40px;
            width: 100%;
            max-width: 600px;
            text-align: center;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            position: relative;
        }
        .logo-top-left {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 60px;
        }
        .avatar {
            background-color: #0c2840;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            position: absolute;
            top: -50px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .avatar i {
            color: white;
            font-size: 40px;
        }
        form {
            margin-top: 80px;
        }
        .form-control, .input-group-text {
            background-color: rgba(0, 0, 0, 0.3);
            border: none;
            color: white;
            font-size: 1rem;
        }
        .form-control::placeholder {
            color: white;
            opacity: 1;
        }
        .input-group {
            margin-bottom: 18px;
        }
        .input-group-text i {
            color: white;
        }
        .btn-login {
            background: linear-gradient(to right, #1E9C99, #064D51);
            color: white;
            border: none;
            font-weight: bold;
            padding: 14px 0;
            width: 100%;
            max-width: 400px;
            margin-top: -20px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .forgot-password {
            font-size: 0.85rem;
            color: white;
            text-align: right;
            margin-top: 12px;
        }
        .footer {
            margin-top: 20px;
            color: white;
            font-size: 0.85rem;
            text-align: center;
        }

            body.swal2-shown {
            overflow: hidden !important;
            height: 100vh !important;
        }

    </style>
</head>
<body>
<?php
if (isset($_SESSION['login_error'])) {
    echo "
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: '" . $_SESSION['login_error'] . "',
                text: 'Please check your credentials and try again.',
                confirmButtonColor: '#064D51'
            });
        });
    </script>
    ";
    unset($_SESSION['login_error']); // Clear it after showing
}
?>
    <div class="text-center">
        <div class="login-container mx-auto">
            <img src="image/logoumk.png" alt="UMK Logo" class="logo-top-left">
            <div class="avatar">
                <i class="fas fa-user"></i>
            </div>
            <form id="loginForm" method="POST" action="">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="Email ID" required>
                </div>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                    <span class="input-group-text toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </span>
                </div>
                <div class="forgot-password">
                <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" style="color: white; text-decoration: none;">Forgot Password?</a>
                </div>
            </form>
        </div>
        <button type="button" class="btn btn-login mt-0" onclick="submitLogin()">LOGIN</button>
        <div class="footer">
            &copy; 2021 Universiti Malaysia Kelantan | Entrepreneur University. All Rights Reserved.
        </div>
    </div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" class="modal-content" style="border-radius: 15px;">
      <div class="modal-header">
        <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="email" name="forgot_email" class="form-control" placeholder="Enter your email" required>
      </div>
      <div class="modal-footer">
        <button type="submit" name="send_dummy_password" class="btn btn-primary w-100">Send Dummy Password</button>
      </div>
    </form>
  </div>
</div>


    <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const eyeIcon = document.getElementById("eyeIcon");
            if (password.type === "password") {
                password.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                password.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }

        function submitLogin() {
        const form = document.getElementById("loginForm");
        const email = form.email.value.trim();
        const password = form.password.value.trim();

        if (!email || !password) {
            alert("Please fill in both email and password.");
            return;
        }

        form.submit();
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
