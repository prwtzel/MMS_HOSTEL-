<?php
require_once __DIR__ . '/../includes/functions.php';require_admin();$pdo=db();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$action=$_POST['action']??'';
 if($action==='create'){
  $name=trim($_POST['name']??'');$email=trim($_POST['email']??'');$password=$_POST['password']??'';
  if(mb_strlen($name)<2||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<10){$error='Enter a name, valid email, and a password with at least 10 characters.';}
  else{try{$q=$pdo->prepare("INSERT INTO admins(name,email,password_hash,role,is_active) VALUES(?,?,?,'staff',1)");$q->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);redirect('/admin/staff.php?created=1');}catch(PDOException $ex){$error='Could not create account. This email may already be in use.';}}
 }elseif($action==='toggle'){$id=(int)($_POST['staff_id']??0);$q=$pdo->prepare("UPDATE admins SET is_active=1-is_active WHERE id=? AND role='staff'");$q->execute([$id]);redirect('/admin/staff.php');}
}
$staff=$pdo->query("SELECT id,name,email,is_active,created_at FROM admins WHERE role='staff' ORDER BY name")->fetchAll();
$pageTitle='Manage staff';include __DIR__ . '/../includes/header.php';
?>
<section class="admin-page container"><div class="admin-heading"><div><p class="eyebrow">ADMINISTRATOR ONLY</p><h1>Manage <em>your team.</em></h1><p class="muted">Create staff logins and deactivate accounts when access is no longer needed.</p></div><a class="button button-outline" href="<?= BASE_URL ?>/admin/index.php">← Dashboard</a></div>
<?php if(isset($_GET['created'])): ?><div class="notice notice-success">Staff account created successfully.</div><?php endif; ?><?php if($error): ?><div class="notice notice-warning"><?= e($error) ?></div><?php endif; ?>
<div class="admin-room-layout"><div class="admin-table-wrap"><div class="table-heading"><h2>Staff accounts</h2></div><div class="table-scroll"><table><thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach($staff as $person): ?><tr><td><?= e($person['name']) ?></td><td><?= e($person['email']) ?></td><td><?= $person['is_active']?'Active':'Inactive' ?></td><td><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="staff_id" value="<?= (int)$person['id'] ?>"><button class="small-button"><?= $person['is_active']?'Deactivate':'Activate' ?></button></form></td></tr><?php endforeach; ?><?php if(!$staff): ?><tr><td colspan="4">No staff accounts created yet.</td></tr><?php endif; ?></tbody></table></div></div>
<div class="add-room-card"><p class="eyebrow">NEW TEAM MEMBER</p><h2>Create staff login</h2><form method="post" class="booking-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="create"><label for="name">Full name</label><input id="name" name="name" required minlength="2"><label for="email">Staff email</label><input id="email" name="email" type="email" required><label for="password">Temporary password</label><input id="password" name="password" type="password" minlength="10" required><p class="fine-print">Use at least 10 characters. Share credentials privately and have staff change them before production use.</p><button class="button button-dark full-width">Create staff account</button></form></div></div></section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
