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
    <title>Create Account</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow">
                <div class="card-body">
                    <h4 class="text-center mb-3">Create New Account</h4>

                    <?php if(isset($_GET['msg'])) { ?>
                        <div class="alert alert-info">
                            <?= $_GET['msg']; ?>
                        </div>
                    <?php } ?>

                    <form action="proses_register.php" method="POST">
                        
                        <div class="mb-3">
                            <label>Username</label>
                            <input name="username" type="text" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input name="password" type="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Role Akses</label>
                            <select name="role" class="form-control">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Create</button>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
