<?php
session_start();

$file_users = "taekwondo_users.txt";
if (!file_exists($file_users)) file_put_contents($file_users, json_encode([]));
$users = json_decode(file_get_contents($file_users), true);

// LOGOUT
if(isset($_GET['logout'])){
    session_destroy();
    header("Location: login.php");
    exit;
}

// LOGIN
$login_error = '';
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    if(isset($users[$username]) && $users[$username] === $password){
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit;
    } else $login_error = "Username atau password salah!";
}

// REGISTER
$register_error = '';
$register_success = '';
if(isset($_POST['register'])){
    $new_user = $_POST['new_username'];
    $new_pass = $_POST['new_password'];
    if(isset($users[$new_user])) $register_error = "Username sudah terdaftar!";
    else{
        $users[$new_user] = $new_pass;
        file_put_contents($file_users, json_encode($users, JSON_PRETTY_PRINT));
        $register_success = "Akun berhasil dibuat! Silakan login.";
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login & Register BGFC</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    margin:0; padding:0; font-family:'Poppins',sans-serif;
    min-height:100vh; display:flex; justify-content:center; align-items:center;
    background:
        linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
        url('img/taekwondo_indonesia.png') no-repeat top left,
        url('img/logo_bgfc.png') no-repeat top center,
        url('img/tulisan_bgfc.png') no-repeat top right;
    background-size: 150px auto, 150px auto, 200px auto;
    background-position: top left, top center, top right;
    background-attachment: fixed;
    background-color:#000;
}
.card {
    background-color: rgba(20,20,20,0.85);
    border-radius:10px; padding:20px; width:100%; max-width:400px; margin:10px;
    box-shadow:0 0 25px rgba(255,0,0,0.5);
}
input{background:#1c1c1c!important;color:#fff!important;border:1px solid #ff0000!important; border-radius:6px; padding:8px;}
.btn-primary{background:linear-gradient(90deg,#ff0000,#660000);border:none;}
.btn-success{background:linear-gradient(90deg,#00c853,#006400);border:none;}
.alert{background-color: rgba(255,0,0,0.6); color:#fff; border:none; border-radius:6px; padding:8px;}
.alert-success{background-color: rgba(0,200,83,0.6);}
h4{text-align:center; margin-bottom:10px; color:#ff4444; text-shadow: 0 0 5px rgba(255,0,0,0.5);}
.note{text-align:center; margin-bottom:15px; color:#ffcc00; font-weight:bold;}
.d-flex {flex-wrap:wrap; justify-content:center; gap:20px;}
</style>
</head>
<body>

<div class="d-flex">
  <!-- LOGIN -->
  <div class="card">
    <h4>Login BGFC</h4>
    <p class="note">Jika belum memiliki akun, silakan daftar terlebih dahulu.</p>
    <?php if($login_error): ?><div class="alert"><?= $login_error ?></div><?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label>Username</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
    </form>
  </div>

  <!-- REGISTER -->
  <div class="card">
    <h4>Tambah Akun Baru</h4>
    <?php if($register_error): ?>
        <div class="alert"><?= $register_error ?></div>
    <?php elseif($register_success): ?>
        <div class="alert alert-success"><?= $register_success ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label>Username</label>
        <input type="text" name="new_username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="new_password" class="form-control" required>
      </div>
      <button type="submit" name="register" class="btn btn-success w-100">Buat Akun</button>
    </form>
  </div>
</div>

</body>
</html>
