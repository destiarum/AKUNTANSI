<?php
session_start();

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Finance System</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(-45deg, #1a237e, #0d47a1, #009688, #4caf50);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Floating Shapes */
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            backdrop-filter: blur(5px);
            z-index: 0;
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            top: -50px;
            left: -50px;
            animation: float 6s ease-in-out infinite;
        }

        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: 50px;
            right: -50px;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(20px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        /* Login Card */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 10;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h3 {
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #7b809a;
            font-size: 0.9rem;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            padding: 12px 15px;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #1a237e;
            box-shadow: 0 0 0 4px rgba(26, 35, 126, 0.1);
        }

        .input-group-text {
            background: white;
            border: 2px solid #e0e0e0;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #7b809a;
        }

        .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #1a237e;
        }

        .btn-login {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            border: none;
            color: white;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 35, 126, 0.3);
            color: white;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-radius: 12px;
            padding: 15px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            border: 1px solid #ffcdd2;
            display: flex;
            align-items: center;
        }
    </style>
</head>

<body>

    <!-- Animated Shapes -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="container d-flex justify-content-center">
        <div class="login-card animate__animated animate__fadeInUp">
            <div class="login-header">
                <div class="mb-3">
                    <i class="fas fa-wallet fa-3x text-primary"
                        style="background: -webkit-linear-gradient(#1a237e, #283593); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                </div>
                <h3>Welcome Back</h3>
                <p>Please enter your details to sign in.</p>
            </div>

            <?php if (isset($_GET['error'])) { ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $_GET['error']; ?>
                </div>
            <?php } ?>

            <form action="proses_login.php" method="POST">
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted ms-1">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Enter your username"
                            required>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="form-label fw-bold small text-muted ms-1">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-login w-100">
                    Sign In <i class="fas fa-arrow-right ms-2"></i>
                </button>

                <div class="text-center mt-4">
                    <p class="small text-muted mb-0">Don't have an account? <a href="register.php"
                            class="fw-bold text-primary text-decoration-none">Create Account</a></p>
                </div>
            </form>
        </div>
    </div>

</body>

</html>