<?php
session_start();
require_once 'config.php';

// Cek status login admin
$is_admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// ... kode lainnya tetap sama ...
?>

<!-- Di navbar, perbaiki menu admin -->
<nav class="<?php
session_start();
require_once 'config.php';

// Jika sudah login, redirect ke index
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
   
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
       
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_nama'] = $admin['nama_lengkap'];
            header("Location: dashboard.php?login_success=1");
            exit();
        } else {
            $error = "Username atau password salah.";
        }
    } catch(PDOException $e) {
        $error = "Terjadi kesalahan sistem: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Admin - DesaFunds Minggiran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Login Admin Desa Minggiran</h3>
               
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
               
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
               
                <div class="text-center mt-3">
                    <a href="index.php" class="text-decoration-none">← Kembali ke Beranda</a>
                </div>
               
                <div class="mt-4 p-3 bg-light rounded">
                    <small class="text-muted">
                        <strong>Info Login Default:</strong><br>
                        Username: admin<br>
                        Password: password
                    </small>
                </div>
            </div>
        </div>
    </div>
   
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>navbar navbar-expand-md navbar-custom px-3">
    <!-- ... logo dan brand ... -->
    <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto mb-2 mb-md-0">
            <li class="nav-item"><a class="nav-link active" href="index.php">Beranda</a></li>
            <li class="nav-item"><a class="nav-link" href="apbdes.php">APBDes</a></li>
            <li class="nav-item"><a class="nav-link" href="kegiatan.php">Kegiatan</a></li>
            <li class="nav-item"><a class="nav-link" href="forum.php">Forum</a></li>
            <li class="nav-item">
                <?php if (!$is_admin_logged_in): ?>
                    <a href="admin_login.php" class="btn btn-outline-light btn-sm ms-3">Login Admin</a>
                <?php else: ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-light btn-sm ms-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Admin Panel
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="dashboard.php">Dashboard Admin</a></li>
                            <li><a class="dropdown-item" href="admin_apbdes.php">Kelola APBDes</a></li>
                            <li><a class="dropdown-item" href="admin_kegiatan.php">Kelola Kegiatan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="admin_logout.php">Logout</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </li>
        </ul>
    </div>
</nav>