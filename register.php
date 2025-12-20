<?php
session_start();

// Opsional: hanya admin yang boleh menambah akun
// Kalau mau semua user bisa daftar, hapus blok ini
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Finance System</title>
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

        /* Card */
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 10;
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h3 {
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 10px;
        }

        .form-control,
        .form-select {
            border: 2px solid #e0e0e0;
            padding: 12px 15px;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #1a237e;
            box-shadow: 0 0 0 4px rgba(26, 35, 126, 0.1);
        }

        .btn-register {
            background: linear-gradient(135deg, #4caf50 0%, #009688 100%);
            border: none;
            color: white;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
            color: white;
        }

        .alert-info-custom {
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 12px;
            padding: 15px;
            border: 1px solid #bbdefb;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        .back-link {
            color: #7b809a;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #1a237e;
        }
    </style>
</head>

<body>

    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="container d-flex justify-content-center">
        <div class="register-card animate__animated animate__fadeInUp">
            <div class="register-header">
                <h3>Create New Account</h3>
                <p class="text-muted">Register a new user to the system.</p>
            </div>

            <?php if (isset($_GET['msg'])) { ?>
                <div class="alert-info-custom mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <?= $_GET['msg']; ?>
                </div>
            <?php } ?>

            <form action="proses_register.php" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">Username</label>
                    <input name="username" type="text" class="form-control" placeholder="Choose a username" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">Password</label>
                    <input name="password" type="password" class="form-control" placeholder="Create a strong password"
                        required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted">Role Akses</label>
                    <select name="role" class="form-select">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-register w-100 mb-3">
                    <i class="fas fa-user-plus me-2"></i> Create Account
                </button>

                <div class="text-center">
                    <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left me-1"></i> Back to
                        Dashboard</a>
                </div>

            </form>
        </div>
    </div>

</body>

</html>