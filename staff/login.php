<?php
require_once __DIR__ . '/../includes/functions.php';
if (logged_in_staff()) redirect('/staff/index.php');
if (logged_in_admin()) redirect('/admin/index.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); $email=trim($_POST['email']??'');$password=$_POST['password']??'';
 $stmt=db()->prepare("SELECT id,name,email,password_hash,role,is_active FROM admins WHERE email=? AND role='staff' LIMIT 1");$stmt->execute([$email]);$user=$stmt->fetch();
 if($user && (int)$user['is_active']===1 && password_verify($password,$user['password_hash'])){session_regenerate_id(true);$_SESSION['user_id']=$user['id'];$_SESSION['user_role']='staff';$_SESSION['user_name']=$user['name'];redirect('/staff/index.php');}
 $error='Incorrect staff email or password, or this account is inactive.';
}
$pageTitle='Staff sign in';include __DIR__ . '/../includes/header.php';
?>
<section class="admin-login container"><div class="admin-login-card"><p class="eyebrow">MMS HOSTEL TEAM</p><h1>Staff <em>sign in.</em></h1><p class="muted">Access reservations and guest check-in tools.</p><?php if($error): ?><div class="notice notice-warning"><?= e($error) ?></div><?php endif; ?><form method="post" class="booking-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label for="email">Staff email</label><input id="email" name="email" type="email" required autocomplete="username"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"><button class="button button-dark full-width">Sign in as staff</button></form><p class="fine-print">Need access? Ask the MMS Hostel administrator to create your staff account.</p><a class="text-link" href="<?= BASE_URL ?>/admin/login.php">Admin login ↗</a></div></section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
