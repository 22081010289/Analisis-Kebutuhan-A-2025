<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    
    if ($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if email already exists
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $query = "INSERT INTO users (name, email, password, address, phone) 
                     VALUES (:name, :email, :password, :address, :phone)";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":address", $address);
            $stmt->bindParam(":phone", $phone);
            
            if ($stmt->execute()) {
                $success = "Pendaftaran berhasil! Silakan login.";
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Swalayan Bagus</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: url('assets/img/login-bg.png') center center/cover no-repeat;
            min-height: 100vh;
            position: relative;
        }
        .bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.25);
            z-index: 0;
        }
        .register-card {
            position: relative;
            z-index: 1;
            max-width: 900px;
            margin: 60px auto;
            background: #fff;
            border-radius: 3rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            padding: 3rem 2.5rem 2.5rem 2.5rem;
        }
        .register-left {
            flex: 1 1 340px;
            min-width: 320px;
        }
        .register-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: #222;
        }
        .register-subtitle {
            color: #f44336;
            font-size: 1.15rem;
            margin-bottom: 2.2rem;
        }
        .register-form label {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.3rem;
        }
        .register-form input[type="text"],
        .register-form input[type="email"],
        .register-form input[type="password"] {
            width: 100%;
            border-radius: 0.5rem;
            border: 1.5px solid #bbb;
            padding: 0.9rem 1.1rem;
            font-size: 1.1rem;
            margin-bottom: 1.3rem;
        }
        .register-form input::placeholder {
            color: #aaa;
            font-size: 1.05rem;
        }
        .register-form .btn-register {
            width: 100%;
            background: #2563eb;
            color: #fff;
            font-size: 1.35rem;
            font-weight: 700;
            border-radius: 0.5rem;
            padding: 0.9rem 0;
            border: none;
            margin-top: 0.5rem;
            margin-bottom: 0.7rem;
            transition: background 0.2s;
        }
        .register-form .btn-register:hover {
            background: #1d4ed8;
        }
        .register-link {
            text-align: center;
            font-size: 1.05rem;
        }
        .register-link a {
            color: #1976d2;
            text-decoration: none;
            font-weight: 500;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .register-logo {
            flex: 0 0 260px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-logo img {
            width: 180px;
            max-width: 100%;
        }
        @media (max-width: 900px) {
            .register-card {
                flex-direction: column;
                padding: 2rem 1.2rem;
            }
            .register-logo {
                margin-top: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="register-card">
        <div class="register-left">
            <div class="register-title">Daftar Sekarang</div>
            <div class="register-subtitle">Mari persiapkan akun anda terlebih dahulu</div>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form class="register-form" method="POST" action="" autocomplete="off">
                <label for="name">Masukkan nama</label>
                <input type="text" id="name" name="name" placeholder="Nama anda" required>
                <label for="email">Masukkan email</label>
                <input type="email" id="email" name="email" placeholder="Email anda" required>
                <label for="password">Masukkan kata sandi</label>
                <input type="password" id="password" name="password" placeholder="Kata sandi anda" required>
                <button type="submit" class="btn-register">Buat Akun</button>
            </form>
            <div class="register-link">
                Sudah punya akun? <a href="login.php">Masuk</a>
            </div>
        </div>
        <div class="register-logo">
            <img src="assets/img/logo.png" alt="Logo Swalayan Bagus">
        </div>
    </div>
</body>
</html> 